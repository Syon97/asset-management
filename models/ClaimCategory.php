<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ClaimCategory extends ActiveRecord
{
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    public static function tableName()
    {
        return 'claim_category';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 100],
            [['name'], 'unique'],
            [['annual_limit_amount'], 'number'],
            [['is_mileage_type'], 'boolean'],
            [['status'], 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE]],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Category Name',
            'annual_limit_amount' => 'Annual Limit (RM)',
            'is_mileage_type' => 'Uses Mileage Fields (date/destination/vehicle/distance)',
            'status' => 'Status',
        ];
    }

    public static function statusLabels()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }

    public function hasLimit()
    {
        return $this->annual_limit_amount !== null;
    }

    public function beforeSave($insert)
    {
        if ($insert && empty($this->status)) {
            $this->status = self::STATUS_ACTIVE;
        }
        return parent::beforeSave($insert);
    }
}