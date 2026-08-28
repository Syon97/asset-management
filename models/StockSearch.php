<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class StockSearch extends Stock
{
    public function rules()
    {
        return [
            [['id', 'catalog_item_id', 'department_id'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = Stock::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
            'sort' => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'catalog_item_id' => $this->catalog_item_id,
            'department_id' => $this->department_id,
        ]);

        return $dataProvider;
    }
}