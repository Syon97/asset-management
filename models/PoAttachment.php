<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PoAttachment extends ActiveRecord
{
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

    public static function tableName()
    {
        return 'po_attachment';
    }

    public function rules()
    {
        return [
            [['po_id', 'original_name', 'stored_name', 'uploaded_by'], 'required'],
            [['po_id', 'uploaded_by'], 'integer'],
            [['original_name', 'stored_name'], 'string', 'max' => 255],
            [['remarks'], 'string', 'max' => 255],
        ];
    }

    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, ['id' => 'po_id']);
    }

    public function getUploadedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'uploaded_by']);
    }

    public function getDownloadUrl()
    {
        return Yii::getAlias('@web/uploads/po-attachments/' . $this->stored_name);
    }

    public static function saveUploadedFiles($poId, $uploadedBy, array $files, $remarks = null)
    {
        $uploadDir = Yii::getAlias('@webroot/uploads/po-attachments');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $saved = 0;
        $skipped = [];

        foreach ($files as $file) {
            $ext = strtolower($file->extension);
            if (!in_array($ext, self::ALLOWED_EXTENSIONS)) {
                $skipped[] = $file->name;
                continue;
            }

            $storedName = 'PO' . $poId . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

            if ($file->saveAs($uploadDir . DIRECTORY_SEPARATOR . $storedName)) {
                $attachment = new self();
                $attachment->po_id = $poId;
                $attachment->original_name = $file->baseName . '.' . $ext;
                $attachment->stored_name = $storedName;
                $attachment->uploaded_by = $uploadedBy;
                $attachment->remarks = $remarks;
                $attachment->save(false);
                $saved++;
            }
        }

        return ['saved' => $saved, 'skipped' => $skipped];
    }
}