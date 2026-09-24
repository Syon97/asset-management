<?php
use yii\db\Migration;

/**
 * Login/role data lives here, deliberately separate from `staff` - that
 * table gets overwritten on every HR sync, so keeping auth data on it
 * directly would risk a resync silently wiping someone's role or password.
 */
class m260908_015639_create_user_account_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('user_account', [
            'id' => $this->primaryKey(),
            'staff_id' => $this->integer()->notNull(),
            'username' => $this->string(50)->notNull()->unique(),
            'password_hash' => $this->string(255)->notNull(),
            'auth_key' => $this->string(32)->notNull(),

            'role' => "ENUM('generic','purchaser','financer','admin') NOT NULL DEFAULT 'generic'",
            'status' => "ENUM('active','inactive') NOT NULL DEFAULT 'active'",
            'must_change_password' => $this->boolean()->notNull()->defaultValue(true),

            'last_login_at' => $this->dateTime()->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_user_account_staff', 'user_account', 'staff_id', 'staff', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('idx_user_account_staff', 'user_account', 'staff_id', true);
    }

    public function safeDown()
    {
        $this->dropTable('user_account');
    }
}