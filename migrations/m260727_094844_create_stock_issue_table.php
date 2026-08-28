<?php
use yii\db\Migration;

/**
 * Issuing stock out to a Staff, Project, or Department. holder_type +
 * holder_id is a polymorphic reference (no FK possible since it can point
 * to different tables depending on holder_type).
 */
class m260727_094844_create_stock_issue_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('stock_issue', [
            'id' => $this->primaryKey(),
            'catalog_item_id' => $this->integer()->notNull(),
            'department_id' => $this->integer()->notNull(),
            'quantity' => $this->integer()->notNull(),

            'holder_type' => "ENUM('staff','project','department') NOT NULL",
            'holder_id' => $this->integer()->notNull(),

            'issued_by' => $this->integer()->notNull(),
            'issue_date' => $this->date()->notNull(),
            'remarks' => $this->string(255)->null(),

            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->addForeignKey('fk_issue_catalog_item', 'stock_issue', 'catalog_item_id', 'item_catalog', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_issue_department', 'stock_issue', 'department_id', 'department', 'id', 'RESTRICT', 'CASCADE');
        $this->addForeignKey('fk_issue_issued_by', 'stock_issue', 'issued_by', 'staff', 'id', 'RESTRICT', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('stock_issue');
    }
}