<?php
use yii\db\Migration;

class m260720_092658_create_cost_center_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('cost_center', [
            'id' => $this->primaryKey(),
            'code' => $this->string(20)->notNull()->unique(),
            'name' => $this->string(100)->notNull(),
            'is_active' => $this->boolean()->notNull()->defaultValue(1),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Seed with the cost center codes printed on the actual Purchase Request form.
        $this->batchInsert('cost_center', ['code', 'name'], [
            ['PROD-10', 'Production'],
            ['TECH-20', 'Technical'],
            ['QA-30', 'Quality Assurance'],
            ['STORE-40', 'Store'],
            ['SHIPPING-50', 'Shipping'],
            ['PURCHASING-60', 'Purchasing'],
            ['PLANNER-70', 'Planner'],
            ['FINANCE-80', 'Finance'],
            ['HR-90', 'Human Resource'],
            ['FAC-100', 'Facility'],
            ['R&D-101', 'Research & Development'],
            ['MIS-102', 'MIS / IT'],
            ['LAB-103', 'Laboratory'],
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('cost_center');
    }
}