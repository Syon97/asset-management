<?php

namespace app\components;

use Yii;
use app\models\Staff;
use app\models\Department;

class StaffSyncService
{
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
        $db = Yii::$app->get('db_staff_gwidb');
        $rows = $db->createCommand('SELECT * FROM staff_list')->queryAll();

        $created = 0;
        $updated = 0;

        foreach ($rows as $row) {
            $mapped = ['source_sid' => $row['sid'] ?? null];
            foreach ($this->columnMap as $srcCol => $localCol) {
                $mapped[$localCol] = $row[$srcCol] ?? null;
            }

            $staff = Staff::find()->where(['staff_id' => $mapped['staff_id']])->one();

            $isNew = false;
            if (!$staff) {
                $staff = new Staff();
                $isNew = true;
            }

            $staff->setAttributes($mapped, false);
            $staff->department_id = $this->matchDepartment($mapped['department_text']);
            $staff->synced_at = date('Y-m-d H:i:s');
            $staff->save(false);

            $isNew ? $created++ : $updated++;
        }

        return ['created' => $created, 'updated' => $updated, 'total' => count($rows)];
    }

    private function matchDepartment($text)
    {
        if (empty($text)) {
            return null;
        }
        $text = trim($text);

        $dept = Department::find()->andWhere(['name' => $text])->one();
        if (!$dept) {
            $dept = Department::find()
                ->andWhere(new \yii\db\Expression('LOWER(name) = LOWER(:name)', [':name' => $text]))
                ->one();
        }
        return $dept ? $dept->id : null;
    }
}