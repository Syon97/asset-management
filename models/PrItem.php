<?php

namespace app\models;

use yii\db\ActiveRecord;
use Yii;

class PrItem extends ActiveRecord
{
    public static function tableName()
    {
        return 'pr_item';
    }

    public function rules()
    {
        return [
            [['quantity'], 'required'],
            [['description'], 'required', 'when' => function ($model) {
                return empty($model->catalog_item_id);
            }, 'message' => 'Description is required when no Catalog Item is selected.'],
            [['pr_id', 'catalog_item_id', 'quantity'], 'integer'],
            [['unit_price', 'total_price'], 'number'],
            [['needed_date'], 'safe'],
            [['purpose'], 'string'],
            [['description', 'specification', 'remarks', 'item_type'], 'string', 'max' => 255],
            [['quotation_ref_no'], 'string', 'max' => 100],
            [['quantity'], 'integer', 'min' => 1],
        ];
    }

    public function attributeLabels()
    {
        return [
            'catalog_item_id' => 'Catalog Item',
            'item_type' => 'Type',
            'needed_date' => 'Date Needed',
            'quotation_ref_no' => 'Quotation Ref No.',
        ];
    }

    public function getCatalogItem()
    {
        return $this->hasOne(ItemCatalog::class, ['id' => 'catalog_item_id']);
    }

    public function getPurchaseRequisition()
    {
        return $this->hasOne(PurchaseRequisition::class, ['id' => 'pr_id']);
    }

    public function beforeSave($insert)
    {
        $this->total_price = $this->quantity * $this->unit_price;

        if (empty($this->description) && $this->catalog_item_id) {
            $catalogItem = $this->catalogItem ?: ItemCatalog::findOne($this->catalog_item_id);
            if ($catalogItem) {
                $this->description = $catalogItem->item_name;
            }
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