<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class HardwareAccessory extends ActiveRecord
{
    const STATUS_ATTACHED = 'attached';
    const STATUS_DETACHED = 'detached';

    public $accessoryImageFile;

    public static function tableName()
    {
        return 'hardware_accessory';
    }

    public function rules()
    {
        return [
            [['hardware_asset_id', 'accessory_type_id', 'attached_by'], 'required'],
            [['hardware_asset_id', 'accessory_type_id', 'attached_by', 'detached_by'], 'integer'],
            [['attached_at', 'detached_at'], 'safe'],
            [['accessory_no'], 'string', 'max' => 255],
            [['remarks', 'detach_reason'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_ATTACHED, self::STATUS_DETACHED]],
            [['accessoryImageFile'], 'file', 'extensions' => 'jpg, jpeg, png', 'skipOnEmpty' => true],
        ];
    }

    public function attributeLabels()
    {
        return [
            'accessory_type_id' => 'Accessory Type',
            'accessory_no' => 'Accessory No. / Serial',
            'accessoryImageFile' => 'Photo',
        ];
    }

    public function getHardwareAsset()
    {
        return $this->hasOne(HardwareAsset::class, ['id' => 'hardware_asset_id']);
    }

    public function getAccessoryType()
    {
        return $this->hasOne(AccessoryType::class, ['id' => 'accessory_type_id']);
    }

    public function getAttachedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'attached_by']);
    }

    public function getDetachedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'detached_by']);
    }

    public function canDetach()
    {
        return $this->status === self::STATUS_ATTACHED;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            if (empty($this->status)) {
                $this->status = self::STATUS_ATTACHED;
            }
            if (empty($this->attached_at)) {
                $this->attached_at = date('Y-m-d H:i:s');
            }
        }
        return parent::beforeSave($insert);
    }

    public function saveUploadedImage()
    {
        if (!($this->accessoryImageFile instanceof UploadedFile)) {
            return;
        }

        $uploadDir = Yii::getAlias('@webroot/uploads/accessories');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $filename = 'acc_' . $this->id . '_' . time() . '.' . $this->accessoryImageFile->extension;
        if ($this->accessoryImageFile->saveAs($uploadDir . DIRECTORY_SEPARATOR . $filename)) {
            $this->accessory_image = $filename;
            $this->save(false);
        }
    }

    public function getImageUrl()
    {
        if (empty($this->accessory_image)) {
            return null;
        }
        return Yii::getAlias('@web/uploads/accessories/' . $this->accessory_image);
    }
}