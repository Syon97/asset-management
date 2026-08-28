<?php
use yii\db\Migration;

class m260727_071245_create_po_item_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('po_item', [
            'id' => $this->primaryKey(),
            'po_id' => $this->integer()->notNull(),
            'pr_item_id' => $this->integer()->null(),

            'description' => $this->string(255)->notNull(),
            'specification' => $this->string(255)->null(),
            'item_type' => $this->string(50)->null(),

            'quantity' => $this->integer()->notNull()->defaultValue(1),
            'unit_price' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'total_price' => $this->decimal(12, 2)->notNull()->defaultValue(0),

            // Not used until Phase 4 (Goods Receipt) actually posts receipts against it.
            'quantity_received' => $this->integer()->notNull()->defaultValue(0),

            'remarks' => $this->string(255)->null(),
        ]);

        $this->addForeignKey('fk_po_item_po', 'po_item', 'po_id', 'purchase_order', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_po_item_pr_item', 'po_item', 'pr_item_id', 'pr_item', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('po_item');
    }
}