<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "project".
 *
 * @property int $id
 * @property string $project_code
 * @property string $project_name
 * @property int|null $department_id
 * @property string $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Project extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ON_HOLD = 'on_hold';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'project';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['department_id'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 'active'],
            [['project_code', 'project_name'], 'required'],
            [['department_id'], 'integer'],
            [['status'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['project_code'], 'string', 'max' => 50],
            [['project_name'], 'string', 'max' => 255],
            ['status', 'in', 'range' => array_keys(self::optsStatus())],
            [['project_code'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_code' => 'Project Code',
            'project_name' => 'Project Name',
            'department_id' => 'Department ID',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * column status ENUM value labels
     * @return string[]
     */
    public static function optsStatus()
    {
        return [
            self::STATUS_ACTIVE => 'active',
            self::STATUS_COMPLETED => 'completed',
            self::STATUS_ON_HOLD => 'on_hold',
        ];
    }

    /**
     * @return string
     */
    public function displayStatus()
    {
        return self::optsStatus()[$this->status];
    }

    /**
     * @return bool
     */
    public function isStatusActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function setStatusToActive()
    {
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * @return bool
     */
    public function isStatusCompleted()
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function setStatusToCompleted()
    {
        $this->status = self::STATUS_COMPLETED;
    }

    /**
     * @return bool
     */
    public function isStatusOnhold()
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function setStatusToOnhold()
    {
        $this->status = self::STATUS_ON_HOLD;
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
    }
}
