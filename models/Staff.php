<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "staff".
 *
 * @property int $id
 * @property string $staff_id
 * @property string $staff_name
 * @property string|null $employment_type
 * @property string|null $agency
 * @property string|null $status
 * @property string|null $join_date
 * @property int|null $department_id
 * @property string|null $position
 * @property string|null $location
 * @property string|null $photo
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Staff extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'staff';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['staff_id', 'staff_name'], 'required'],
            [['department_id', 'source_sid'], 'integer'],
            [['synced_at', 'created_at', 'updated_at'], 'safe'],
            [['source_db'], 'string'],
            [['staff_id', 'staff_name', 'employment_type', 'agency', 'status', 'department_text',
            'position', 'email', 'section', 'duty_post'], 'string', 'max' => 255],
            [['contact_num'], 'string', 'max' => 50],
        ];
    }

    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'department_id']);
    }   

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'staff_id' => 'Staff ID',
            'staff_name' => 'Staff Name',
            'employment_type' => 'Employment Type',
            'agency' => 'Agency',
            'status' => 'Status',
            'join_date' => 'Join Date',
            'department_id' => 'Department ID',
            'position' => 'Position',
            'location' => 'Location',
            'photo' => 'Photo',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
