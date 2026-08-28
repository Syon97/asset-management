<?php

namespace app\models;

use Yii;

class AccessoryType extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'accessory_type';
    }

    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 50],
            [['name'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'name' => 'Accessory Type',
        ];
    }
}