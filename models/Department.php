<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "department".
 *
 * @property int $id
 * @property string $name
 * @property int|null $parent_id
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Department extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'department';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['parent_id'], 'integer'],
            [['parent_id'], 'exist', 'targetClass' => Department::class, 'targetAttribute' => 'id'],
        ];
    }

    public function getParent()
    {
        return $this->hasOne(Department::class, ['id' => 'parent_id']);
    }

    public function getChildren()
    {
        return $this->hasMany(Department::class, ['parent_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'parent_id' => 'Parent ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
