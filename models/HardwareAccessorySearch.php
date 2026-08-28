<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class HardwareAccessorySearch extends HardwareAccessory
{
    public function rules()
    {
        return [
            [['id', 'hardware_asset_id', 'accessory_type_id'], 'integer'],
            [['accessory_no', 'status'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = HardwareAccessory::find();

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
            'hardware_asset_id' => $this->hardware_asset_id,
            'accessory_type_id' => $this->accessory_type_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'accessory_no', $this->accessory_no]);

        return $dataProvider;
    }
}