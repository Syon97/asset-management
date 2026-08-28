<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class StockLedger extends ActiveRecord
{
    const MOVEMENT_IN = 'in';
    const MOVEMENT_OUT = 'out';

    public static function tableName()
    {
        return 'stock_ledger';
    }

    public function rules()
    {
        return [
            [['catalog_item_id', 'department_id', 'movement_type', 'quantity', 'reference_type', 'reference_id'], 'required'],
            [['catalog_item_id', 'department_id', 'quantity', 'reference_id', 'staff_id'], 'integer'],
            [['movement_type'], 'in', 'range' => [self::MOVEMENT_IN, self::MOVEMENT_OUT]],
            [['reference_type'], 'string', 'max' => 20],
            [['notes'], 'string', 'max' => 255],
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

    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id']);
    }
}