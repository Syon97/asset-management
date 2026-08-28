<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class SoftwareLicenseSearch extends SoftwareLicense
{
    public function rules()
    {
        return [
            [['id', 'hardware_asset_id'], 'integer'],
            [['software_name', 'software_version', 'status'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = SoftwareLicense::find();

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
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'software_name', $this->software_name])
            ->andFilterWhere(['like', 'software_version', $this->software_version]);

        return $dataProvider;
    }
}