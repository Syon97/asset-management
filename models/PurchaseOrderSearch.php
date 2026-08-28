<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class PurchaseOrderSearch extends PurchaseOrder
{
    public function rules()
    {
        return [
            [['id', 'pr_id', 'supplier_id', 'department_id', 'cost_center_id', 'prepared_by'], 'integer'],
            [['po_no', 'erp_po_no', 'po_date', 'status', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = PurchaseOrder::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['po_date' => SORT_DESC]],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'po_date' => $this->po_date,
            'supplier_id' => $this->supplier_id,
            'department_id' => $this->department_id,
            'cost_center_id' => $this->cost_center_id,
            'prepared_by' => $this->prepared_by,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'po_no', $this->po_no])
            ->andFilterWhere(['like', 'erp_po_no', $this->erp_po_no]);

        return $dataProvider;
    }
}