<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PoItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'po_item';
    }

    public function rules()
    {
        return [
            [['description', 'quantity'], 'required'],
            [['po_id', 'pr_item_id', 'catalog_item_id', 'quantity', 'quantity_received'], 'integer'],
            [['unit_price', 'total_price'], 'number'],
            [['description', 'specification', 'remarks', 'item_type'], 'string', 'max' => 255],
            [['quantity'], 'integer', 'min' => 1],
        ];
    }

    public function getCatalogItem()
    {
        return $this->hasOne(ItemCatalog::class, ['id' => 'catalog_item_id']);
    }

    public function attributeLabels()
    {
        return [
            'item_type' => 'Type',
            'quantity_received' => 'Qty Received',
        ];
    }

    public function getPrItem()
    {
        return $this->hasOne(PrItem::class, ['id' => 'pr_item_id']);
    }

    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, ['id' => 'po_id']);
    }

    public function beforeSave($insert)
    {
        $this->total_price = $this->quantity * $this->unit_price;
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