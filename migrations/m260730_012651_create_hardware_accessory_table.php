<?php
use yii\db\Migration;

/**
 * Attach/detach history for accessories on hardware assets. Replaces the
 * legacy pattern of marking rows current=0 then immediately deleting them
 * on every hardware edit, which silently destroyed history if the edit
 * form ever failed to resubmit a checkbox correctly.
 */
class m260730_012651_create_hardware_accessory_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('hardware_accessory', [
            'id' => $this->primaryKey(),
            'hardware_asset_id' => $this->integer()->notNull(),
            'accessory_type_id' => $this->integer()->notNull(),
            'accessory_no' => $this->string(255)->null(),
            'accessory_image' => $this->string(255)->null(),

            'status' => "ENUM('attached','detached') NOT NULL DEFAULT 'attached'",

            'attached_by' => $this->integer()->notNull(),
            'attached_at' => $this->dateTime()->notNull(),
            'detached_by' => $this->integer()->null(),
            'detached_at' => $this->dateTime()->null(),
            'detach_reason' => $this->text()->null(),

            'remarks' => $this->text()->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_hwacc_hardware', 'hardware_accessory', 'hardware_asset_id', 'hardware_asset', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk_hwacc_type', 'hardware_accessory', 'accessory_type_id', 'accessory_type', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_hwacc_attached_by', 'hardware_accessory', 'attached_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_hwacc_detached_by', 'hardware_accessory', 'detached_by', 'staff', 'id', 'SET NULL', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('hardware_accessory');
    }
}