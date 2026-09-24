<?php
use yii\db\Migration;

class m260914_012035_create_claim_category_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('claim_category', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100)->notNull()->unique(),
            'annual_limit_amount' => $this->decimal(12, 2)->null(),
            'is_mileage_type' => $this->boolean()->notNull()->defaultValue(false),
            'status' => "ENUM('active','inactive') NOT NULL DEFAULT 'active'",
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->batchInsert('claim_category', ['name', 'annual_limit_amount', 'is_mileage_type'], [
            ['Medical', 300.00, false],
            ['Staff Welfare / Special', 600.00, false],
            ['Mileage', null, true],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('claim_category');
    }
}