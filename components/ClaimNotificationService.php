<?php

namespace app\components;

use Yii;
use app\models\Claim;
use app\models\UserAccount;

/**
 * Sends the claim workflow notification emails:
 *   Submit    -> resolved verifier
 *   Verify    -> all Financer/Admin users
 *   Approve   -> the claiming employee
 *   Reject    -> the claiming employee (at either the verify or approve stage)
 *   Paid      -> the claiming employee
 *
 * Each method is deliberately tolerant of a missing/blank email address -
 * it skips silently (logged, not thrown) rather than breaking the actual
 * workflow action just because a notification couldn't go out.
 */
class ClaimNotificationService
{
    public static function notifySubmitted(Claim $claim, $verifierStaffId)
    {
        if (!$verifierStaffId) {
            return;
        }
        $verifier = \app\models\Staff::findOne($verifierStaffId);
        self::send(
            $verifier,
            "Claim {$claim->claim_no} awaiting your verification",
            "{$claim->staff->staff_name} submitted a claim ({$claim->claimCategory->name}, RM " . number_format($claim->total_amount, 2) . ") that needs your verification.\n\n"
            . Yii::$app->urlManager->createAbsoluteUrl(['/claim-verification/index'])
        );
    }

    public static function notifyVerified(Claim $claim)
    {
        $financeUsers = UserAccount::find()
            ->where(['role' => [UserAccount::ROLE_FINANCER, UserAccount::ROLE_ADMIN], 'status' => UserAccount::STATUS_ACTIVE])
            ->with('staff')
            ->all();

        foreach ($financeUsers as $account) {
            if (!$account->staff) {
                continue;
            }
            self::send(
                $account->staff,
                "Claim {$claim->claim_no} verified — awaiting approval",
                "{$claim->staff->staff_name}'s claim ({$claim->claimCategory->name}, RM " . number_format($claim->total_amount, 2) . ") has been verified and is ready for your review.\n\n"
                . Yii::$app->urlManager->createAbsoluteUrl(['/claim-approval/index'])
            );
        }
    }

    public static function notifyApproved(Claim $claim)
    {
        self::send(
            $claim->staff,
            "Claim {$claim->claim_no} approved",
            "Your claim ({$claim->claimCategory->name}, RM " . number_format($claim->total_amount, 2) . ") has been approved.\n\n"
            . Yii::$app->urlManager->createAbsoluteUrl(['/claim/view', 'id' => $claim->id])
        );
    }

    public static function notifyRejected(Claim $claim)
    {
        self::send(
            $claim->staff,
            "Claim {$claim->claim_no} rejected",
            "Your claim ({$claim->claimCategory->name}, RM " . number_format($claim->total_amount, 2) . ") was rejected.\n\nReason: {$claim->rejection_reason}\n\n"
            . "You can edit and resubmit it here: " . Yii::$app->urlManager->createAbsoluteUrl(['/claim/view', 'id' => $claim->id])
        );
    }

    public static function notifyPaid(Claim $claim)
    {
        self::send(
            $claim->staff,
            "Claim {$claim->claim_no} paid",
            "Your claim ({$claim->claimCategory->name}, RM " . number_format($claim->total_amount, 2) . ") has been marked as paid.\n\n"
            . Yii::$app->urlManager->createAbsoluteUrl(['/claim/view', 'id' => $claim->id])
        );
    }

    private static function send($staff, $subject, $body)
    {
        if (!$staff || empty($staff->email)) {
            Yii::warning("Claim notification skipped - no email on file for staff_id " . ($staff->id ?? 'unknown'), 'claim-notify');
            return;
        }

        try {
            Yii::$app->mailer->compose()
                ->setTo($staff->email)
                ->setFrom([Yii::$app->params['claimNotifyFromEmail'] ?? 'no-reply@example.com' => 'AssetTrack Claims'])
                ->setSubject($subject)
                ->setTextBody($body)
                ->send();
        } catch (\Exception $e) {
            // A notification failing to send should never break the
            // actual workflow action it's attached to.
            Yii::error("Claim notification failed: " . $e->getMessage(), 'claim-notify');
        }
    }
}