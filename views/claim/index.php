<?php
use yii\helpers\Html;
use app\models\Claim;

/** @var app\models\Claim[] $claims */

$this->title = 'My Claims';
$this->params['breadcrumbs'][] = $this->title;

$statusBadge = [
    Claim::STATUS_DRAFT => 'status-draft',
    Claim::STATUS_SUBMITTED => 'status-verified',
    Claim::STATUS_VERIFIED => 'status-verified',
    Claim::STATUS_APPROVED => 'status-approved',
    Claim::STATUS_REJECTED => 'status-rejected',
    Claim::STATUS_PAID => 'status-approved',
];
?>
<div class="claim-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p><?= Html::a('New Claim', ['create'], ['class' => 'btn btn-primary']) ?></p>

    <table class="table table-bordered">
        <thead>
            <tr><th>Claim No.</th><th>Category</th><th>Total (RM)</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            <?php if (empty($claims)): ?>
                <tr><td colspan="5" class="text-center text-muted">No claims yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($claims as $claim): ?>
                <tr>
                    <td><?= Html::a(Html::encode($claim->claim_no), ['view', 'id' => $claim->id]) ?></td>
                    <td><?= Html::encode($claim->claimCategory->name ?? '-') ?></td>
                    <td><?= number_format($claim->total_amount, 2) ?></td>
                    <td><span class="badge-status <?= $statusBadge[$claim->status] ?? 'status-draft' ?>"><?= strtoupper($claim->status) ?></span></td>
                    <td><?= Html::a('View', ['view', 'id' => $claim->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>