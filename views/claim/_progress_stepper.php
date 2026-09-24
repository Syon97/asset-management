<?php
use yii\helpers\Html;
use app\models\Claim;

/** @var app\models\Claim $claim */

if ($claim->status === Claim::STATUS_DRAFT) {
    echo '<p class="text-muted">This claim hasn\'t been submitted yet - progress will appear here once submitted.</p>';
    return;
}

$steps = [
    ['key' => 'submitted', 'title' => 'Submitted', 'label' => 'By You'],
    ['key' => 'verified', 'title' => 'Verified', 'label' => 'By Manager'],
    ['key' => 'approved', 'title' => 'Approved', 'label' => 'By Finance'],
    ['key' => 'paid', 'title' => 'Paid', 'label' => 'Payroll / Mileage Run'],
];

// Determine each step's state: completed / current / pending / rejected.
$rejectedAtStep = null;
if ($claim->status === Claim::STATUS_REJECTED) {
    $rejectedAtStep = $claim->verified_at ? 2 : 1; // 0-indexed: 1=Verified stage, 2=Approved stage
}

$states = [];
foreach ($steps as $i => $step) {
    if ($rejectedAtStep !== null) {
        if ($i < $rejectedAtStep) {
            $states[$i] = 'completed';
        } elseif ($i === $rejectedAtStep) {
            $states[$i] = 'rejected';
        } else {
            $states[$i] = 'na';
        }
        continue;
    }

    $order = ['submitted' => 0, 'verified' => 1, 'approved' => 2, 'paid' => 3];
    $currentIndex = $order[$claim->status] ?? 0;

    if ($i < $currentIndex) {
        $states[$i] = 'completed';
    } elseif ($i === $currentIndex) {
        $states[$i] = 'current';
    } else {
        $states[$i] = 'pending';
    }
}
?>
<style>
.claim-stepper { display: flex; align-items: flex-start; margin: 24px 0; }
.claim-stepper .step-col { flex: 1; text-align: center; position: relative; }
.claim-stepper .step-row { display: flex; align-items: center; }
.claim-stepper .step-circle {
    width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 18px; font-weight: 600; flex-shrink: 0; margin: 0 auto; border: 2px solid transparent;
}
.claim-stepper .step-connector { flex: 1; height: 3px; margin-top: 22px; }
.claim-stepper .step-completed .step-circle { background: var(--color-success, #2E8B57); color: #fff; }
.claim-stepper .step-current .step-circle { background: #fff; border-color: var(--color-primary, #1F4E5F); color: var(--color-primary, #1F4E5F); box-shadow: 0 0 0 4px rgba(31,78,95,0.12); }
.claim-stepper .step-pending .step-circle, .claim-stepper .step-na .step-circle { background: #E1E4E8; color: #9AA1A8; }
.claim-stepper .step-rejected .step-circle { background: var(--color-danger, #C0392B); color: #fff; }
.claim-stepper .connector-filled { background: var(--color-success, #2E8B57); }
.claim-stepper .connector-empty { background: #E1E4E8; }
.claim-stepper .step-label-num { font-size: 11px; letter-spacing: 0.5px; color: #9AA1A8; text-transform: uppercase; margin-top: 10px; }
.claim-stepper .step-label-title { font-size: 15px; font-weight: 600; color: #1C2732; }
.claim-stepper .step-label-status { font-size: 12px; margin-top: 2px; }
.claim-stepper .status-completed-text { color: var(--color-success, #2E8B57); font-weight: 600; }
.claim-stepper .status-current-text { color: var(--color-primary, #1F4E5F); font-weight: 600; }
.claim-stepper .status-pending-text, .claim-stepper .status-na-text { color: #9AA1A8; }
.claim-stepper .status-rejected-text { color: var(--color-danger, #C0392B); font-weight: 600; }
</style>
<div class="claim-stepper">
    <?php foreach ($steps as $i => $step): $state = $states[$i]; ?>
        <div class="step-col step-<?= $state ?>">
            <div class="step-row">
                <?php if ($i > 0): $prevState = $states[$i - 1]; ?>
                    <div class="step-connector <?= $prevState === 'completed' ? 'connector-filled' : 'connector-empty' ?>"></div>
                <?php endif; ?>
                <div class="step-circle">
                    <?php if ($state === 'completed'): ?>
                        &#10003;
                    <?php elseif ($state === 'rejected'): ?>
                        &#10007;
                    <?php elseif ($state === 'current'): ?>
                        &#9679;
                    <?php else: ?>
                        <?= $i + 1 ?>
                    <?php endif; ?>
                </div>
                <?php if ($i < count($steps) - 1): $thisState = $state; ?>
                    <div class="step-connector <?= $thisState === 'completed' ? 'connector-filled' : 'connector-empty' ?>"></div>
                <?php endif; ?>
            </div>
            <div class="step-label-num">Step <?= $i + 1 ?></div>
            <div class="step-label-title"><?= Html::encode($step['title']) ?></div>
            <div class="step-label-status status-<?= $state ?>-text">
                <?php
                echo match ($state) {
                    'completed' => 'Completed',
                    'current' => 'In Progress',
                    'rejected' => 'Rejected',
                    'na' => 'N/A',
                    default => 'Pending',
                };
                ?>
            </div>
            <div class="text-muted" style="font-size: 11px;"><?= Html::encode($step['label']) ?></div>
        </div>
    <?php endforeach; ?>
</div>