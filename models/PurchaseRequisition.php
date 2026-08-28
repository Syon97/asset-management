<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class PurchaseRequisition extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VERIFIED = 'verified';
    const STATUS_REVIEWED = 'reviewed';
    const STATUS_RECEIVED = 'received';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    const ORDER_TYPE_NEW = 'new_order';
    const ORDER_TYPE_REPEAT = 'repeat_order';

    /**
     * Ordered list of statuses a PR moves through on its way to approval.
     * Used to figure out "which stage was this rejected at" and to drive
     * the next-action button in the view.
     */
    public static function statusChain()
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_VERIFIED,
            self::STATUS_REVIEWED,
            self::STATUS_RECEIVED,
            self::STATUS_APPROVED,
        ];
    }

    public static function tableName()
    {
        return 'purchase_requisition';
    }

    public function behaviors()
    {
        return [TimestampBehavior::class];
    }

    public function rules()
    {
        return [
            [['pr_date', 'requested_by'], 'required'],
            [[
                'department_id', 'cost_center_id', 'requested_by',
                'verified_by', 'reviewed_by', 'received_by', 'approved_by', 'rejected_by',
            ], 'integer'],
            [['pr_date', 'verified_at', 'reviewed_at', 'received_at', 'approved_at', 'rejected_at'], 'safe'],
            [['purpose', 'rejection_reason'], 'string'],
            [['status'], 'in', 'range' => [
                self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_VERIFIED,
                self::STATUS_REVIEWED, self::STATUS_RECEIVED, self::STATUS_APPROVED, self::STATUS_REJECTED,
            ]],
            [['order_type'], 'in', 'range' => [self::ORDER_TYPE_NEW, self::ORDER_TYPE_REPEAT]],
            [['order_type'], 'default', 'value' => self::ORDER_TYPE_NEW],
            [['pr_no'], 'string', 'max' => 30],
            [['account_code', 'customer_code'], 'string', 'max' => 50],
            [['rejected_stage'], 'string', 'max' => 20],
        ];
    }

    public function attributeLabels()
    {
        return [
            'pr_no' => 'PR No.',
            'pr_date' => 'Date',
            'department_id' => 'Dept',
            'cost_center_id' => 'Cost Center',
            'account_code' => 'Account Code',
            'customer_code' => 'Customer Code',
            'order_type' => 'Order Type',
            'requested_by' => 'Requested By',
            'verified_by' => 'Verified By',
            'reviewed_by' => 'Reviewed By',
            'received_by' => 'Received By',
            'approved_by' => 'Approved By',
            'rejected_by' => 'Rejected By',
        ];
    }

    /**
     * @return string[] order_type => label
     */
    public static function optsOrderType()
    {
        return [
            self::ORDER_TYPE_NEW => 'New Order',
            self::ORDER_TYPE_REPEAT => 'Repeat Order',
        ];
    }

    /**
     * Known customer codes printed on the real PR form. Kept as a simple
     * fixed list (not a master-data table) since it's a short, rarely
     * changing set; "Other" allows free text for anything not listed.
     * @return string[]
     */
    public static function optsCustomerCode()
    {
        return [
            'P&G-40' => 'P&G-40',
            '3M-60' => '3M-60',
            'DURACELL-70' => 'DURACELL-70',
            'LIVFUL-80' => 'LIVFUL-80',
            'GWmicrolab-91' => 'GWmicrolab-91',
        ];
    }

    public function getAttachments()
    {
        return $this->hasMany(PrAttachment::class, ['pr_id' => 'id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
    }

    public function getCostCenter()
    {
        return $this->hasOne(CostCenter::class, ['id' => 'cost_center_id']);
    }

    public function getRequestedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'requested_by']);
    }

    public function getVerifiedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'verified_by']);
    }

    public function getReviewedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'reviewed_by']);
    }

    public function getReceivedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'received_by']);
    }

    public function getApprovedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'approved_by']);
    }

    public function getRejectedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'rejected_by']);
    }

    public function getItems()
    {
        return $this->hasMany(PrItem::class, ['pr_id' => 'id']);
    }

    public function getTotalAmount()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->total_price;
        }
        return $total;
    }

    /**
     * Can this PR still be rejected from its current status?
     */
    public function canReject()
    {
        return in_array($this->status, [
            self::STATUS_SUBMITTED, self::STATUS_VERIFIED, self::STATUS_REVIEWED, self::STATUS_RECEIVED,
        ]);
    }

    public static function generatePrNo()
    {
        $year = date('Y');
        $count = self::find()
            ->where(['like', 'pr_no', "PR-{$year}-"])
            ->count();
        $next = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        return "PR-{$year}-{$next}";
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->pr_no)) {
            $this->pr_no = self::generatePrNo();
        }
        if ($insert && empty($this->status)) {
            $this->status = self::STATUS_DRAFT;
        }
        if ($insert && empty($this->order_type)) {
            $this->order_type = self::ORDER_TYPE_NEW;
        }
        return parent::beforeSave($insert);
    }
}