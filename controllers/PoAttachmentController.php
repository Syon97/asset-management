<?php

namespace app\controllers;

use Yii;
use app\models\PoAttachment;
use app\models\PurchaseOrder;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

class PoAttachmentController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'upload' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionUpload($poId)
    {
        $po = PurchaseOrder::findOne($poId);
        if ($po === null) {
            throw new NotFoundHttpException('The requested PO does not exist.');
        }

        if (empty($po->erp_po_no)) {
            Yii::$app->session->setFlash('error', 'Please record the ERP PO No. before attaching documents.');
            return $this->redirect(['/purchase-order/view', 'id' => $poId]);
        }

        $uploadedBy = Yii::$app->request->post('uploaded_by');
        $remarks = Yii::$app->request->post('remarks');
        $files = UploadedFile::getInstancesByName('attachmentFiles');

        if (empty($uploadedBy)) {
            Yii::$app->session->setFlash('error', 'Could not determine who is uploading (Prepared By is missing on this PO).');
            return $this->redirect(['/purchase-order/view', 'id' => $poId]);
        }

        if (empty($files)) {
            Yii::$app->session->setFlash('error', 'Please choose at least one file.');
            return $this->redirect(['/purchase-order/view', 'id' => $poId]);
        }

        $result = PoAttachment::saveUploadedFiles($poId, $uploadedBy, $files, $remarks);

        if ($result['saved'] > 0) {
            Yii::$app->session->setFlash('success', "{$result['saved']} file(s) uploaded.");
        }
        if (!empty($result['skipped'])) {
            Yii::$app->session->setFlash('warning', 'Skipped (unsupported file type): ' . implode(', ', $result['skipped']));
        }

        return $this->redirect(['/purchase-order/view', 'id' => $poId]);
    }

    public function actionDelete($id)
    {
        $model = PoAttachment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested attachment does not exist.');
        }

        $poId = $model->po_id;
        $path = Yii::getAlias('@webroot/uploads/po-attachments/' . $model->stored_name);
        if (is_file($path)) {
            @unlink($path);
        }
        $model->delete();

        Yii::$app->session->setFlash('success', 'Attachment removed.');
        return $this->redirect(['/purchase-order/view', 'id' => $poId]);
    }
}