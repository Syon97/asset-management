<?php
use yii\db\Migration;

class m260921_072439_create_department_verifier_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('department_verifier', [
            'id' => $this->primaryKey(),
            'department_id' => $this->integer()->notNull()->unique(),
            'staff_id' => $this->integer()->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
        $this->addForeignKey('fk_dept_verifier_dept', 'department_verifier', 'department_id', 'department', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_dept_verifier_staff', 'department_verifier', 'staff_id', 'staff', 'id', 'RESTRICT', 'CASCADE');

        // General-purpose key-value settings table - used here for the
        // single GM escalation contact, but reusable for any future
        // single-value setting without needing a one-row dedicated table.
        $this->createTable('app_setting', [
            'setting_key' => $this->string(100)->notNull(),
            'setting_value' => $this->string(255)->null(),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);
        $this->addPrimaryKey('pk_app_setting', 'app_setting', 'setting_key');
    }

    public function safeDown()
    {
        $this->dropTable('department_verifier');
        $this->dropTable('app_setting');
    }
}