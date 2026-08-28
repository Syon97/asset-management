<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class StockIssue extends ActiveRecord
{
    const HOLDER_STAFF = 'staff';
    const HOLDER_PROJECT = 'project';
    const HOLDER_DEPARTMENT = 'department';

    public static function tableName()
    {
        return 'stock_issue';
    }

    public function rules()
    {
        return [
            [['catalog_item_id', 'department_id', 'quantity', 'holder_type', 'holder_id', 'issued_by', 'issue_date'], 'required'],
            [['catalog_item_id', 'department_id', 'quantity', 'holder_id', 'issued_by'], 'integer'],
            [['quantity'], 'integer', 'min' => 1],
            [['holder_type'], 'in', 'range' => [self::HOLDER_STAFF, self::HOLDER_PROJECT, self::HOLDER_DEPARTMENT]],
            [['issue_date'], 'safe'],
            [['remarks'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'catalog_item_id' => 'Item',
            'department_id' => 'From Location',
            'holder_type' => 'Issue To',
            'issued_by' => 'Issued By',
            'issue_date' => 'Date',
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

    public function getCatalogItem()
    {
        return $this->hasOne(ItemCatalog::class, ['id' => 'catalog_item_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
    }

    public function getIssuedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'issued_by']);
    }

    /**
     * Resolves the holder's display name regardless of holder_type, since
     * holder_id is a polymorphic reference with no single FK relation.
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
}