<?php
use yii\db\Migration;

/**
 * Holds the external order/invoice number for a receipt (e.g. a Shopee
 * order ID or a physical invoice number) - either typed manually or
 * pulled from an uploaded PDF via text extraction.
 */
class m260824_094346_add_external_ref_no_to_goods_receipt extends Migration
{
    public function safeUp()
    {
        if (!$this->db->getTableSchema('goods_receipt')->getColumn('external_ref_no')) {
            $this->addColumn('goods_receipt', 'external_ref_no', $this->string(50)->null()->after('supplier_id'));
        }
    }

    public function safeDown()
    {
        $this->dropColumn('goods_receipt', 'external_ref_no');
    }
}