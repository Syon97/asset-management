<?php
use yii\db\Migration;

class m260714_043932_create_staff_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('staff', [
            'id' => $this->primaryKey(),
            'staff_id' => $this->string(20)->notNull()->unique(), // legacy staffid e.g. A0085
            'staff_name' => $this->string(255)->notNull(),
            'employment_type' => $this->string(50)->null(), // PERMANENT / CONTRACT
            'agency' => $this->string(255)->null(),
            'status' => $this->string(50)->null(), // PROBATION / CONFIRMED
            'join_date' => $this->date()->null(),
            'department_id' => $this->integer()->null(),
            'position' => $this->string(255)->null(),
            'location' => $this->string(255)->null(),
            'photo' => $this->string(300)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey(
            'fk_staff_department', 'staff', 'department_id', 'department', 'id', 'SET NULL', 'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('fk_staff_department', 'staff');
        $this->dropTable('staff');
    }
}