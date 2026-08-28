<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class GoodsReceiptSearch extends GoodsReceipt
{
    public function rules()
    {
        return [
            [['id', 'po_id', 'supplier_id', 'department_id', 'received_by'], 'integer'],
            [['grn_no', 'grn_date', 'status', 'created_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = GoodsReceipt::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['grn_date' => SORT_DESC]],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'po_id' => $this->po_id,
            'supplier_id' => $this->supplier_id,
            'department_id' => $this->department_id,
            'received_by' => $this->received_by,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'grn_no', $this->grn_no]);

        return $dataProvider;
    }
}