<?php
use yii\db\Migration;

class m260730_071227_create_pr_attachment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('pr_attachment', [
            'id' => $this->primaryKey(),
            'pr_id' => $this->integer()->notNull(),
            'original_name' => $this->string(255)->notNull(),
            'stored_name' => $this->string(255)->notNull(),
            'uploaded_by' => $this->integer()->notNull(),
            'remarks' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_pra_pr', 'pr_attachment', 'pr_id', 'purchase_requisition', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_pra_uploaded_by', 'pr_attachment', 'uploaded_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('pr_attachment');
    }
}