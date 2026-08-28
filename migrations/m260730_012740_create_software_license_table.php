<?php
use yii\db\Migration;

/**
 * Software licenses, properly FK'd to hardware_asset.id instead of the
 * legacy pattern of matching product_no as a plain unenforced varchar.
 * Perpetual licenses only (no expiry fields) per current scope.
 */
class m260730_012740_create_software_license_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('software_license', [
            'id' => $this->primaryKey(),
            'hardware_asset_id' => $this->integer()->null(),

            'software_name' => $this->string(191)->notNull(),
            'software_version' => $this->string(191)->null(),
            'license_key' => $this->string(255)->null(),

            'status' => "ENUM('assigned','unassigned') NOT NULL DEFAULT 'assigned'",

            'assigned_by' => $this->integer()->notNull(),
            'assigned_at' => $this->dateTime()->notNull(),
            'unassigned_by' => $this->integer()->null(),
            'unassigned_at' => $this->dateTime()->null(),
            'unassign_reason' => $this->text()->null(),

            'remarks' => $this->text()->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_swlic_hardware', 'software_license', 'hardware_asset_id', 'hardware_asset', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('fk_swlic_assigned_by', 'software_license', 'assigned_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_swlic_unassigned_by', 'software_license', 'unassigned_by', 'staff', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('software_license');
    }
}