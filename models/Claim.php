<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Claim extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_PAID = 'paid';

    public static function tableName()
    {
        return 'claim';
    }

    public function rules()
    {
        return [
            [['staff_id', 'claim_category_id'], 'required'],
            [['staff_id', 'claim_category_id', 'approved_by', 'rejected_by', 'paid_by'], 'integer'],
            [['status'], 'in', 'range' => [
                self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_VERIFIED,
                self::STATUS_APPROVED, self::STATUS_REJECTED, self::STATUS_PAID,
            ]],
            [['submitted_at', 'verified_at', 'approved_at', 'rejected_at', 'paid_at'], 'safe'],
            [['total_amount'], 'number'],
            [['remarks', 'rejection_reason'], 'string'],
            [['claim_no'], 'string', 'max' => 30],
        ];
    }

    public function attributeLabels()
    {
        return [
            'claim_no' => 'Claim No.',
            'claim_category_id' => 'Category',
            'total_amount' => 'Total Amount (RM)',
        ];
    }

    public static function statusLabels()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_VERIFIED => 'Verified',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_PAID => 'Paid',
        ];
    }

    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id']);
    }

    public function getClaimCategory()
    {
        return $this->hasOne(ClaimCategory::class, ['id' => 'claim_category_id']);
    }

    public function getApprovedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'approved_by']);
    }

    public function getRejectedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'rejected_by']);
    }

    public function getPaidByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'paid_by']);
    }

    public function getItems()
    {
        return $this->hasMany(ClaimItem::class, ['claim_id' => 'id']);
    }

    public function getStatusHistory()
    {
        return $this->hasMany(ClaimStatusHistory::class, ['claim_id' => 'id'])->orderBy(['changed_at' => SORT_ASC]);
    }

    public function canEdit()
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    /**
     * Sum of this employee's claims in this category so far this year
     * that count against the annual limit - i.e. anything that's passed
     * validation (verified/approved/paid), not drafts or rejections.
     * Uses created_at's year as the limit period, since claims don't
     * currently have their own single "claim date" - items can each have
     * their own date, so this is a reasonable approximation rather than
     * trying to bucket by item dates.
     */
    public static function usedAmountThisYear($staffId, $categoryId, $excludeClaimId = null)
    {
        $query = self::find()
            ->where(['staff_id' => $staffId, 'claim_category_id' => $categoryId])
            ->andWhere(['status' => [self::STATUS_VERIFIED, self::STATUS_APPROVED, self::STATUS_PAID]])
            ->andWhere(['between', 'created_at', date('Y-01-01 00:00:00'), date('Y-12-31 23:59:59')]);

        if ($excludeClaimId) {
            $query->andWhere(['<>', 'id', $excludeClaimId]);
        }

        return (float) $query->sum('total_amount');
    }

    /**
     * Shared by the Dashboard card and the sidebar badge, so both always
     * agree with each other and with the actual approval queue.
     */
    public static function pendingApprovalCount()
    {
        return (int) self::find()->where(['status' => self::STATUS_VERIFIED])->count();
    }

    public static function generateClaimNo()
    {
        $year = date('Y');
        $count = self::find()
            ->where(['like', 'claim_no', "CLM-{$year}-"])
            ->count();
        $next = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        return "CLM-{$year}-{$next}";
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->claim_no)) {
            $this->claim_no = self::generateClaimNo();
        }
        if ($insert && empty($this->status)) {
            $this->status = self::STATUS_DRAFT;
        }
        return parent::beforeSave($insert);
    }
}