<?php

namespace app\controllers;

use Yii;
use app\models\HardwareAccessory;
use app\models\HardwareAsset;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

class HardwareAccessoryController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'detach' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * Cross-asset listing for auditing (which accessories are attached where).
     */
    public function actionIndex()
    {
        $searchModel = new \app\models\HardwareAccessorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Attach a new accessory to a hardware asset. Linked from the
     * hardware asset's view page.
     */
    public function actionAttach($hardwareAssetId)
    {
        $hardwareAsset = HardwareAsset::findOne($hardwareAssetId);
        if ($hardwareAsset === null) {
            throw new NotFoundHttpException('The requested hardware asset does not exist.');
        }

        $model = new HardwareAccessory();
        $model->hardware_asset_id = $hardwareAsset->id;

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            $model->accessoryImageFile = UploadedFile::getInstance($model, 'accessoryImageFile');

            if (empty($model->attached_by)) {
                Yii::$app->session->setFlash('error', 'Please click "Select" next to Attached By.');
            } elseif (!$model->validate()) {
                Yii::$app->session->setFlash('error', $this->collectErrors($model));
            } elseif ($model->save(false)) {
                $model->saveUploadedImage();
                Yii::$app->session->setFlash('success', 'Accessory attached.');
                return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAsset->id]);
            }
        }

        return $this->render('attach', [
            'model' => $model,
            'hardwareAsset' => $hardwareAsset,
        ]);
    }

    public function actionDetach($id)
    {
        $model = $this->findModel($id);

        if (!$model->canDetach()) {
            Yii::$app->session->setFlash('warning', 'This accessory is already detached.');
            return $this->redirect(['/hardware-asset/view', 'id' => $model->hardware_asset_id]);
        }

        $detachedBy = Yii::$app->request->post('detached_by');
        if (!$detachedBy) {
            Yii::$app->session->setFlash('error', 'Please select who is detaching this accessory.');
            return $this->redirect(['/hardware-asset/view', 'id' => $model->hardware_asset_id]);
        }

        $model->status = HardwareAccessory::STATUS_DETACHED;
        $model->detached_by = $detachedBy;
        $model->detached_at = date('Y-m-d H:i:s');
        $model->detach_reason = Yii::$app->request->post('detach_reason');
        $model->save(false);

        Yii::$app->session->setFlash('success', 'Accessory detached.');
        return $this->redirect(['/hardware-asset/view', 'id' => $model->hardware_asset_id]);
    }

    protected function findModel($id)
    {
        if (($model = HardwareAccessory::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested record does not exist.');
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