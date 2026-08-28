<?php
use yii\db\Migration;

/**
 * grn_item normally pulls description/pricing from its po_item. For a
 * direct (PO-less) receipt there's no po_item to reference, so these
 * columns let a line stand on its own.
 */
class m260804_030959_allow_direct_grn_item extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('grn_item', 'po_item_id', $this->integer()->null());

        if (!$this->db->getTableSchema('grn_item')->getColumn('description')) {
            $this->addColumn('grn_item', 'description', $this->string(255)->null()->after('catalog_item_id'));
        }
        if (!$this->db->getTableSchema('grn_item')->getColumn('unit_price')) {
            $this->addColumn('grn_item', 'unit_price', $this->decimal(12, 2)->null());
        }
        if (!$this->db->getTableSchema('grn_item')->getColumn('total_price')) {
            $this->addColumn('grn_item', 'total_price', $this->decimal(12, 2)->null());
        }
    }

    public function safeDown()
    {
        $this->dropColumn('grn_item', 'total_price');
        $this->dropColumn('grn_item', 'unit_price');
        $this->dropColumn('grn_item', 'description');
        $this->alterColumn('grn_item', 'po_item_id', $this->integer()->notNull());
    }
}