<?php
use yii\db\Migration;

/**
 * Our internal PO record is a staging/bridge between an approved PR and
 * whatever the company's actual ERP system issues as the real Purchase
 * Order. erp_po_no records that real order number once it exists, so this
 * record can be traced back to the document of truth without our system
 * trying to duplicate the ERP's own numbering/format.
 */
class m260803_040445_add_erp_po_no_to_purchase_order_table extends Migration
{
    public function safeUp()
    {
        if (!$this->db->getTableSchema('purchase_order')->getColumn('erp_po_no')) {
            $this->addColumn('purchase_order', 'erp_po_no', $this->string(30)->null()->after('po_no'));
        }
    }

    public function safeDown()
    {
        $this->dropColumn('purchase_order', 'erp_po_no');
    }
}