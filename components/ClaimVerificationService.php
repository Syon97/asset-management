<?php

namespace app\components;

use app\models\Claim;
use app\models\DepartmentVerifier;
use app\models\AppSetting;

class ClaimVerificationService
{
    /**
     * Resolves which staff member must verify this claim, following the
     * agreed logic:
     *   1. Look up the employee's department's configured verifier.
     *   2. If the employee IS that department's verifier (submitting
     *      their own claim), escalate to the General Manager instead.
     *   3. If no verifier is configured for their department at all,
     *      return null - the claim falls to Admin's catch-all queue
     *      rather than being stuck with nobody responsible for it.
     *
     * @return int|null staff_id of the resolved verifier, or null if it
     *                   should fall to the Admin catch-all queue.
     */
    public static function resolveVerifierStaffId(Claim $claim)
    {
        $staff = $claim->staff;
        if (!$staff || !$staff->department_id) {
            return null;
        }

        $mapping = DepartmentVerifier::findOne(['department_id' => $staff->department_id]);
        if (!$mapping) {
            return null;
        }

        if ((int) $mapping->staff_id === (int) $claim->staff_id) {
            $gmStaffId = AppSetting::get(AppSetting::KEY_CLAIM_GM_STAFF_ID);
            return $gmStaffId ? (int) $gmStaffId : null;
        }

        return (int) $mapping->staff_id;
    }

    /**
     * Whether the given staff member is the resolved verifier for this
     * claim - used both to filter the verifier's queue and, critically,
     * to re-check authorization on the actual verify/reject action itself
     * (never trust that "it showed up in their list" alone is enough).
     */
    public static function isVerifierFor(Claim $claim, $staffId)
    {
        return self::resolveVerifierStaffId($claim) === (int) $staffId;
    }

    /**
     * Whether this claim has no resolvable verifier at all, and should
     * therefore appear in Admin's catch-all queue.
     */
    public static function needsAdminFallback(Claim $claim)
    {
        return self::resolveVerifierStaffId($claim) === null;
    }
}