<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class StaffSearch extends Staff
{
    public function rules()
    {
        return [
            [['id', 'department_id', 'source_sid'], 'integer'],
            [['staff_id', 'staff_name', 'employment_type', 'agency', 'status',
              'department_text', 'position', 'email', 'contact_num', 'section', 'duty_post',
              'synced_at', 'created_at', 'updated_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Staff::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'department_id' => $this->department_id,
            'source_sid' => $this->source_sid,
            'synced_at' => $this->synced_at,
        ]);

        $query->andFilterWhere(['like', 'staff_id', $this->staff_id])
            ->andFilterWhere(['like', 'staff_name', $this->staff_name])
            ->andFilterWhere(['like', 'employment_type', $this->employment_type])
            ->andFilterWhere(['like', 'agency', $this->agency])
            ->andFilterWhere(['like', 'status', $this->status])
            ->andFilterWhere(['like', 'department_text', $this->department_text])
            ->andFilterWhere(['like', 'position', $this->position])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'contact_num', $this->contact_num])
            ->andFilterWhere(['like', 'section', $this->section])
            ->andFilterWhere(['like', 'duty_post', $this->duty_post]);

        return $dataProvider;
    }
}