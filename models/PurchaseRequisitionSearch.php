<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\PurchaseRequisition;

/**
 * PurchaseRequisitionSearch represents the model behind the search form of `app\models\PurchaseRequisition`.
 */
class PurchaseRequisitionSearch extends PurchaseRequisition
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'id', 'department_id', 'cost_center_id', 'requested_by',
                'verified_by', 'reviewed_by', 'received_by', 'approved_by', 'rejected_by',
            ], 'integer'],
            [[
                'pr_no', 'pr_date', 'purpose', 'status', 'order_type', 'account_code', 'customer_code',
                'verified_at', 'reviewed_at', 'received_at', 'approved_at', 'rejected_at',
                'rejection_reason', 'rejected_stage', 'created_at', 'updated_at',
            ], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = PurchaseRequisition::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['pr_date' => SORT_DESC]],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'pr_date' => $this->pr_date,
            'department_id' => $this->department_id,
            'cost_center_id' => $this->cost_center_id,
            'requested_by' => $this->requested_by,
            'verified_by' => $this->verified_by,
            'reviewed_by' => $this->reviewed_by,
            'received_by' => $this->received_by,
            'approved_by' => $this->approved_by,
            'rejected_by' => $this->rejected_by,
            'order_type' => $this->order_type,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'pr_no', $this->pr_no])
            ->andFilterWhere(['like', 'purpose', $this->purpose])
            ->andFilterWhere(['like', 'account_code', $this->account_code])
            ->andFilterWhere(['like', 'customer_code', $this->customer_code])
            ->andFilterWhere(['like', 'rejection_reason', $this->rejection_reason]);

        return $dataProvider;
    }
}