<?php
use yii\db\Migration;

class m260714_043920_create_project_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('project', [
            'id' => $this->primaryKey(),
            'project_code' => $this->string(50)->notNull()->unique(),
            'project_name' => $this->string(255)->notNull(),
            'department_id' => $this->integer()->null(),
            'status' => "ENUM('active','completed','on_hold') NOT NULL DEFAULT 'active'",
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_project_department', 'project', 'department_id', 'department', 'id', 'SET NULL', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_project_department', 'project');
        $this->dropTable('project');
    }
}