<?php
use yii\db\Migration;

class m260714_043856_create_item_catalog_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('item_catalog', [
            'id' => $this->primaryKey(),
            'item_name' => $this->string(255)->notNull(),
            'category_id' => $this->integer()->null(),
            'item_type' => "ENUM('hardware','accessory','software','consumable') NOT NULL DEFAULT 'hardware'",
            'description' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_item_catalog_category', 'item_catalog', 'category_id', 'category', 'id', 'SET NULL', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_item_catalog_category', 'item_catalog');
        $this->dropTable('item_catalog');
    }
}