<?php
use yii\db\Migration;
use app\models\Staff;
use app\models\UserAccount;

/**
 * Seeds just the two named admins for now, so login can be tested
 * end-to-end. Bulk-creating accounts for every synced staff member is a
 * separate step.
 */
class m260908_015900_seed_admin_accounts extends Migration
{
    private $adminStaffIds = ['M3A029', 'M3A016'];

    public function safeUp()
    {
        foreach ($this->adminStaffIds as $staffCode) {
            $staff = Staff::findOne(['staff_id' => $staffCode]);
            if ($staff === null) {
                echo "  Skipped {$staffCode}: no matching staff record found.\n";
                continue;
            }

            if (UserAccount::findOne(['staff_id' => $staff->id]) !== null) {
                echo "  Skipped {$staffCode}: account already exists.\n";
                continue;
            }

            $account = new UserAccount();
            $account->staff_id = $staff->id;
            $account->username = $staffCode;
            $account->setPassword($staffCode);
            $account->role = UserAccount::ROLE_ADMIN;
            $account->must_change_password = true;
            $account->status = UserAccount::STATUS_ACTIVE;
            $account->save(false);

            echo "  Created admin account for {$staffCode}.\n";
        }
    }

    public function safeDown()
    {
        foreach ($this->adminStaffIds as $staffCode) {
            $this->delete('user_account', ['username' => $staffCode]);
        }
    }
}