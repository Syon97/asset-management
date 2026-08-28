<?php

namespace app\controllers;

use Yii;
use app\models\PrAttachment;
use app\models\PurchaseRequisition;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

class PrAttachmentController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'upload' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionUpload($prId)
    {
        $pr = PurchaseRequisition::findOne($prId);
        if ($pr === null) {
            throw new NotFoundHttpException('The requested PR does not exist.');
        }

        $uploadedBy = Yii::$app->request->post('uploaded_by');
        $remarks = Yii::$app->request->post('remarks');
        $files = UploadedFile::getInstancesByName('attachmentFiles');

        if (empty($uploadedBy)) {
            Yii::$app->session->setFlash('error', 'Could not determine who is uploading (Requested By is missing on this PR).');
            return $this->redirect(['/purchase-requisition/view', 'id' => $prId]);
        }

        if (empty($files)) {
            Yii::$app->session->setFlash('error', 'Please choose at least one file.');
            return $this->redirect(['/purchase-requisition/view', 'id' => $prId]);
        }

        $result = PrAttachment::saveUploadedFiles($prId, $uploadedBy, $files, $remarks);

        if ($result['saved'] > 0) {
            Yii::$app->session->setFlash('success', "{$result['saved']} file(s) uploaded.");
        }
        if (!empty($result['skipped'])) {
            Yii::$app->session->setFlash('warning', 'Skipped (unsupported file type): ' . implode(', ', $result['skipped']));
        }

        return $this->redirect(['/purchase-requisition/view', 'id' => $prId]);
    }

    public function actionDelete($id)
    {
        $model = PrAttachment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested attachment does not exist.');
        }

        $prId = $model->pr_id;
        $path = Yii::getAlias('@webroot/uploads/pr-attachments/' . $model->stored_name);
        if (is_file($path)) {
            @unlink($path);
        }
        $model->delete();

        Yii::$app->session->setFlash('success', 'Attachment removed.');
        return $this->redirect(['/purchase-requisition/view', 'id' => $prId]);
    }
}