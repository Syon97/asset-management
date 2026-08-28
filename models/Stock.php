<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Stock extends ActiveRecord
{
    public static function tableName()
    {
        return 'stock';
    }

    public function rules()
    {
        return [
            [['catalog_item_id', 'department_id'], 'required'],
            [['catalog_item_id', 'department_id', 'quantity_on_hand'], 'integer'],
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

    /**
     * Finds (or creates, at qty 0) the stock row for an item/location combo.
     */
    public static function findOrCreate($catalogItemId, $departmentId)
    {
        $stock = self::findOne(['catalog_item_id' => $catalogItemId, 'department_id' => $departmentId]);
        if ($stock === null) {
            $stock = new self();
            $stock->catalog_item_id = $catalogItemId;
            $stock->department_id = $departmentId;
            $stock->quantity_on_hand = 0;
        }
        return $stock;
    }

    /**
     * Adjusts qty on hand and writes a matching ledger entry. Caller is
     * responsible for wrapping this in a transaction alongside whatever
     * else needs to happen atomically (e.g. GRN approval, PO status update).
     */
    public static function adjust($catalogItemId, $departmentId, $delta, $movementType, $referenceType, $referenceId, $staffId = null, $notes = null)
    {
        $stock = self::findOrCreate($catalogItemId, $departmentId);
        $stock->quantity_on_hand += $delta;
        if (!$stock->save(false)) {
            return false;
        }

        $ledger = new StockLedger();
        $ledger->catalog_item_id = $catalogItemId;
        $ledger->department_id = $departmentId;
        $ledger->movement_type = $movementType;
        $ledger->quantity = abs($delta);
        $ledger->reference_type = $referenceType;
        $ledger->reference_id = $referenceId;
        $ledger->staff_id = $staffId;
        $ledger->notes = $notes;

        return $ledger->save(false);
    }
}