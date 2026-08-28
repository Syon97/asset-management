<?php

namespace app\controllers;

use Yii;
use app\models\PurchaseOrder;
use app\models\PoItem;
use app\models\PurchaseOrderSearch;
use app\models\PurchaseRequisition;
use app\models\Staff;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

class PurchaseOrderController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'approve' => ['POST'],
                    'reject' => ['POST'],
                    'close' => ['POST'],
                    'set-erp-no' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new PurchaseOrderSearch();
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
     * Records the real PO number issued by the company's ERP system.
     * This is purely a record-keeping link, not a replacement for whatever
     * numbering/workflow the ERP already handles.
     */
    public function actionSetErpNo($id)
    {
        $model = $this->findModel($id);
        $model->erp_po_no = trim(Yii::$app->request->post('erp_po_no'));
        $model->save(false);

        Yii::$app->session->setFlash('success', 'ERP PO No. updated.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Step 1 of conversion: pick an approved PR from the PR view page,
     * which links here. Builds a draft PO pre-filled with the PR's items.
     */
    public function actionConvert($prId)
    {
        $pr = PurchaseRequisition::findOne($prId);
        if ($pr === null) {
            throw new NotFoundHttpException('The requested PR does not exist.');
        }

        if ($pr->status !== PurchaseRequisition::STATUS_APPROVED) {
            Yii::$app->session->setFlash('error', 'Only approved PRs can be converted to a Purchase Order.');
            return $this->redirect(['purchase-requisition/view', 'id' => $prId]);
        }

        $existingPo = PurchaseOrder::findOne(['pr_id' => $prId]);
        if ($existingPo !== null) {
            Yii::$app->session->setFlash('warning', 'This PR has already been converted to ' . $existingPo->po_no . '.');
            return $this->redirect(['view', 'id' => $existingPo->id]);
        }

        $model = new PurchaseOrder();
        $model->po_date = date('Y-m-d');
        $model->pr_id = $pr->id;
        $model->department_id = $pr->department_id;
        $model->cost_center_id = $pr->cost_center_id;

        $items = [];
        foreach ($pr->items as $prItem) {
            $poItem = new PoItem();
            $poItem->pr_item_id = $prItem->id;
            $poItem->catalog_item_id = $prItem->catalog_item_id;
            $poItem->description = $prItem->catalogItem->item_name ?? $prItem->description;
            $poItem->specification = $prItem->specification;
            $poItem->item_type = $prItem->item_type;
            $poItem->quantity = $prItem->quantity;
            $poItem->unit_price = $prItem->unit_price;
            $items[] = $poItem;
        }

        if ($this->loadAndSave($model, $items)) {
            Yii::$app->session->setFlash('success', $model->po_no . ' created as draft.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('convert', [
            'model' => $model,
            'items' => $items,
            'pr' => $pr,
        ]);
    }

    private function loadAndSave($model, &$items)
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            return false;
        }

        if (!$model->load($request->post())) {
            return false;
        }

        if (empty($model->prepared_by)) {
            Yii::$app->session->setFlash('error', 'Please click "Select" next to Prepared By.');
            return false;
        }

        if (empty($model->supplier_id)) {
            Yii::$app->session->setFlash('error', 'Please select a Supplier.');
            return false;
        }

        $newItems = PoItem::createMultiple(PoItem::class, $items);
        PoItem::loadMultiple($newItems, $request->post());
        $items = $newItems;

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
                Yii::$app->session->setFlash('error', $this->collectErrors($model, $items));
                return false;
            }

            if (!$model->save(false)) {
                $transaction->rollBack();
                return false;
            }

            foreach ($items as $item) {
                $item->po_id = $model->id;
                if (!$item->save(false)) {
                    $transaction->rollBack();
                    return false;
                }
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        if ($model->status !== PurchaseOrder::STATUS_DRAFT) {
            Yii::$app->session->setFlash('warning', 'Only draft POs can be deleted.');
            return $this->redirect(['view', 'id' => $id]);
        }
        $model->delete();
        return $this->redirect(['index']);
    }

    /**
     * Single approval step: approving a draft PO also sends it to the supplier.
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if (!$model->canApprove()) {
            Yii::$app->session->setFlash('warning', 'This PO can no longer be approved.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $approvedBy = Yii::$app->request->post('approved_by');
        if (!$approvedBy) {
            Yii::$app->session->setFlash('error', 'Please select who is approving this PO.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->approved_by = $approvedBy;
        $model->approved_at = date('Y-m-d H:i:s');
        $model->status = PurchaseOrder::STATUS_SENT;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PO approved and marked as sent to supplier.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);

        if (!$model->canReject()) {
            Yii::$app->session->setFlash('warning', 'This PO can no longer be rejected.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $rejectedBy = Yii::$app->request->post('rejected_by');
        $reason = Yii::$app->request->post('rejection_reason');

        $model->rejected_by = $rejectedBy;
        $model->rejected_at = date('Y-m-d H:i:s');
        $model->rejection_reason = $reason;
        $model->status = PurchaseOrder::STATUS_REJECTED;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PO rejected.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionClose($id)
    {
        $model = $this->findModel($id);

        if (!$model->canClose()) {
            Yii::$app->session->setFlash('warning', 'This PO cannot be closed from its current status.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $closedBy = Yii::$app->request->post('closed_by');
        if (!$closedBy) {
            Yii::$app->session->setFlash('error', 'Please select who is closing this PO.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->closed_by = $closedBy;
        $model->closed_at = date('Y-m-d H:i:s');
        $model->status = PurchaseOrder::STATUS_CLOSED;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'PO closed.');
        return $this->redirect(['view', 'id' => $id]);
    }

    protected function findModel($id)
    {
        if (($model = PurchaseOrder::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested PO does not exist.');
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