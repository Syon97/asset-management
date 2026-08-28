<?php
use yii\db\Migration;

class m260730_012551_create_accessory_type_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('accessory_type', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->unique(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Seed with the accessory types the legacy system had hardcoded as checkboxes.
        $this->batchInsert('accessory_type', ['name'], [
            ['Mouse'],
            ['Charger'],
            ['Pendrive'],
            ['Harddisk'],
            ['Bag'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('accessory_type');
    }
}