<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "cost_center".
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_active
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class CostCenter extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cost_center';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['code', 'name'], 'required'],
            [['is_active'], 'boolean'],
            [['is_active'], 'default', 'value' => 1],
            [['created_at', 'updated_at'], 'safe'],
            [['code'], 'string', 'max' => 20],
            [['name'], 'string', 'max' => 100],
            [['code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'code' => 'Cost Center Code',
            'name' => 'Cost Center Name',
            'is_active' => 'Active',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->code . ' - ' . $this->name;
    }
}