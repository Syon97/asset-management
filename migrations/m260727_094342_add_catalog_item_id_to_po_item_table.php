<?php
use yii\db\Migration;

class m260727_094342_add_catalog_item_id_to_po_item_table extends Migration
{
    public function safeUp()
    {
        if (!$this->db->getTableSchema('po_item')->getColumn('catalog_item_id')) {
            $this->addColumn('po_item', 'catalog_item_id', $this->integer()->null()->after('pr_item_id'));
            $this->addForeignKey('fk_po_item_catalog_item', 'po_item', 'catalog_item_id', 'item_catalog', 'id', 'SET NULL', 'CASCADE');
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_po_item_catalog_item', 'po_item');
        $this->dropColumn('po_item', 'catalog_item_id');
    }
}