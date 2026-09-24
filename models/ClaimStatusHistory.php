<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class ClaimStatusHistory extends ActiveRecord
{
    public static function tableName()
    {
        return 'claim_status_history';
    }

    public function rules()
    {
        return [
            [['claim_id', 'to_status', 'changed_by'], 'required'],
            [['claim_id', 'changed_by'], 'integer'],
            [['from_status', 'to_status'], 'string', 'max' => 20],
            [['changed_at'], 'safe'],
            [['note'], 'string'],
        ];
    }

    public function getClaim()
    {
        return $this->hasOne(Claim::class, ['id' => 'claim_id']);
    }

    public function getChangedByStaff()
    {
        return $this->hasOne(Staff::class, ['id' => 'changed_by']);
    }

    /**
     * Records one status transition. Used by every phase that moves a
     * claim from one status to another, so the full history - including
     * multiple reject/resubmit cycles - is always captured, not just the
     * most recent transition.
     */
    public static function record($claimId, $fromStatus, $toStatus, $changedBy, $note = null)
    {
        $entry = new self();
        $entry->claim_id = $claimId;
        $entry->from_status = $fromStatus;
        $entry->to_status = $toStatus;
        $entry->changed_by = $changedBy;
        $entry->changed_at = date('Y-m-d H:i:s');
        $entry->note = $note;
        return $entry->save(false);
    }
}