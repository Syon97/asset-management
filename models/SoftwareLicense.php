<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class SoftwareLicense extends ActiveRecord
{
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_UNASSIGNED = 'unassigned';

    public static function tableName()
    {
        return 'software_license';
    }

    public function rules()
    {
        return [
            [['software_name', 'assigned_by'], 'required'],
            [['hardware_asset_id', 'assigned_by', 'unassigned_by'], 'integer'],
            [['assigned_at', 'unassigned_at'], 'safe'],
            [['software_name', 'software_version'], 'string', 'max' => 191],
            [['license_key'], 'string', 'max' => 255],
            [['remarks', 'unassign_reason'], 'string'],
            [['status'], 'in', 'range' => [self::STATUS_ASSIGNED, self::STATUS_UNASSIGNED]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'software_name' => 'Software Name',
            'software_version' => 'Version',
            'license_key' => 'License Key',
        ];
    }

    public function getHardwareAsset()
    {
        return $this->hasOne(HardwareAsset::class, ['id' => 'hardware_asset_id']);
    }

    public function getAssignedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'assigned_by']);
    }

    public function getUnassignedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'unassigned_by']);
    }

    public function canUnassign()
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            if (empty($this->status)) {
                $this->status = self::STATUS_ASSIGNED;
            }
            if (empty($this->assigned_at)) {
                $this->assigned_at = date('Y-m-d H:i:s');
            }
        }
        return parent::beforeSave($insert);
    }
}