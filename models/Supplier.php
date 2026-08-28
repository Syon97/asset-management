<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "supplier".
 *
 * @property int $id
 * @property string $name
 * @property string|null $contact_person
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Supplier extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'supplier';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contact_person', 'phone', 'email', 'address'], 'default', 'value' => null],
            [['name'], 'required'],
            [['address'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'contact_person', 'email'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'contact_person' => 'Contact Person',
            'phone' => 'Phone',
            'email' => 'Email',
            'address' => 'Address',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}
