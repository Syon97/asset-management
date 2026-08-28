<?php
use yii\db\Migration;

/**
 * Full assignment history for hardware assets, replacing the legacy
 * pattern of a bare nullable "current holder" flag with no audit trail.
 * hardware_asset.current_holder_type/current_holder_id remains as a
 * cached "who has it right now" pointer (same pattern as stock.quantity_on_hand
 * caching stock_ledger) - this table is the actual history behind it.
 */
class m260825_075728_create_asset_assignment_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('asset_assignment', [
            'id' => $this->primaryKey(),
            'hardware_asset_id' => $this->integer()->notNull(),

            'holder_type' => "ENUM('staff','project','department') NOT NULL",
            'holder_id' => $this->integer()->notNull(),

            'status' => "ENUM('active','returned') NOT NULL DEFAULT 'active'",

            'assigned_by' => $this->integer()->notNull(),
            'assigned_at' => $this->dateTime()->notNull(),
            'returned_by' => $this->integer()->null(),
            'returned_at' => $this->dateTime()->null(),

            'remarks' => $this->text()->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_assign_hardware', 'asset_assignment', 'hardware_asset_id', 'hardware_asset', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_assign_assigned_by', 'asset_assignment', 'assigned_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_assign_returned_by', 'asset_assignment', 'returned_by', 'staff', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('asset_assignment');
    }
}