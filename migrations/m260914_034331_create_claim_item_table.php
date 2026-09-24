<?php
use yii\db\Migration;

class m260914_034331_create_claim_item_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('claim_item', [
            'id' => $this->primaryKey(),
            'claim_id' => $this->integer()->notNull(),
            'item_date' => $this->date()->notNull(),
            'particular' => $this->string(255)->null(),
            'location_company' => $this->string(255)->null(),
            'purpose' => $this->string(255)->null(),
            'vehicle_type' => "ENUM('car','motorcycle') NULL",
            'distance_km' => $this->decimal(8, 2)->null(),
            'amount' => $this->decimal(12, 2)->notNull()->defaultValue(0),
            'has_receipt' => $this->boolean()->notNull()->defaultValue(false),
            'receipt_file' => $this->string(255)->null(),
            'remarks' => $this->string(255)->null(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_claim_item_claim', 'claim_item', 'claim_id', 'claim', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('claim_item');
    }
}