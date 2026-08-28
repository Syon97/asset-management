<?php
use yii\db\Migration;

class m260727_094504_create_goods_receipt_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('goods_receipt', [
            'id' => $this->primaryKey(),
            'grn_no' => $this->string(30)->notNull()->unique(),
            'grn_date' => $this->date()->notNull(),

            'po_id' => $this->integer()->notNull(),
            'department_id' => $this->integer()->null(),
            'received_by' => $this->integer()->notNull(),

            'status' => "ENUM('draft','approved','rejected') NOT NULL DEFAULT 'draft'",

            'approved_by' => $this->integer()->null(),
            'approved_at' => $this->dateTime()->null(),
            'rejected_by' => $this->integer()->null(),
            'rejected_at' => $this->dateTime()->null(),
            'rejection_reason' => $this->text()->null(),

            'remarks' => $this->text()->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_grn_po', 'goods_receipt', 'po_id', 'purchase_order', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_grn_department', 'goods_receipt', 'department_id', 'department', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_grn_received_by', 'goods_receipt', 'received_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_grn_approved_by', 'goods_receipt', 'approved_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_grn_rejected_by', 'goods_receipt', 'rejected_by', 'staff', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('goods_receipt');
    }
}