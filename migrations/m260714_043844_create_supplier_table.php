<?php
use yii\db\Migration;

class m260714_043844_create_supplier_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('supplier', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'contact_person' => $this->string(255)->null(),
            'phone' => $this->string(50)->null(),
            'email' => $this->string(255)->null(),
            'address' => $this->text()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('supplier');
    }
}