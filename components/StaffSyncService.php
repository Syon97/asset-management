<?php

namespace app\components;

use Yii;
use app\models\Staff;
use app\models\Department;
use app\models\UserAccount;

class StaffSyncService
{
    /**
     * Statuses considered "currently active" for the purposes of being
     * selectable in StaffPicker and keeping a login account active.
     * This does NOT affect who gets synced - every staff row is always
     * mirrored regardless of status, so historical records (old PRs,
     * assignments, etc.) always resolve to a real name. Eligibility is
     * enforced separately, at the point of selection (StaffPicker) and
     * at the point of login (account status).
     */
    const ELIGIBLE_STATUSES = ['PROBATION', 'CONFIRMED', 'CONTRACT', 'TRANSFER'];

    private $columnMap = [
        'staffid'            => 'staff_id',
        'staffname'          => 'staff_name',
        'staffdept'          => 'department_text',
        'staffpos'           => 'position',
        'staff_perm_contrct' => 'employment_type',
        'staffagency'        => 'agency',
        'staffstatus'        => 'status',
        'email'              => 'email',
        'contact_num'        => 'contact_num',
        'section'            => 'section',
        'duty_post'          => 'duty_post',
    ];

    public function sync()
    {
        // Safety nets for a large staff_list table: memory for the debug
        // module's query logging, execution time since bulk-provisioning
        // new accounts means hashing a password per new account, which
        // adds up across a few thousand rows.
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $db = Yii::$app->get('db_staff_gwidb');
        $rows = $db->createCommand('SELECT * FROM staff_list')->queryAll();

        // Preload once instead of querying per row - avoids the N+1
        // pattern that caused memory exhaustion on a full sync.
        $existingStaffById = [];
        foreach (Staff::find()->all() as $s) {
            $existingStaffById[$s->staff_id] = $s;
        }

        $departmentByNormalizedName = [];
        foreach (Department::find()->all() as $d) {
            $departmentByNormalizedName[strtolower(trim($d->name))] = $d->id;
        }

        $activeAccountByStaffId = [];
        $anyAccountByStaffId = [];
        foreach (UserAccount::find()->all() as $a) {
            $anyAccountByStaffId[$a->staff_id] = $a;
            if ($a->status === UserAccount::STATUS_ACTIVE) {
                $activeAccountByStaffId[$a->staff_id] = $a;
            }
        }

        $created = 0;
        $updated = 0;
        $deactivated = 0;
        $accountsCreated = 0;
        $accountsReactivated = 0;

        foreach ($rows as $row) {
            $mapped = ['source_sid' => $row['sid'] ?? null];
            foreach ($this->columnMap as $srcCol => $localCol) {
                $mapped[$localCol] = $row[$srcCol] ?? null;
            }

            $rawStatus = strtoupper(trim((string) ($mapped['status'] ?? '')));
            $isEligible = in_array($rawStatus, self::ELIGIBLE_STATUSES, true);

            $staff = $existingStaffById[$mapped['staff_id']] ?? null;

            $isNew = false;
            if (!$staff) {
                $staff = new Staff();
                $isNew = true;
            }

            $staff->setAttributes($mapped, false);

            $deptText = $mapped['department_text'] ? strtolower(trim($mapped['department_text'])) : null;
            $staff->department_id = $deptText ? ($departmentByNormalizedName[$deptText] ?? null) : null;

            $staff->synced_at = date('Y-m-d H:i:s');
            $staff->save(false);

            $isNew ? $created++ : $updated++;

            if ($isEligible) {
                $account = $anyAccountByStaffId[$staff->id] ?? null;
                if (!$account) {
                    $newAccount = new UserAccount();
                    $newAccount->staff_id = $staff->id;
                    $newAccount->username = $staff->staff_id;
                    $newAccount->setPassword($staff->staff_id);
                    $newAccount->role = UserAccount::ROLE_GENERIC;
                    $newAccount->must_change_password = true;
                    $newAccount->status = UserAccount::STATUS_ACTIVE;
                    if ($newAccount->save()) {
                        $accountsCreated++;
                    }
                } elseif ($account->status === UserAccount::STATUS_INACTIVE) {
                    $account->status = UserAccount::STATUS_ACTIVE;
                    $account->save(false, ['status']);
                    $accountsReactivated++;
                }
            } else {
                $account = $activeAccountByStaffId[$staff->id] ?? null;
                if ($account) {
                    $account->status = UserAccount::STATUS_INACTIVE;
                    $account->save(false, ['status']);
                    $deactivated++;
                }
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'deactivated_accounts' => $deactivated,
            'accounts_created' => $accountsCreated,
            'accounts_reactivated' => $accountsReactivated,
            'total' => count($rows),
        ];
    }
}