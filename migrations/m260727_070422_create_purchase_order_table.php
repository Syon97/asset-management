<?php
use yii\db\Migration;

/**
 * Purchase Order header, converted from an approved Purchase Requisition.
 * One PR -> one PO for now (kept simple per initial scope; revisit later
 * if splitting one PR's items across multiple suppliers/POs is needed).
 */
class m260727_070422_create_purchase_order_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('purchase_order', [
            'id' => $this->primaryKey(),
            'po_no' => $this->string(30)->notNull()->unique(),
            'po_date' => $this->date()->notNull(),

            'pr_id' => $this->integer()->notNull(),
            'supplier_id' => $this->integer()->notNull(),
            'department_id' => $this->integer()->null(),
            'cost_center_id' => $this->integer()->null(),
            'prepared_by' => $this->integer()->notNull(),

            'delivery_address' => $this->text()->null(),
            'payment_terms' => $this->string(191)->null(),
            'remarks' => $this->text()->null(),

            'status' => "ENUM('draft','sent','partially_received','received','closed','rejected') NOT NULL DEFAULT 'draft'",

            'approved_by' => $this->integer()->null(),
            'approved_at' => $this->dateTime()->null(),
            'rejected_by' => $this->integer()->null(),
            'rejected_at' => $this->dateTime()->null(),
            'rejection_reason' => $this->text()->null(),
            'closed_by' => $this->integer()->null(),
            'closed_at' => $this->dateTime()->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_po_pr', 'purchase_order', 'pr_id', 'purchase_requisition', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_po_supplier', 'purchase_order', 'supplier_id', 'supplier', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_po_department', 'purchase_order', 'department_id', 'department', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_po_cost_center', 'purchase_order', 'cost_center_id', 'cost_center', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_po_prepared_by', 'purchase_order', 'prepared_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_po_approved_by', 'purchase_order', 'approved_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_po_rejected_by', 'purchase_order', 'rejected_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_po_closed_by', 'purchase_order', 'closed_by', 'staff', 'id', 'SET NULL', 'CASCADE');

        // One PR can only ever have one PO for now.
        $this->createIndex('idx_po_pr_unique', 'purchase_order', 'pr_id', true);
    }

    public function safeDown()
    {
        $this->dropTable('purchase_order');
    }
}