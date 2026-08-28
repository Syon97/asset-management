<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class StockIssueSearch extends StockIssue
{
    public function rules()
    {
        return [
            [['id', 'catalog_item_id', 'department_id', 'holder_id', 'issued_by'], 'integer'],
            [['holder_type', 'issue_date'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = StockIssue::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['issue_date' => SORT_DESC]],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'catalog_item_id' => $this->catalog_item_id,
            'department_id' => $this->department_id,
            'holder_type' => $this->holder_type,
            'issue_date' => $this->issue_date,
        ]);

        return $dataProvider;
    }
}