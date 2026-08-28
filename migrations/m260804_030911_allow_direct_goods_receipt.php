<?php
use yii\db\Migration;

/**
 * Allows a Goods Receipt to exist without a source PO, for ad-hoc purchases
 * (e.g. Shopee/Lazada) that never went through the PR/PO workflow.
 * department_id already exists and is populated on both paths, so it's
 * used as-is; supplier_id is new, for record-keeping on direct purchases.
 */
class m260804_030911_allow_direct_goods_receipt extends Migration
{
    public function safeUp()
    {
        $this->alterColumn('goods_receipt', 'po_id', $this->integer()->null());

        if (!$this->db->getTableSchema('goods_receipt')->getColumn('supplier_id')) {
            $this->addColumn('goods_receipt', 'supplier_id', $this->integer()->null()->after('po_id'));
            $this->addForeignKey('fk_grn_supplier', 'goods_receipt', 'supplier_id', 'supplier', 'id', 'SET NULL', 'CASCADE');
        }
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_grn_supplier', 'goods_receipt');
        $this->dropColumn('goods_receipt', 'supplier_id');
        $this->alterColumn('goods_receipt', 'po_id', $this->integer()->notNull());
    }
}