<?php
use yii\db\Migration;

class m260914_034518_create_claim_status_history_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('claim_status_history', [
            'id' => $this->primaryKey(),
            'claim_id' => $this->integer()->notNull(),
            'from_status' => $this->string(20)->null(),
            'to_status' => $this->string(20)->notNull(),
            'changed_by' => $this->integer()->notNull(),
            'changed_at' => $this->dateTime()->notNull(),
            'note' => $this->text()->null(),
        ]);

        $this->addForeignKey('fk_claim_history_claim', 'claim_status_history', 'claim_id', 'claim', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_claim_history_staff', 'claim_status_history', 'changed_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('claim_status_history');
    }
}