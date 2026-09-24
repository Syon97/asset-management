<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserAccountSearch extends UserAccount
{
    public $staffName;

    public function rules()
    {
        return [
            [['id', 'staff_id'], 'integer'],
            [['username', 'role', 'status', 'staffName'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params, $formName = null)
    {
        $query = UserAccount::find()->with('staff');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 50],
            'sort' => [
                'defaultOrder' => ['username' => SORT_ASC],
                'attributes' => ['id', 'username', 'role', 'status', 'last_login_at'],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'role' => $this->role,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'username', $this->username]);

        if (!empty($this->staffName)) {
            $query->joinWith(['staff'])
                ->andFilterWhere(['like', 'staff.staff_name', $this->staffName]);
        }

        return $dataProvider;
    }
}