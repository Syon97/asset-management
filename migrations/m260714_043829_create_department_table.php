<?php
use yii\db\Migration;

class m260714_043829_create_department_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('department', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'parent_id' => $this->integer()->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_department_parent', 'department', 'parent_id', 'department', 'id', 'SET NULL', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_department_parent', 'department');
        $this->dropTable('department');
    }
}