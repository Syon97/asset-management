<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PrAttachment extends ActiveRecord
{
    public static function tableName()
    {
        return 'pr_attachment';
    }

    public function rules()
    {
        return [
            [['pr_id', 'original_name', 'stored_name', 'uploaded_by'], 'required'],
            [['pr_id', 'uploaded_by'], 'integer'],
            [['original_name', 'stored_name'], 'string', 'max' => 255],
            [['remarks'], 'string', 'max' => 255],
        ];
    }

    public function getPurchaseRequisition()
    {
        return $this->hasOne(PurchaseRequisition::class, ['id' => 'pr_id']);
    }

    public function getUploadedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'uploaded_by']);
    }

    public function getDownloadUrl()
    {
        return Yii::getAlias('@web/uploads/pr-attachments/' . $this->stored_name);
    }

    const ALLOWED_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx', 'xls', 'xlsx'];

    /**
     * Saves one or more UploadedFile instances as attachments for a PR.
     * @param int $prId
     * @param int $uploadedBy staff id
     * @param \yii\web\UploadedFile[] $files
     * @param string|null $remarks
     * @return array ['saved' => int, 'skipped' => string[]]
     */
    public static function saveUploadedFiles($prId, $uploadedBy, array $files, $remarks = null)
    {
        $uploadDir = Yii::getAlias('@webroot/uploads/pr-attachments');
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

            $storedName = 'PR' . $prId . '_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;

            if ($file->saveAs($uploadDir . DIRECTORY_SEPARATOR . $storedName)) {
                $attachment = new self();
                $attachment->pr_id = $prId;
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