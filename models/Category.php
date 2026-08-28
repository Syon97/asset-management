<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "category".
 *
 * @property int $id
 * @property string $category_name
 * @property string|null $description
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Category extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'category';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_name'], 'required'],
            [['category_name'], 'string', 'max' => 200],
            [['category_name'], 'unique'],
            [['description'], 'string'],
            [['tag_prefix'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_name' => 'Category Name',
            'tag_prefix' => 'Asset Tag Prefix',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

}