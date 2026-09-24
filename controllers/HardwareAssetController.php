<?php

namespace app\controllers;

use Yii;
use app\models\HardwareAsset;
use app\models\HardwareAssetSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;

class HardwareAssetController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        // Generic users can only reach index/view/qr/print-label,
                        // and only for assets currently assigned to them -
                        // enforced inside each action below, not just by
                        // hiding the link.
                        'actions' => ['index', 'view', 'qr', 'print-label'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->canAccessOperations();
                        },
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new HardwareAssetSearch();

        if (!Yii::$app->user->identity->canAccessOperations()) {
            $searchModel->restrictToOwnStaffId = Yii::$app->user->identity->staff_id;
        }

        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnAssetAccess($model);

        return $this->render('view', [
            'model' => $model,
        ]);
    }

    public function actionCreate()
    {
        $model = new HardwareAsset();
        $model->status = HardwareAsset::STATUS_ACTIVE;

        // Pre-fill from a Stock Issue (the "optional link" from issuing stock
        // into creating a trackable hardware record for the specific unit).
        $fromIssueId = Yii::$app->request->get('fromStockIssue');
        if ($fromIssueId) {
            $issue = \app\models\StockIssue::findOne($fromIssueId);
            if ($issue !== null) {
                $model->category_id = $issue->catalogItem->category_id ?? null;
                $model->department_id = $issue->department_id;
                $model->brand = $issue->catalogItem->item_name ?? '';
                $model->current_holder_type = $issue->holder_type;
                $model->current_holder_id = $issue->holder_id;
                $model->remarks = 'Created from Stock Issue #' . $issue->id;
            }
        }

        if ($this->load($model)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->load($model)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    private function load($model)
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            return false;
        }

        $isNew = $model->isNewRecord;

        if (!$model->load($request->post())) {
            return false;
        }

        $model->productImageFile = UploadedFile::getInstance($model, 'productImageFile');
        $model->serialImageFile = UploadedFile::getInstance($model, 'serialImageFile');
        $model->serialCommandImageFile = UploadedFile::getInstance($model, 'serialCommandImageFile');

        // A holder set at creation time still needs to go through the same
        // history table as Assign/Transfer/Return, so it shows up correctly
        // on the asset's Assignment History from day one.
        $assignedBy = $request->post('assigned_by');
        if ($isNew && !empty($model->current_holder_type) && !empty($model->current_holder_id)) {
            if (empty($assignedBy)) {
                Yii::$app->session->setFlash('error', 'Please click "Select" next to Assigned By, since a holder is set.');
                return false;
            }
        }

        if (!$model->validate()) {
            Yii::$app->session->setFlash('error', $this->collectErrors($model));
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save(false)) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to save.');
                return false;
            }

            if ($isNew && !empty($model->current_holder_type) && !empty($model->current_holder_id)) {
                $assignment = new \app\models\AssetAssignment();
                $assignment->hardware_asset_id = $model->id;
                $assignment->holder_type = $model->current_holder_type;
                $assignment->holder_id = $model->current_holder_id;
                $assignment->assigned_by = $assignedBy;
                $assignment->remarks = 'Set at asset creation.';
                if (!$assignment->save()) {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Failed to save the initial assignment record.');
                    return false;
                }
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        $model->saveUploadedImages();

        return true;
    }

    public function actionDelete($id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * Dynamically generated QR PNG encoding an absolute URL to this asset's
     * view page. Not stored as a file - generated on request each time.
     */
    public function actionQr($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnAssetAccess($model);

        $url = Url::to(['/hardware-asset/view', 'id' => $model->id], true);

        $builder = new Builder(
            writer: new PngWriter(),
            data: $url,
            size: 300,
            margin: 10
        );
        $result = $builder->build();

        Yii::$app->response->format = Response::FORMAT_RAW;
        Yii::$app->response->headers->set('Content-Type', $result->getMimeType());
        return $result->getString();
    }

    /**
     * Printable QR label - small sticker-style page, no app layout.
     */
    public function actionPrintLabel($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnAssetAccess($model);

        $this->layout = false;

        return $this->render('print-label', [
            'model' => $model,
        ]);
    }

    /**
     * A Generic user can only view/print-label/QR-scan an asset that's
     * currently assigned to them. Operations roles (Purchaser/Financer/
     * Admin) always have full access.
     */
    private function checkOwnAssetAccess($model)
    {
        if (Yii::$app->user->identity->canAccessOperations()) {
            return;
        }

        $ownStaffId = Yii::$app->user->identity->staff_id;
        if ($model->current_holder_type !== 'staff' || (int) $model->current_holder_id !== (int) $ownStaffId) {
            throw new ForbiddenHttpException('You can only view assets assigned to you.');
        }
    }

    protected function findModel($id)
    {
        if (($model = HardwareAsset::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested hardware asset does not exist.');
    }

    private function collectErrors($model)
    {
        $messages = [];
        foreach ($model->getErrors() as $attrErrors) {
            $messages = array_merge($messages, $attrErrors);
        }
        return $messages ? implode('<br>', $messages) : 'Failed to save. Please check your input.';
    }
}