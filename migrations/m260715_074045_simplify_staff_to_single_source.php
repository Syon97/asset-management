<?php
use yii\db\Migration;

class m260715_074045_simplify_staff_to_single_source extends Migration
{
    public function safeUp()
    {
        $this->dropIndex('idx_staff_source_unique', 'staff');
        $this->dropColumn('staff', 'source_db');
        $this->createIndex('idx_staff_id_unique', 'staff', 'staff_id', true);
    }

    public function safeDown()
    {
        $this->dropIndex('idx_staff_id_unique', 'staff');
        $this->addColumn('staff', 'source_db', $this->string(10)->null()->after('id'));
        $this->createIndex('idx_staff_source_unique', 'staff', ['source_db', 'staff_id'], true);
    }
}