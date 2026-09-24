<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

class ClaimReportSearch extends Model
{
    public $staffId;
    public $categoryId;
    public $status;
    public $month; // 1-12
    public $year;

    public function rules()
    {
        return [
            [['staffId', 'categoryId', 'month', 'year'], 'integer'],
            [['status'], 'string'],
        ];
    }

    public function search($params)
    {
        $this->load($params, '');

        $query = Claim::find()->with('staff', 'claimCategory');

        $query->andFilterWhere(['staff_id' => $this->staffId]);
        $query->andFilterWhere(['claim_category_id' => $this->categoryId]);
        $query->andFilterWhere(['status' => $this->status]);

        if (!empty($this->year)) {
            $month = !empty($this->month) ? str_pad($this->month, 2, '0', STR_PAD_LEFT) : null;
            if ($month) {
                $start = "{$this->year}-{$month}-01 00:00:00";
                $end = date('Y-m-d 23:59:59', strtotime("{$start} +1 month -1 day"));
            } else {
                $start = "{$this->year}-01-01 00:00:00";
                $end = "{$this->year}-12-31 23:59:59";
            }
            $query->andWhere(['between', 'created_at', $start, $end]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
            'pagination' => ['pageSize' => 50],
        ]);

        return $dataProvider;
    }

    /**
     * Summary totals for the current filter - by category and by status,
     * computed against the same filtered set (not the paginated page).
     */
    public function summarize($params)
    {
        $this->load($params, '');

        $base = Claim::find();
        $base->andFilterWhere(['staff_id' => $this->staffId]);
        $base->andFilterWhere(['claim_category_id' => $this->categoryId]);
        $base->andFilterWhere(['status' => $this->status]);
        if (!empty($this->year)) {
            $month = !empty($this->month) ? str_pad($this->month, 2, '0', STR_PAD_LEFT) : null;
            if ($month) {
                $start = "{$this->year}-{$month}-01 00:00:00";
                $end = date('Y-m-d 23:59:59', strtotime("{$start} +1 month -1 day"));
            } else {
                $start = "{$this->year}-01-01 00:00:00";
                $end = "{$this->year}-12-31 23:59:59";
            }
            $base->andWhere(['between', 'created_at', $start, $end]);
        }

        $byCategory = (clone $base)
            ->select(['claim_category_id', 'total' => 'SUM(total_amount)', 'cnt' => 'COUNT(*)'])
            ->groupBy('claim_category_id')
            ->asArray()
            ->all();

        $categoryNames = ClaimCategory::find()->select(['name'])->indexBy('id')->column();
        foreach ($byCategory as &$row) {
            $row['category_name'] = $categoryNames[$row['claim_category_id']] ?? 'Unknown';
        }
        unset($row);

        $byStatus = (clone $base)
            ->select(['status', 'total' => 'SUM(total_amount)', 'cnt' => 'COUNT(*)'])
            ->groupBy('status')
            ->asArray()
            ->all();

        $grandTotal = (clone $base)->sum('total_amount');
        $grandCount = (clone $base)->count();

        return [
            'byCategory' => $byCategory,
            'byStatus' => $byStatus,
            'grandTotal' => (float) $grandTotal,
            'grandCount' => (int) $grandCount,
        ];
    }
}