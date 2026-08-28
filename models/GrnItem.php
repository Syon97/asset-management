<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class GrnItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'grn_item';
    }

    public function rules()
    {
        return [
            [['quantity_received'], 'required'],
            [['description'], 'required', 'when' => function ($model) {
                return empty($model->po_item_id);
            }, 'message' => 'Description is required for items not tied to a PO.'],
            [['grn_id', 'po_item_id', 'catalog_item_id', 'quantity_received'], 'integer'],
            [['quantity_received'], 'integer', 'min' => 0],
            [['unit_price', 'total_price'], 'number'],
            [['description'], 'string', 'max' => 255],
            [['remarks'], 'string', 'max' => 255],
        ];
    }

    public function attributeLabels()
    {
        return [
            'quantity_received' => 'Qty Received (this delivery)',
            'unit_price' => 'Unit Price',
        ];
    }

    public function getPoItem()
    {
        return $this->hasOne(PoItem::class, ['id' => 'po_item_id']);
    }

    public function getCatalogItem()
    {
        return $this->hasOne(ItemCatalog::class, ['id' => 'catalog_item_id']);
    }

    public function getGoodsReceipt()
    {
        return $this->hasOne(GoodsReceipt::class, ['id' => 'grn_id']);
    }

    /**
     * Description to show/use, whichever source it came from - the PO
     * line's own description, or this line's own free-text one for a
     * direct (PO-less) receipt.
     */
    public function getDisplayDescription()
    {
        return $this->poItem->description ?? $this->description;
    }

    public function beforeSave($insert)
    {
        if ($this->unit_price !== null && $this->unit_price !== '') {
            $this->total_price = $this->quantity_received * $this->unit_price;
        }
        return parent::beforeSave($insert);
    }

    public static function createMultiple($modelClass, $multipleModels = [])
    {
        $model = new $modelClass;
        $formName = $model->formName();
        $post = Yii::$app->request->post($formName);
        $models = [];

        if (!empty($post) && is_array($post)) {
            foreach ($post as $i => $item) {
                if (isset($multipleModels[$i])) {
                    $models[] = $multipleModels[$i];
                } else {
                    $models[] = new $modelClass;
                }
            }
        } else {
            $models = $multipleModels ?: [$model];
        }

        return $models;
    }
}