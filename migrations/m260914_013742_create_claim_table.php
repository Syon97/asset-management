<?php
use yii\db\Migration;

class m260914_013742_create_claim_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('claim', [
            'id' => $this->primaryKey(),
            'claim_no' => $this->string(30)->notNull()->unique(),
            'staff_id' => $this->integer()->notNull(),
            'claim_category_id' => $this->integer()->notNull(),
            'status' => "ENUM('draft','submitted','verified','approved','rejected','paid') NOT NULL DEFAULT 'draft'",
            'submitted_at' => $this->dateTime()->null(),
            'verified_at' => $this->dateTime()->null(),
            'approved_by' => $this->integer()->null(),
            'approved_at' => $this->dateTime()->null(),
            'rejected_by' => $this->integer()->null(),
            'rejected_at' => $this->dateTime()->null(),
            'rejection_reason' => $this->text()->null(),
            'paid_by' => $this->integer()->null(),
            'paid_at' => $this->dateTime()->null(),
            'total_amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'remarks' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_claim_staff', 'claim', 'staff_id', 'staff', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_claim_category', 'claim', 'claim_category_id', 'claim_category', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_claim_approved_by', 'claim', 'approved_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_claim_rejected_by', 'claim', 'rejected_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_claim_paid_by', 'claim', 'paid_by', 'staff', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('claim');
    }
}