<?php
use yii\db\Migration;

class m260803_040603_create_po_attachment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('po_attachment', [
            'id' => $this->primaryKey(),
            'po_id' => $this->integer()->notNull(),
            'original_name' => $this->string(255)->notNull(),
            'stored_name' => $this->string(255)->notNull(),
            'uploaded_by' => $this->integer()->notNull(),
            'remarks' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_poa_po', 'po_attachment', 'po_id', 'purchase_order', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_poa_uploaded_by', 'po_attachment', 'uploaded_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('po_attachment');
    }
}