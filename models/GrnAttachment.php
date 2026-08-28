<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class GrnAttachment extends ActiveRecord
{
    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

    public static function tableName()
    {
        return 'grn_attachment';
    }

    public function rules()
    {
        return [
            [['grn_id', 'original_name', 'stored_name', 'uploaded_by'], 'required'],
            [['grn_id', 'uploaded_by'], 'integer'],
            [['original_name', 'stored_name'], 'string', 'max' => 255],
            [['remarks'], 'string', 'max' => 255],
        ];
    }

    public function getGoodsReceipt()
    {
        return $this->hasOne(GoodsReceipt::class, ['id' => 'grn_id']);
    }

    public function getUploadedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'uploaded_by']);
    }

    public function getDownloadUrl()
    {
        return Yii::getAlias('@web/uploads/grn-attachments/' . $this->stored_name);
    }

    public static function saveUploadedFiles($grnId, $uploadedBy, array $files, $remarks = null)
    {
        $uploadDir = Yii::getAlias('@webroot/uploads/grn-attachments');
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

            $storedName = 'GRN' . $grnId . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

            if ($file->saveAs($uploadDir . DIRECTORY_SEPARATOR . $storedName)) {
                $attachment = new self();
                $attachment->grn_id = $grnId;
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