<?php
use yii\db\Migration;

/**
 * Adds the remaining per-line fields from the real Purchase Request form:
 * Type, Date Needed, Purpose (per line), and Quotation Ref No.
 */
class m260720_093050_add_form_fields_to_pr_item_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('pr_item', 'item_type', $this->string(50)->null()->after('catalog_item_id'));
        $this->addColumn('pr_item', 'needed_date', $this->date()->null()->after('quantity'));
        $this->addColumn('pr_item', 'purpose', $this->text()->null()->after('remarks'));
        $this->addColumn('pr_item', 'quotation_ref_no', $this->string(100)->null()->after('purpose'));
    }

    public function safeDown()
    {
        $this->dropColumn('pr_item', 'quotation_ref_no');
        $this->dropColumn('pr_item', 'purpose');
        $this->dropColumn('pr_item', 'needed_date');
        $this->dropColumn('pr_item', 'item_type');
    }
}