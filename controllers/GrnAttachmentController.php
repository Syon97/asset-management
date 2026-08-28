<?php

namespace app\controllers;

use Yii;
use app\models\GrnAttachment;
use app\models\GoodsReceipt;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;

class GrnAttachmentController extends Controller
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

    public function actionUpload($grnId)
    {
        $grn = GoodsReceipt::findOne($grnId);
        if ($grn === null) {
            throw new NotFoundHttpException('The requested GRN does not exist.');
        }

        $uploadedBy = Yii::$app->request->post('uploaded_by');
        $remarks = Yii::$app->request->post('remarks');
        $files = UploadedFile::getInstancesByName('attachmentFiles');

        if (empty($uploadedBy)) {
            Yii::$app->session->setFlash('error', 'Could not determine who is uploading.');
            return $this->redirect(['/goods-receipt/view', 'id' => $grnId]);
        }

        if (empty($files)) {
            Yii::$app->session->setFlash('error', 'Please choose at least one file.');
            return $this->redirect(['/goods-receipt/view', 'id' => $grnId]);
        }

        $result = GrnAttachment::saveUploadedFiles($grnId, $uploadedBy, $files, $remarks);

        if ($result['saved'] > 0) {
            Yii::$app->session->setFlash('success', "{$result['saved']} file(s) uploaded.");
        }
        if (!empty($result['skipped'])) {
            Yii::$app->session->setFlash('warning', 'Skipped (unsupported file type): ' . implode(', ', $result['skipped']));
        }

        return $this->redirect(['/goods-receipt/view', 'id' => $grnId]);
    }

    public function actionDelete($id)
    {
        $model = GrnAttachment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested attachment does not exist.');
        }

        $grnId = $model->grn_id;
        $path = Yii::getAlias('@webroot/uploads/grn-attachments/' . $model->stored_name);
        if (is_file($path)) {
            @unlink($path);
        }
        $model->delete();

        Yii::$app->session->setFlash('success', 'Attachment removed.');
        return $this->redirect(['/goods-receipt/view', 'id' => $grnId]);
    }

    /**
     * Tier 1 extraction: pulls whatever text is actually embedded in a PDF
     * (works for genuine digital invoices e.g. Shopee/Lazada) and applies
     * loose pattern matching for an order/invoice number and a total
     * amount. Returns empty results for scanned/photographed invoices,
     * since those have no embedded text to read - that's expected, not a
     * bug (true OCR would be a separate, heavier feature).
     */
    public function actionExtract($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $model = GrnAttachment::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested attachment does not exist.');
        }

        $ext = strtolower(pathinfo($model->stored_name, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return ['ok' => false, 'message' => 'Text extraction only works on PDF files.'];
        }

        $path = Yii::getAlias('@webroot/uploads/grn-attachments/' . $model->stored_name);
        if (!is_file($path)) {
            return ['ok' => false, 'message' => 'File not found on disk.'];
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($path);
            $text = $pdf->getText();
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => 'Could not read this PDF (it may be a scanned image, not a real text PDF).'];
        }

        if (trim($text) === '') {
            return ['ok' => false, 'message' => 'No text found in this PDF - likely a scanned image rather than a digital invoice.'];
        }

        $orderNo = null;
        if (preg_match('/(?:Order\s*(?:ID|No\.?|Number)?|Invoice\s*(?:No\.?|Number)?)\s*[:#]?\s*([A-Z0-9][A-Z0-9\-\/]{5,})/i', $text, $m)) {
            $orderNo = trim($m[1]);
        }

        $total = null;
        if (preg_match('/(?:Grand\s*Total|Total\s*Payable|Total\s*Amount|Total)\s*[:#]?\s*(?:RM|MYR)?\s*([\d,]+\.\d{2})/i', $text, $m)) {
            $total = str_replace(',', '', $m[1]);
        }

        return [
            'ok' => true,
            'order_no' => $orderNo,
            'total' => $total,
            'found_anything' => $orderNo !== null || $total !== null,
        ];
    }
}