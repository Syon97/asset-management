<?php
use yii\db\Migration;

class m260715_080107_create_purchase_requisition_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('purchase_requisition', [
            'id' => $this->primaryKey(),
            'pr_no' => $this->string(30)->notNull()->unique(),
            'pr_date' => $this->date()->notNull(),
            'department_id' => $this->integer()->null(),
            'requested_by' => $this->integer()->null(), // staff.id
            'purpose' => $this->text()->null(),
            'status' => "ENUM('draft','submitted','checked','approved','rejected') NOT NULL DEFAULT 'draft'",
            'checked_by' => $this->integer()->null(),
            'checked_at' => $this->dateTime()->null(),
            'approved_by' => $this->integer()->null(),
            'approved_at' => $this->dateTime()->null(),
            'rejected_by' => $this->integer()->null(),
            'rejected_at' => $this->dateTime()->null(),
            'rejection_reason' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_pr_department', 'purchase_requisition', 'department_id', 'department', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_pr_requested_by', 'purchase_requisition', 'requested_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_pr_checked_by', 'purchase_requisition', 'checked_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_pr_approved_by', 'purchase_requisition', 'approved_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_pr_rejected_by', 'purchase_requisition', 'rejected_by', 'staff', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('purchase_requisition');
    }
}