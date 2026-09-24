<?php

namespace app\controllers;

use Yii;
use app\models\AssetAssignment;
use app\models\HardwareAsset;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class AssetAssignmentController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(),[
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'assign' => ['POST'],
                    'transfer' => ['POST'],
                    'return' => ['POST'],
                ],
            ],
        ]);
    }

    /**
     * For an asset with no current holder: starts a new active assignment.
     */
    public function actionAssign($hardwareAssetId)
    {
        $asset = HardwareAsset::findOne($hardwareAssetId);
        if ($asset === null) {
            throw new NotFoundHttpException('The requested hardware asset does not exist.');
        }

        if ($asset->hasActiveHolder()) {
            Yii::$app->session->setFlash('warning', 'This asset already has a holder - use Transfer or Return instead.');
            return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
        }

        $holderType = Yii::$app->request->post('holder_type');
        $holderId = Yii::$app->request->post('holder_id');
        $assignedBy = Yii::$app->request->post('assigned_by');
        $remarks = Yii::$app->request->post('remarks');

        if (empty($holderType) || empty($holderId)) {
            Yii::$app->session->setFlash('error', 'Please select who/what this asset is being assigned to.');
            return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
        }
        if (empty($assignedBy)) {
            Yii::$app->session->setFlash('error', 'Please click "Select" next to Assigned By.');
            return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $assignment = new AssetAssignment();
            $assignment->hardware_asset_id = $asset->id;
            $assignment->holder_type = $holderType;
            $assignment->holder_id = $holderId;
            $assignment->assigned_by = $assignedBy;
            $assignment->remarks = $remarks;
            if (!$assignment->save()) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to save assignment.');
                return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
            }

            $asset->current_holder_type = $holderType;
            $asset->current_holder_id = $holderId;
            $asset->save(false);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', 'Asset assigned.');
        return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
    }

    /**
     * For an asset with a current holder: closes that assignment out and
     * opens a new one for the new holder, atomically.
     */
    public function actionTransfer($hardwareAssetId)
    {
        $asset = HardwareAsset::findOne($hardwareAssetId);
        if ($asset === null) {
            throw new NotFoundHttpException('The requested hardware asset does not exist.');
        }

        if (!$asset->hasActiveHolder()) {
            Yii::$app->session->setFlash('warning', 'This asset has no current holder to transfer from - use Assign instead.');
            return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
        }

        $holderType = Yii::$app->request->post('holder_type');
        $holderId = Yii::$app->request->post('holder_id');
        $transferredBy = Yii::$app->request->post('transferred_by');
        $remarks = Yii::$app->request->post('remarks');

        if (empty($holderType) || empty($holderId)) {
            Yii::$app->session->setFlash('error', 'Please select the new holder to transfer this asset to.');
            return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
        }
        if (empty($transferredBy)) {
            Yii::$app->session->setFlash('error', 'Please click "Select" next to Transferred By.');
            return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $current = AssetAssignment::find()
                ->where(['hardware_asset_id' => $asset->id, 'status' => AssetAssignment::STATUS_ACTIVE])
                ->one();
            if ($current) {
                $current->status = AssetAssignment::STATUS_RETURNED;
                $current->returned_by = $transferredBy;
                $current->returned_at = date('Y-m-d H:i:s');
                $current->remarks = trim(($current->remarks ? $current->remarks . ' ' : '') . '(Transferred to a new holder)');
                $current->save(false);
            }

            $new = new AssetAssignment();
            $new->hardware_asset_id = $asset->id;
            $new->holder_type = $holderType;
            $new->holder_id = $holderId;
            $new->assigned_by = $transferredBy;
            $new->remarks = $remarks;
            if (!$new->save()) {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Failed to save the new assignment.');
                return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
            }

            $asset->current_holder_type = $holderType;
            $asset->current_holder_id = $holderId;
            $asset->save(false);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', 'Asset transferred.');
        return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
    }

    /**
     * For an asset with a current holder: ends the assignment, asset
     * becomes unassigned (back in the store/location).
     */
    public function actionReturn($hardwareAssetId)
    {
        $asset = HardwareAsset::findOne($hardwareAssetId);
        if ($asset === null) {
            throw new NotFoundHttpException('The requested hardware asset does not exist.');
        }

        if (!$asset->hasActiveHolder()) {
            Yii::$app->session->setFlash('warning', 'This asset has no current holder to return.');
            return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
        }

        $returnedBy = Yii::$app->request->post('returned_by');
        $remarks = Yii::$app->request->post('remarks');

        if (empty($returnedBy)) {
            Yii::$app->session->setFlash('error', 'Please click "Select" next to Returned By.');
            return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $current = AssetAssignment::find()
                ->where(['hardware_asset_id' => $asset->id, 'status' => AssetAssignment::STATUS_ACTIVE])
                ->one();
            if ($current) {
                $current->status = AssetAssignment::STATUS_RETURNED;
                $current->returned_by = $returnedBy;
                $current->returned_at = date('Y-m-d H:i:s');
                $current->remarks = trim(($current->remarks ? $current->remarks . ' ' : '') . $remarks);
                $current->save(false);
            }

            $asset->current_holder_type = null;
            $asset->current_holder_id = null;
            $asset->save(false);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', 'Asset returned.');
        return $this->redirect(['/hardware-asset/view', 'id' => $hardwareAssetId]);
    }
}