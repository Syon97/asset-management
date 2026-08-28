<?php
use yii\db\Migration;

class m260804_031054_create_grn_attachment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('grn_attachment', [
            'id' => $this->primaryKey(),
            'grn_id' => $this->integer()->notNull(),
            'original_name' => $this->string(255)->notNull(),
            'stored_name' => $this->string(255)->notNull(),
            'uploaded_by' => $this->integer()->notNull(),
            'remarks' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_grna_grn', 'grn_attachment', 'grn_id', 'goods_receipt', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_grna_uploaded_by', 'grn_attachment', 'uploaded_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('grn_attachment');
    }
}