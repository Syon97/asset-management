<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "item_catalog".
 *
 * @property int $id
 * @property string $item_name
 * @property int|null $category_id
 * @property string $item_type
 * @property string|null $description
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class ItemCatalog extends \yii\db\ActiveRecord
{

    /**
     * ENUM field values
     */
    const ITEM_TYPE_HARDWARE = 'hardware';
    const ITEM_TYPE_ACCESSORY = 'accessory';
    const ITEM_TYPE_SOFTWARE = 'software';
    const ITEM_TYPE_CONSUMABLE = 'consumable';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'item_catalog';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'description'], 'default', 'value' => null],
            [['item_type'], 'default', 'value' => 'hardware'],
            [['item_name'], 'required'],
            [['category_id'], 'integer'],
            [['item_type', 'description'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['item_name'], 'string', 'max' => 255],
            ['item_type', 'in', 'range' => array_keys(self::optsItemType())],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'item_name' => 'Item Name',
            'category_id' => 'Category ID',
            'item_type' => 'Item Type',
            'description' => 'Description',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * column item_type ENUM value labels
     * @return string[]
     */
    public static function optsItemType()
    {
        return [
            self::ITEM_TYPE_HARDWARE => 'hardware',
            self::ITEM_TYPE_ACCESSORY => 'accessory',
            self::ITEM_TYPE_SOFTWARE => 'software',
            self::ITEM_TYPE_CONSUMABLE => 'consumable',
        ];
    }

    /**
     * @return string
     */
    public function displayItemType()
    {
        return self::optsItemType()[$this->item_type];
    }

    /**
     * @return bool
     */
    public function isItemTypeHardware()
    {
        return $this->item_type === self::ITEM_TYPE_HARDWARE;
    }

    public function setItemTypeToHardware()
    {
        $this->item_type = self::ITEM_TYPE_HARDWARE;
    }

    /**
     * @return bool
     */
    public function isItemTypeAccessory()
    {
        return $this->item_type === self::ITEM_TYPE_ACCESSORY;
    }

    public function setItemTypeToAccessory()
    {
        $this->item_type = self::ITEM_TYPE_ACCESSORY;
    }

    /**
     * @return bool
     */
    public function isItemTypeSoftware()
    {
        return $this->item_type === self::ITEM_TYPE_SOFTWARE;
    }

    public function setItemTypeToSoftware()
    {
        $this->item_type = self::ITEM_TYPE_SOFTWARE;
    }

    /**
     * @return bool
     */
    public function isItemTypeConsumable()
    {
        return $this->item_type === self::ITEM_TYPE_CONSUMABLE;
    }

    public function setItemTypeToConsumable()
    {
        $this->item_type = self::ITEM_TYPE_CONSUMABLE;
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }
}
