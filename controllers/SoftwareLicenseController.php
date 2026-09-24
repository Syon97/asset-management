<?php

namespace app\controllers;

use Yii;
use app\models\SoftwareLicense;
use app\models\SoftwareLicenseSearch;
use app\models\HardwareAsset;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class SoftwareLicenseController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'unassign' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new SoftwareLicenseSearch();
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

    public function actionCreate($hardwareAssetId)
    {
        $hardwareAsset = HardwareAsset::findOne($hardwareAssetId);
        if ($hardwareAsset === null) {
            throw new NotFoundHttpException('The requested hardware asset does not exist.');
        }

        $model = new SoftwareLicense();
        $model->hardware_asset_id = $hardwareAsset->id;

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if (empty($model->assigned_by)) {
                Yii::$app->session->setFlash('error', 'Please click "Select" next to Assigned By.');
            } elseif ($model->save()) {
                Yii::$app->session->setFlash('success', 'Software license added.');
                return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAsset->id]);
            } else {
                Yii::$app->session->setFlash('error', $this->collectErrors($model));
            }
        }

        return $this->render('create', [
            'model' => $model,
            'hardwareAsset' => $hardwareAsset,
        ]);
    }

    public function actionUnassign($id)
    {
        $model = $this->findModel($id);

        if (!$model->canUnassign()) {
            Yii::$app->session->setFlash('warning', 'This license is already unassigned.');
            return $this->redirect(['/hardware-asset/view', 'id' => $model->hardware_asset_id]);
        }

        $unassignedBy = Yii::$app->request->post('unassigned_by');
        if (!$unassignedBy) {
            Yii::$app->session->setFlash('error', 'Please select who is unassigning this license.');
            return $this->redirect(['/hardware-asset/view', 'id' => $model->hardware_asset_id]);
        }

        $model->status = SoftwareLicense::STATUS_UNASSIGNED;
        $model->unassigned_by = $unassignedBy;
        $model->unassigned_at = date('Y-m-d H:i:s');
        $model->unassign_reason = Yii::$app->request->post('unassign_reason');
        $model->save(false);

        Yii::$app->session->setFlash('success', 'License unassigned. It remains here as history; use Reassign to move it to another asset.');
        return $this->redirect(['/hardware-asset/view', 'id' => $model->hardware_asset_id]);
    }

    /**
     * Moves a previously-unassigned license to a different hardware asset.
     * Creates a NEW row (carrying over name/version/key) so the old row
     * stays intact as history rather than being overwritten.
     */
    public function actionReassign($id)
    {
        $old = $this->findModel($id);

        if ($old->status !== SoftwareLicense::STATUS_UNASSIGNED) {
            Yii::$app->session->setFlash('warning', 'Only an unassigned license can be reassigned.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model = new SoftwareLicense();
        $model->software_name = $old->software_name;
        $model->software_version = $old->software_version;
        $model->license_key = $old->license_key;
        $model->remarks = $old->remarks;

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if (empty($model->hardware_asset_id)) {
                Yii::$app->session->setFlash('error', 'Please select the hardware asset to reassign this license to.');
            } elseif (empty($model->assigned_by)) {
                Yii::$app->session->setFlash('error', 'Please click "Select" next to Assigned By.');
            } elseif ($model->save()) {
                Yii::$app->session->setFlash('success', 'License reassigned as a new record; the previous assignment stays in history.');
                return $this->redirect(['/hardware-asset/view', 'id' => $model->hardware_asset_id]);
            } else {
                Yii::$app->session->setFlash('error', $this->collectErrors($model));
            }
        }

        return $this->render('reassign', [
            'model' => $model,
            'oldLicense' => $old,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = SoftwareLicense::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested license does not exist.');
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