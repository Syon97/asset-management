<?php
use yii\db\Migration;

/**
 * Aligns purchase_requisition with the real GW Manufacturing Purchase Request
 * form (Rev.07 / EX2-GW30):
 *  - header fields: cost_center_id, account_code, customer_code, order_type
 *  - full 5-step approval chain: Requested -> Verified (Dept Manager) ->
 *    Reviewed (Senior Manager) -> Received (Purchasing) -> Approved (GM)
 *
 * Idempotent: safe to re-run if a previous attempt partially applied
 * (MySQL DDL auto-commits per statement, so a failed migration can still
 * leave earlier ALTERs in place).
 */
class m260720_092850_rework_purchase_requisition_table extends Migration
{
    private function hasColumn($table, $column)
    {
        $schema = $this->db->getTableSchema($table, true);
        return $schema !== null && $schema->getColumn($column) !== null;
    }

    private function hasForeignKey($table, $fkName)
    {
        $row = $this->db->createCommand(
            "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = :table
               AND CONSTRAINT_NAME = :fk AND CONSTRAINT_TYPE = 'FOREIGN KEY'",
            [':table' => $table, ':fk' => $fkName]
        )->queryScalar();
        return (bool) $row;
    }

    public function safeUp()
    {
        // --- New header fields ---
        if (!$this->hasColumn('purchase_requisition', 'cost_center_id')) {
            $this->addColumn('purchase_requisition', 'cost_center_id', $this->integer()->null()->after('department_id'));
        }
        if (!$this->hasColumn('purchase_requisition', 'account_code')) {
            $this->addColumn('purchase_requisition', 'account_code', $this->string(50)->null()->after('cost_center_id'));
        }
        if (!$this->hasColumn('purchase_requisition', 'customer_code')) {
            $this->addColumn('purchase_requisition', 'customer_code', $this->string(50)->null()->after('account_code'));
        }
        if (!$this->hasColumn('purchase_requisition', 'order_type')) {
            $this->addColumn('purchase_requisition', 'order_type', "ENUM('new_order','repeat_order') NOT NULL DEFAULT 'new_order' AFTER `customer_code`");
        }

        if (!$this->hasForeignKey('purchase_requisition', 'fk_pr_cost_center')) {
            $this->addForeignKey('fk_pr_cost_center', 'purchase_requisition', 'cost_center_id', 'cost_center', 'id', 'SET NULL', 'CASCADE');
        }

        // --- Approval chain rework ---
        if ($this->hasForeignKey('purchase_requisition', 'fk_pr_checked_by')) {
            $this->dropForeignKey('fk_pr_checked_by', 'purchase_requisition');
        }
        if ($this->hasColumn('purchase_requisition', 'checked_by') && !$this->hasColumn('purchase_requisition', 'verified_by')) {
            $this->renameColumn('purchase_requisition', 'checked_by', 'verified_by');
        }
        if ($this->hasColumn('purchase_requisition', 'checked_at') && !$this->hasColumn('purchase_requisition', 'verified_at')) {
            $this->renameColumn('purchase_requisition', 'checked_at', 'verified_at');
        }
        if (!$this->hasForeignKey('purchase_requisition', 'fk_pr_verified_by')) {
            $this->addForeignKey('fk_pr_verified_by', 'purchase_requisition', 'verified_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        }

        if (!$this->hasColumn('purchase_requisition', 'reviewed_by')) {
            $this->addColumn('purchase_requisition', 'reviewed_by', $this->integer()->null()->after('verified_at'));
        }
        if (!$this->hasColumn('purchase_requisition', 'reviewed_at')) {
            $this->addColumn('purchase_requisition', 'reviewed_at', $this->dateTime()->null()->after('reviewed_by'));
        }
        if (!$this->hasForeignKey('purchase_requisition', 'fk_pr_reviewed_by')) {
            $this->addForeignKey('fk_pr_reviewed_by', 'purchase_requisition', 'reviewed_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        }

        if (!$this->hasColumn('purchase_requisition', 'received_by')) {
            $this->addColumn('purchase_requisition', 'received_by', $this->integer()->null()->after('reviewed_at'));
        }
        if (!$this->hasColumn('purchase_requisition', 'received_at')) {
            $this->addColumn('purchase_requisition', 'received_at', $this->dateTime()->null()->after('received_by'));
        }
        if (!$this->hasForeignKey('purchase_requisition', 'fk_pr_received_by')) {
            $this->addForeignKey('fk_pr_received_by', 'purchase_requisition', 'received_by', 'staff', 'id', 'SET NULL', 'CASCADE');
        }

        if (!$this->hasColumn('purchase_requisition', 'rejected_stage')) {
            $this->addColumn('purchase_requisition', 'rejected_stage', $this->string(20)->null()->after('rejected_by'));
        }

        $this->alterColumn(
            'purchase_requisition',
            'status',
            "ENUM('draft','submitted','verified','reviewed','received','approved','rejected') NOT NULL DEFAULT 'draft'"
        );
    }

    public function safeDown()
    {
        $this->alterColumn(
            'purchase_requisition',
            'status',
            "ENUM('draft','submitted','checked','approved','rejected') NOT NULL DEFAULT 'draft'"
        );

        if ($this->hasColumn('purchase_requisition', 'rejected_stage')) {
            $this->dropColumn('purchase_requisition', 'rejected_stage');
        }

        if ($this->hasForeignKey('purchase_requisition', 'fk_pr_received_by')) {
            $this->dropForeignKey('fk_pr_received_by', 'purchase_requisition');
        }
        if ($this->hasColumn('purchase_requisition', 'received_at')) {
            $this->dropColumn('purchase_requisition', 'received_at');
        }
        if ($this->hasColumn('purchase_requisition', 'received_by')) {
            $this->dropColumn('purchase_requisition', 'received_by');
        }

        if ($this->hasForeignKey('purchase_requisition', 'fk_pr_reviewed_by')) {
            $this->dropForeignKey('fk_pr_reviewed_by', 'purchase_requisition');
        }
        if ($this->hasColumn('purchase_requisition', 'reviewed_at')) {
            $this->dropColumn('purchase_requisition', 'reviewed_at');
        }
        if ($this->hasColumn('purchase_requisition', 'reviewed_by')) {
            $this->dropColumn('purchase_requisition', 'reviewed_by');
        }

        if ($this->hasForeignKey('purchase_requisition', 'fk_pr_verified_by')) {
            $this->dropForeignKey('fk_pr_verified_by', 'purchase_requisition');
        }
        if ($this->hasColumn('purchase_requisition', 'verified_at')) {
            $this->renameColumn('purchase_requisition', 'verified_at', 'checked_at');
        }
        if ($this->hasColumn('purchase_requisition', 'verified_by')) {
            $this->renameColumn('purchase_requisition', 'verified_by', 'checked_by');
        }
        $this->addForeignKey('fk_pr_checked_by', 'purchase_requisition', 'checked_by', 'staff', 'id', 'SET NULL', 'CASCADE');

        if ($this->hasForeignKey('purchase_requisition', 'fk_pr_cost_center')) {
            $this->dropForeignKey('fk_pr_cost_center', 'purchase_requisition');
        }
        if ($this->hasColumn('purchase_requisition', 'order_type')) {
            $this->dropColumn('purchase_requisition', 'order_type');
        }
        if ($this->hasColumn('purchase_requisition', 'customer_code')) {
            $this->dropColumn('purchase_requisition', 'customer_code');
        }
        if ($this->hasColumn('purchase_requisition', 'account_code')) {
            $this->dropColumn('purchase_requisition', 'account_code');
        }
        if ($this->hasColumn('purchase_requisition', 'cost_center_id')) {
            $this->dropColumn('purchase_requisition', 'cost_center_id');
        }
    }
}