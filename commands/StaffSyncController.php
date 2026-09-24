<?php

namespace app\commands;

use app\components\StaffSyncService;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Runs the same staff_gwidb sync as the web "Sync Now" button, but from
 * the console - no execution time limit, so this is the right way to run
 * the initial bulk account backfill (hashing a password per new account
 * across potentially thousands of staff takes real time, and a single
 * blocking web request is the wrong place for that).
 *
 * Usage: php yii staff-sync/run
 */
class StaffSyncController extends Controller
{
    public function actionDebug()
    {
        $db = \Yii::$app->get('db_staff_gwidb');
        $row = $db->createCommand('SELECT * FROM staff_list LIMIT 1')->queryOne();

        $this->stdout("Raw first row as PHP actually sees it:\n\n");
        foreach ($row as $key => $value) {
            $this->stdout("  [{$key}] => " . var_export($value, true) . "\n");
        }

        return ExitCode::OK;
    }

    public function actionRun()
    {
        $this->stdout("Starting staff sync (this can take a while on the first run - hashing a password per new account)...\n");

        $start = microtime(true);
        $service = new StaffSyncService();
        $stats = $service->sync();
        $elapsed = round(microtime(true) - $start, 1);

        $this->stdout("\nDone in {$elapsed}s.\n");
        $this->stdout("  Staff synced:          {$stats['total']} total ({$stats['created']} new, {$stats['updated']} updated)\n");
        $this->stdout("  Accounts created:      {$stats['accounts_created']}\n");
        $this->stdout("  Accounts reactivated:  {$stats['accounts_reactivated']}\n");
        $this->stdout("  Accounts deactivated:  {$stats['deactivated_accounts']}\n");

        return ExitCode::OK;
    }
}