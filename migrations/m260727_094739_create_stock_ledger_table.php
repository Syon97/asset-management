<?php
use yii\db\Migration;

class m260727_094739_create_stock_ledger_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('stock_ledger', [
            'id' => $this->primaryKey(),
            'catalog_item_id' => $this->integer()->notNull(),
            'department_id' => $this->integer()->notNull(),
            'movement_type' => "ENUM('in','out') NOT NULL",
            'quantity' => $this->integer()->notNull(),
            'reference_type' => $this->string(20)->notNull(),
            'reference_id' => $this->integer()->notNull(),
            'staff_id' => $this->integer()->null(),
            'notes' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_ledger_catalog_item', 'stock_ledger', 'catalog_item_id', 'item_catalog', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ledger_department', 'stock_ledger', 'department_id', 'department', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_ledger_staff', 'stock_ledger', 'staff_id', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->createIndex('idx_ledger_item_location', 'stock_ledger', ['catalog_item_id', 'department_id']);
    }

    public function safeDown()
    {
        $this->dropTable('stock_ledger');
    }
}