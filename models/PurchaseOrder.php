<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class PurchaseOrder extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PARTIALLY_RECEIVED = 'partially_received';
    const STATUS_RECEIVED = 'received';
    const STATUS_CLOSED = 'closed';
    const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return 'purchase_order';
    }

    public function rules()
    {
        return [
            [['po_date', 'pr_id', 'supplier_id', 'prepared_by'], 'required'],
            [['pr_id', 'supplier_id', 'department_id', 'cost_center_id', 'prepared_by',
              'approved_by', 'rejected_by', 'closed_by'], 'integer'],
            [['po_date', 'approved_at', 'rejected_at', 'closed_at'], 'safe'],
            [['delivery_address', 'remarks', 'rejection_reason'], 'string'],
            [['payment_terms'], 'string', 'max' => 191],
            [['po_no'], 'string', 'max' => 30],
            [['erp_po_no'], 'string', 'max' => 30],
            [['status'], 'in', 'range' => [
                self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_PARTIALLY_RECEIVED,
                self::STATUS_RECEIVED, self::STATUS_CLOSED, self::STATUS_REJECTED,
            ]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'po_no' => 'PO No.',
            'erp_po_no' => 'ERP PO No.',
            'po_date' => 'Date',
            'pr_id' => 'Source PR',
            'supplier_id' => 'Supplier',
            'prepared_by' => 'Prepared By',
            'delivery_address' => 'Delivery Address',
            'payment_terms' => 'Payment Terms',
        ];
    }

    public static function statusLabels()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SENT => 'Sent',
            self::STATUS_PARTIALLY_RECEIVED => 'Partially Received',
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    public function getPurchaseRequisition()
    {
        return $this->hasOne(PurchaseRequisition::class, ['id' => 'pr_id']);
    }

    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'supplier_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
    }

    public function getCostCenter()
    {
        return $this->hasOne(CostCenter::class, ['id' => 'cost_center_id']);
    }

    public function getPreparedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'prepared_by']);
    }

    public function getApprovedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'approved_by']);
    }

    public function getRejectedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'rejected_by']);
    }

    public function getClosedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'closed_by']);
    }

    public function getItems()
    {
        return $this->hasMany(PoItem::class, ['po_id' => 'id']);
    }

    public function getAttachments()
    {
        return $this->hasMany(PoAttachment::class, ['po_id' => 'id']);
    }

    public function getTotalAmount()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->total_price;
        }
        return $total;
    }

    public function canReject()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canApprove()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canClose()
    {
        return in_array($this->status, [self::STATUS_SENT, self::STATUS_PARTIALLY_RECEIVED, self::STATUS_RECEIVED]);
    }

    public static function generatePoNo()
    {
        $year = date('Y');
        $count = self::find()
            ->where(['like', 'po_no', "PO-{$year}-"])
            ->count();
        $next = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        return "PO-{$year}-{$next}";
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->po_no)) {
            $this->po_no = self::generatePoNo();
        }
        if ($insert && empty($this->status)) {
            $this->status = self::STATUS_DRAFT;
        }
        return parent::beforeSave($insert);
    }
}