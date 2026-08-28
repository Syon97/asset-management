<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class HardwareAssetSearch extends HardwareAsset
{
    public function rules()
    {
        return [
            [['id', 'category_id', 'department_id', 'supplier_id'], 'integer'],
            [['asset_tag', 'brand', 'model', 'serial_no', 'status'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = HardwareAsset::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['id' => SORT_DESC]],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
            'department_id' => $this->department_id,
            'supplier_id' => $this->supplier_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'asset_tag', $this->asset_tag])
            ->andFilterWhere(['like', 'brand', $this->brand])
            ->andFilterWhere(['like', 'model', $this->model])
            ->andFilterWhere(['like', 'serial_no', $this->serial_no]);

        return $dataProvider;
    }
}