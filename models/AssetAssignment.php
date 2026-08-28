<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class AssetAssignment extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_RETURNED = 'returned';

    const HOLDER_STAFF = 'staff';
    const HOLDER_PROJECT = 'project';
    const HOLDER_DEPARTMENT = 'department';

    public static function tableName()
    {
        return 'asset_assignment';
    }

    public function rules()
    {
        return [
            [['hardware_asset_id', 'holder_type', 'holder_id', 'assigned_by'], 'required'],
            [['hardware_asset_id', 'holder_id', 'assigned_by', 'returned_by'], 'integer'],
            [['assigned_at', 'returned_at'], 'safe'],
            [['holder_type'], 'in', 'range' => [self::HOLDER_STAFF, self::HOLDER_PROJECT, self::HOLDER_DEPARTMENT]],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_RETURNED]],
            [['remarks'], 'string'],
        ];
    }

    public static function holderTypeLabels()
    {
        return [
            self::HOLDER_STAFF => 'Staff',
            self::HOLDER_PROJECT => 'Project',
            self::HOLDER_DEPARTMENT => 'Department',
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

    public function getReturnedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'returned_by']);
    }

    /**
     * Resolves the holder's display name regardless of holder_type, same
     * approach as HardwareAsset::getHolderLabel() / StockIssue::getHolderLabel().
     */
    public function getHolderLabel()
    {
        switch ($this->holder_type) {
            case self::HOLDER_STAFF:
                $staff = Staff::findOne($this->holder_id);
                return $staff->staff_name ?? "Staff #{$this->holder_id}";
            case self::HOLDER_PROJECT:
                $project = Project::findOne($this->holder_id);
                return $project->project_name ?? "Project #{$this->holder_id}";
            case self::HOLDER_DEPARTMENT:
                $department = Department::findOne($this->holder_id);
                return $department->name ?? "Department #{$this->holder_id}";
            default:
                return '-';
        }
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            if (empty($this->status)) {
                $this->status = self::STATUS_ACTIVE;
            }
            if (empty($this->assigned_at)) {
                $this->assigned_at = date('Y-m-d H:i:s');
            }
        }
        return parent::beforeSave($insert);
    }
}