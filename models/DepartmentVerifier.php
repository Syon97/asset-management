<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class DepartmentVerifier extends ActiveRecord
{
    public static function tableName()
    {
        return 'department_verifier';
    }

    public function rules()
    {
        return [
            [['department_id', 'staff_id'], 'required'],
            [['department_id'], 'integer'],
            [['department_id'], 'unique'],
            [['staff_id'], 'integer'],
        ];
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
    }

    public function getStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'staff_id']);
    }
}