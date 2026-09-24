<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ClaimStatusHistorySearch extends Model
{
    public $claimNo;
    public $staffId;
    public $toStatus;

    public function rules()
    {
        return [
            [['staffId'], 'integer'],
            [['claimNo', 'toStatus'], 'string'],
        ];
    }

    public function search($params)
    {
        $this->load($params, '');

        $query = ClaimStatusHistory::find()->with('claim', 'changedByStaff');

        if (!empty($this->claimNo)) {
            $query->joinWith(['claim'])->andWhere(['like', 'claim.claim_no', $this->claimNo]);
        }
        $query->andFilterWhere(['claim_status_history.changed_by' => $this->staffId]);
        $query->andFilterWhere(['claim_status_history.to_status' => $this->toStatus]);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['changed_at' => SORT_DESC]],
            'pagination' => ['pageSize' => 50],
        ]);
    }
}