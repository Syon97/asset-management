<?php
use yii\db\Migration;

class m260715_063110_add_hr_sync_fields_to_staff_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('staff', 'source_db', "ENUM('m1','m2','m3') NULL AFTER id");
        $this->addColumn('staff', 'source_sid', $this->integer()->null()->after('source_db'));
        $this->addColumn('staff', 'department_text', $this->string(255)->null()->after('department_id'));
        $this->addColumn('staff', 'email', $this->string(255)->null());
        $this->addColumn('staff', 'contact_num', $this->string(50)->null());
        $this->addColumn('staff', 'section', $this->string(100)->null());
        $this->addColumn('staff', 'duty_post', $this->string(200)->null());
        $this->addColumn('staff', 'synced_at', $this->timestamp()->null());

        $this->dropColumn('staff', 'join_date');
        $this->dropColumn('staff', 'location');
        $this->dropColumn('staff', 'photo');

        $this->createIndex('idx_staff_source_unique', 'staff', ['source_db', 'staff_id'], true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_staff_source_unique', 'staff');
        $this->addColumn('staff', 'join_date', $this->date()->null());
        $this->addColumn('staff', 'location', $this->string(255)->null());
        $this->addColumn('staff', 'photo', $this->string(300)->null());

        $this->dropColumn('staff', 'source_db');
        $this->dropColumn('staff', 'source_sid');
        $this->dropColumn('staff', 'department_text');
        $this->dropColumn('staff', 'email');
        $this->dropColumn('staff', 'contact_num');
        $this->dropColumn('staff', 'section');
        $this->dropColumn('staff', 'duty_post');
        $this->dropColumn('staff', 'synced_at');
    }
}