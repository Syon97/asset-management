<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class GoodsReceipt extends ActiveRecord
{
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public static function tableName()
    {
        return 'goods_receipt';
    }

    public function rules()
    {
        return [
            [['grn_date', 'received_by', 'department_id'], 'required'],
            [['po_id', 'supplier_id', 'department_id', 'received_by', 'approved_by', 'rejected_by'], 'integer'],
            [['external_ref_no'], 'string', 'max' => 50],
            [['grn_date', 'approved_at', 'rejected_at'], 'safe'],
            [['remarks', 'rejection_reason'], 'string'],
            [['grn_no'], 'string', 'max' => 30],
            [['status'], 'in', 'range' => [self::STATUS_DRAFT, self::STATUS_APPROVED, self::STATUS_REJECTED]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'grn_no' => 'GRN No.',
            'grn_date' => 'Date',
            'po_id' => 'Purchase Order',
            'supplier_id' => 'Supplier',
            'external_ref_no' => 'External Ref. No.',
            'received_by' => 'Received By',
        ];
    }

    public static function statusLabels()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * True when this receipt was never tied to a formal PR/PO (an ad-hoc
     * purchase e.g. from Shopee/Lazada).
     */
    public function isDirect()
    {
        return empty($this->po_id);
    }

    public function getPurchaseOrder()
    {
        return $this->hasOne(PurchaseOrder::class, ['id' => 'po_id']);
    }

    public function getSupplier()
    {
        return $this->hasOne(Supplier::class, ['id' => 'supplier_id']);
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
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
        return $this->hasMany(GrnItem::class, ['grn_id' => 'id']);
    }

    public function getAttachments()
    {
        return $this->hasMany(GrnAttachment::class, ['grn_id' => 'id']);
    }

    public function canApprove()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canReject()
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public static function generateGrnNo()
    {
        $year = date('Y');
        $count = self::find()
            ->where(['like', 'grn_no', "GRN-{$year}-"])
            ->count();
        $next = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        return "GRN-{$year}-{$next}";
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->grn_no)) {
            $this->grn_no = self::generateGrnNo();
        }
        if ($insert && empty($this->status)) {
            $this->status = self::STATUS_DRAFT;
        }
        return parent::beforeSave($insert);
    }
}