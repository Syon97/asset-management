<?php

namespace app\controllers;

use Yii;
use app\models\PurchaseRequisition;
use app\models\PrItem;
use app\models\PurchaseRequisitionSearch;
use app\models\Staff;
use app\models\PrAttachment;
use yii\web\UploadedFile;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class PurchaseRequisitionController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'submit' => ['POST'],
                    'verify' => ['POST'],
                    'review' => ['POST'],
                    'receive' => ['POST'],
                    'approve' => ['POST'],
                    'reject' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new PurchaseRequisitionSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Printable version of the PR, laid out to match the real paper form.
     * Rendered without the app layout (no sidebar/navbar).
     */
    public function actionPrint($id)
    {
        $this->layout = false;

        return $this->render('print', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        if (!Yii::$app->request->isPost) {
            Yii::$app->session->removeAllFlashes();
        }

        $model = new PurchaseRequisition();
        $model->pr_date = date('Y-m-d');
        $model->order_type = PurchaseRequisition::ORDER_TYPE_NEW;
        $items = [new PrItem()];

        if ($this->load($model, $items)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if (Yii::$app->request->isPost && !Yii::$app->session->hasFlash('error')) {
            Yii::$app->session->setFlash('error', $this->collectErrors($model, $items));
        }

        return $this->render('create', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->status !== PurchaseRequisition::STATUS_DRAFT) {
            Yii::$app->session->setFlash('warning', 'Only draft PRs can be edited.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $items = $model->items;
        if (empty($items)) {
            $items = [new PrItem()];
        }

        if ($this->load($model, $items)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if (Yii::$app->request->isPost && !Yii::$app->session->hasFlash('error')) {
            Yii::$app->session->setFlash('error', $this->collectErrors($model, $items));
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    private function load($model, &$items)
    {
        $request = Yii::$app->request;

        if (!$model->load($request->post())) {
            return false;
        }

        // Department is always derived from the requester, never chosen manually.
        if (empty($model->requested_by)) {
            Yii::$app->session->setFlash('error', 'Please click "Select" next to Requested By to pick who is requesting this PR.');
            return false;
        }

        $requester = Staff::findOne($model->requested_by);
        if ($requester === null) {
            Yii::$app->session->setFlash('error', 'The selected requester could not be found. Please pick again.');
            return false;
        }

        $model->department_id = $requester->department_id;
        if ($model->department_id === null) {
            Yii::$app->session->setFlash('error', 'The selected staff has no department mapped yet. Please fix this in Master Data > Department (match HR spelling) or re-run Staff sync before creating this PR.');
            return false;
        }

        $oldItemIds = \yii\helpers\ArrayHelper::getColumn($model->items ?? [], 'id');
        $items = PrItem::createMultiple(PrItem::class, $model->items ?? []);
        PrItem::loadMultiple($items, $request->post());

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->validate()) {
                $transaction->rollBack();
                return false;
            }

            $valid = true;
            foreach ($items as $item) {
                $valid = $item->validate() && $valid;
            }
            if (!$valid) {
                $transaction->rollBack();
                return false;
            }

            if (!$model->save(false)) {
                $transaction->rollBack();
                return false;
            }

            $deletedIds = array_diff($oldItemIds, array_filter(\yii\helpers\ArrayHelper::getColumn($items, 'id')));
            if (!empty($deletedIds)) {
                PrItem::deleteAll(['id' => $deletedIds]);
            }

            foreach ($items as $item) {
                $item->pr_id = $model->id;
                if (!$item->save(false)) {
                    $transaction->rollBack();
                    return false;
                }
            }

            $transaction->commit();

            $attachmentFiles = UploadedFile::getInstancesByName('attachmentFiles');
            if (!empty($attachmentFiles)) {
                $remarks = $request->post('attachment_remarks');
                $result = PrAttachment::saveUploadedFiles($model->id, $model->requested_by, $attachmentFiles, $remarks);
                if (!empty($result['skipped'])) {
                    Yii::$app->session->setFlash('warning', 'Some files were skipped (unsupported type): ' . implode(', ', $result['skipped']));
                }
            }

            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    public function actionSubmit($id)
    {
        $model = $this->findModel($id);
        $model->status = PurchaseRequisition::STATUS_SUBMITTED;
        $model->save(false);
        Yii::$app->session->setFlash('success', 'PR submitted for verification.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Step 2: Verified By (Dept Manager)
     */
    public function actionVerify($id)
    {
        $model = $this->findModel($id);
        $verifiedBy = Yii::$app->request->post('verified_by');

        if (!$verifiedBy) {
            Yii::$app->session->setFlash('warning', 'Please select who is verifying this PR.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->status = PurchaseRequisition::STATUS_VERIFIED;
        $model->verified_by = $verifiedBy;
        $model->verified_at = date('Y-m-d H:i:s');
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PR verified, pending review.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Step 3: Reviewed By (P&L Manager / Finance Manager / Factory Manager)
     */
    public function actionReview($id)
    {
        $model = $this->findModel($id);
        $reviewedBy = Yii::$app->request->post('reviewed_by');

        if (!$reviewedBy) {
            Yii::$app->session->setFlash('warning', 'Please select who is reviewing this PR.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->status = PurchaseRequisition::STATUS_REVIEWED;
        $model->reviewed_by = $reviewedBy;
        $model->reviewed_at = date('Y-m-d H:i:s');
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PR reviewed, pending receipt by Purchasing.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Step 4: Received By (Purchasing)
     */
    public function actionReceive($id)
    {
        $model = $this->findModel($id);
        $receivedBy = Yii::$app->request->post('received_by');

        if (!$receivedBy) {
            Yii::$app->session->setFlash('warning', 'Please select who is receiving this PR in Purchasing.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->status = PurchaseRequisition::STATUS_RECEIVED;
        $model->received_by = $receivedBy;
        $model->received_at = date('Y-m-d H:i:s');
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PR received by Purchasing, pending final approval.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Step 5: Approved By (General Manager)
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);
        $approvedBy = Yii::$app->request->post('approved_by');

        if (!$approvedBy) {
            Yii::$app->session->setFlash('warning', 'Please select who is approving this PR.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->status = PurchaseRequisition::STATUS_APPROVED;
        $model->approved_by = $approvedBy;
        $model->approved_at = date('Y-m-d H:i:s');
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PR approved. Ready to convert into a Purchase Order.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);

        if (!$model->canReject()) {
            Yii::$app->session->setFlash('warning', 'This PR can no longer be rejected.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $rejectedBy = Yii::$app->request->post('rejected_by');
        $reason = Yii::$app->request->post('rejection_reason');

        $model->rejected_stage = $model->status;
        $model->status = PurchaseRequisition::STATUS_REJECTED;
        $model->rejected_by = $rejectedBy;
        $model->rejected_at = date('Y-m-d H:i:s');
        $model->rejection_reason = $reason;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PR rejected.');
        return $this->redirect(['view', 'id' => $id]);
    }

    protected function findModel($id)
    {
        if (($model = PurchaseRequisition::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested PR does not exist.');
    }

    private function collectErrors($model, $items)
    {
        $messages = [];
        foreach ($model->getErrors() as $attrErrors) {
            $messages = array_merge($messages, $attrErrors);
        }
        foreach ($items as $item) {
            foreach ($item->getErrors() as $attrErrors) {
                $messages = array_merge($messages, $attrErrors);
            }
        }
        return $messages ? implode('<br>', $messages) : 'Failed to save. Please check your input.';
    }
}