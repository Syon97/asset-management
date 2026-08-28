<?php
use yii\db\Migration;

/**
 * Hardware asset records. Fixes the legacy bug where product_price, buy_date,
 * warranty fields, and shop/supplier info were silently dropped on insert
 * (the legacy add_hardware.php commented out reading those POST values and
 * never included them in the INSERT column list at all).
 */
class m260729_021609_create_hardware_asset_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('hardware_asset', [
            'id' => $this->primaryKey(),
            'asset_tag' => $this->string(30)->notNull()->unique(),

            'category_id' => $this->integer()->null(),
            'department_id' => $this->integer()->null(),
            'supplier_id' => $this->integer()->null(),

            'brand' => $this->string(191)->null(),
            'model' => $this->string(191)->null(),
            'serial_no' => $this->string(191)->null(),

            'purchase_price' => $this->decimal(12, 2)->null(),
            'purchase_date' => $this->date()->null(),
            'warranty_start_date' => $this->date()->null(),
            'warranty_end_date' => $this->date()->null(),

            'product_image' => $this->string(255)->null(),
            'serial_image' => $this->string(255)->null(),
            'serialcommand_image' => $this->string(255)->null(),

            'status' => "ENUM('active','disposed') NOT NULL DEFAULT 'active'",
            'disposed_at' => $this->date()->null(),
            'disposal_remarks' => $this->text()->null(),

            'current_holder_type' => "ENUM('staff','project','department') NULL",
            'current_holder_id' => $this->integer()->null(),

            'remarks' => $this->text()->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_hw_category', 'hardware_asset', 'category_id', 'category', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_hw_department', 'hardware_asset', 'department_id', 'department', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_hw_supplier', 'hardware_asset', 'supplier_id', 'supplier', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('hardware_asset');
    }
}