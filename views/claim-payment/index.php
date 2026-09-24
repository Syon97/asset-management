<?php
use yii\helpers\Html;

/** @var app\models\Claim[] $payrollClaims */
/** @var app\models\Claim[] $mileageClaims */

$this->title = 'Mark Claims as Paid';
$this->params['breadcrumbs'][] = $this->title;

function renderClaimPaymentGroup($groupId, $title, $description, $badgeClass, $claims)
{
    $count = count($claims);

    $html = '<div class="pr-action-card" style="max-width: 100%;">';
    $html .= '<div class="d-flex justify-content-between align-items-center mb-1">';
    $html .= '<div class="pr-action-title mb-0">' . Html::encode($title) . '</div>';
    $html .= '<span class="badge-status ' . $badgeClass . '">' . $count . ' WAITING</span>';
    $html .= '</div>';
    $html .= '<p class="text-muted small mb-3">' . Html::encode($description) . '</p>';

    if (empty($claims)) {
        $html .= '<p class="text-muted mb-0">Nothing waiting in this group.</p>';
        $html .= '</div>';
        return $html;
    }

    $html .= Html::beginForm(['mark-paid'], 'post');
    $html .= '<table class="table table-bordered table-sm mb-2">';
    $html .= '<thead><tr>'
        . '<th style="width:30px;"><input type="checkbox" onclick="toggleClaimGroup(this, \'' . $groupId . '\')"></th>'
        . '<th>Claim No.</th><th>Employee</th><th>Category</th><th>Amount (RM)</th><th>Approved</th>'
        . '</tr></thead><tbody>';

    foreach ($claims as $claim) {
        $html .= '<tr>'
            . '<td><input type="checkbox" name="claim_ids[]" value="' . $claim->id . '" class="claim-payment-check claim-group-' . $groupId . '"></td>'
            . '<td>' . Html::a(Html::encode($claim->claim_no), ['/claim/view', 'id' => $claim->id], ['target' => '_blank']) . '</td>'
            . '<td>' . Html::encode($claim->staff->staff_name ?? '-') . '</td>'
            . '<td>' . Html::encode($claim->claimCategory->name ?? '-') . '</td>'
            . '<td>' . number_format($claim->total_amount, 2) . '</td>'
            . '<td>' . Html::encode($claim->approved_at) . '</td>'
            . '</tr>';
    }

    $html .= '</tbody></table>';
    $html .= Html::submitButton('Mark Selected as Paid', ['class' => 'btn btn-primary btn-sm']);
    $html .= Html::endForm();
    $html .= '</div>';

    return $html;
}
?>
<div class="claim-payment-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Approved claims, ready to be marked paid once processed. No live payroll integration - this just records that payment happened.</p>

    <div class="pr-action-row">
        <?= renderClaimPaymentGroup('payroll', 'Payroll Cycle', 'Medical / Welfare — paid together with regular payroll.', 'status-approved', $payrollClaims) ?>
        <?= renderClaimPaymentGroup('mileage', 'Mileage Run', 'Paid on a separate run from payroll.', 'status-verified', $mileageClaims) ?>
    </div>

</div>

<script>
function toggleClaimGroup(checkbox, groupId) {
    document.querySelectorAll('.claim-group-' + groupId).forEach(function (el) {
        el.checked = checkbox.checked;
    });
}
</script>