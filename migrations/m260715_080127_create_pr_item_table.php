<?php
use yii\db\Migration;

class m260715_080127_create_pr_item_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('pr_item', [
            'id' => $this->primaryKey(),
            'pr_id' => $this->integer()->notNull(),
            'catalog_item_id' => $this->integer()->null(),
            'description' => $this->string(255)->notNull(),
            'specification' => $this->string(255)->null(),
            'quantity' => $this->integer()->notNull()->defaultValue(1),
            'unit_price' => $this->decimal(12, 2)->null()->defaultValue(0),
            'total_price' => $this->decimal(12, 2)->null()->defaultValue(0),
            'remarks' => $this->string(255)->null(),
        ]);

        $this->addForeignKey('fk_pr_item_pr', 'pr_item', 'pr_id', 'purchase_requisition', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_pr_item_catalog', 'pr_item', 'catalog_item_id', 'item_catalog', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('pr_item');
    }
}