<?php
use yii\db\Migration;

class m260914_012504_create_mileage_rate_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('mileage_rate', [
            'id' => $this->primaryKey(),
            'vehicle_type' => "ENUM('car','motorcycle') NOT NULL UNIQUE",
            'rate_per_km' => $this->decimal(6, 2)->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->batchInsert('mileage_rate', ['vehicle_type', 'rate_per_km'], [
            ['car', 0.50],
            ['motorcycle', 0.30],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('mileage_rate');
    }
}