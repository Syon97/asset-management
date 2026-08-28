<?php
use yii\db\Migration;

class m260727_094606_create_grn_item_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('grn_item', [
            'id' => $this->primaryKey(),
            'grn_id' => $this->integer()->notNull(),
            'po_item_id' => $this->integer()->notNull(),
            'catalog_item_id' => $this->integer()->null(),

            'quantity_received' => $this->integer()->notNull()->defaultValue(0),
            'remarks' => $this->string(255)->null(),
        ]);

        $this->addForeignKey('fk_grn_item_grn', 'grn_item', 'grn_id', 'goods_receipt', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_grn_item_po_item', 'grn_item', 'po_item_id', 'po_item', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_grn_item_catalog_item', 'grn_item', 'catalog_item_id', 'item_catalog', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('grn_item');
    }
}