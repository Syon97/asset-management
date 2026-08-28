<?php
use yii\db\Migration;

class m260727_094659_create_stock_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('stock', [
            'id' => $this->primaryKey(),
            'catalog_item_id' => $this->integer()->notNull(),
            'department_id' => $this->integer()->notNull(),
            'quantity_on_hand' => $this->integer()->notNull()->defaultValue(0),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_stock_catalog_item', 'stock', 'catalog_item_id', 'item_catalog', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_stock_department', 'stock', 'department_id', 'department', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_stock_item_location', 'stock', ['catalog_item_id', 'department_id'], true);
    }

    public function safeDown()
    {
        $this->dropTable('stock');
    }
}