<?php
use yii\helpers\Html;
use app\models\Claim;

/** @var app\models\Claim $model */
/** @var bool $isOwn */

$this->title = $model->claim_no;

$statusBadge = [
    Claim::STATUS_DRAFT => 'status-draft',
    Claim::STATUS_SUBMITTED => 'status-submitted',
    Claim::STATUS_VERIFIED => 'status-verified',
    Claim::STATUS_APPROVED => 'status-approved',
    Claim::STATUS_REJECTED => 'status-rejected',
    Claim::STATUS_PAID => 'status-approved',
][$model->status] ?? 'status-draft';
?>

<h1><?= Html::encode($model->claim_no) ?>
    <span class="badge-status <?= $statusBadge ?>"><?= strtoupper($model->status) ?></span>
</h1>

<?= $this->render('_progress_stepper', ['claim' => $model]) ?>

<div class="pr-meta-grid">
    <div class="pr-meta-item">
        <span class="pr-meta-label">Employee</span>
        <span class="pr-meta-value"><?= Html::encode($model->staff->staff_name ?? '-') ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Category</span>
        <span class="pr-meta-value"><?= Html::encode($model->claimCategory->name ?? '-') ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Total Amount</span>
        <span class="pr-meta-value">RM <?= number_format($model->total_amount, 2) ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Submitted</span>
        <span class="pr-meta-value"><?= Html::encode($model->submitted_at) ?: '—' ?></span>
    </div>
</div>

<?php if ($model->remarks): ?>
    <div class="pr-notes-box">
        <strong>Notes</strong>
        <p class="mb-0"><?= nl2br(Html::encode($model->remarks)) ?></p>
    </div>
<?php endif; ?>

<?php if ($model->status === Claim::STATUS_REJECTED && $model->rejection_reason): ?>
    <div class="alert alert-danger">
        <strong>Rejected</strong> by <?= Html::encode($model->rejectedByStaff->staff_name ?? '-') ?> on <?= Html::encode($model->rejected_at) ?>.<br>
        Reason: <?= Html::encode($model->rejection_reason) ?>
    </div>
<?php endif; ?>

<table class="table table-bordered">
    <thead>
        <tr><th>Date</th><th>Details</th><th>Amount (RM)</th><th>Receipt</th></tr>
    </thead>
    <tbody>
        <?php foreach ($model->items as $item): ?>
            <tr>
                <td><?= Html::encode($item->item_date) ?></td>
                <td>
                    <?php if ($item->isMileageRow()): ?>
                        <?= Html::encode($item->purpose) ?>
                        <br><small class="text-muted"><?= Html::encode(\app\models\MileageRate::vehicleTypeLabels()[$item->vehicle_type] ?? '') ?> — <?= Html::encode($item->distance_km) ?> km</small>
                    <?php else: ?>
                        <?= Html::encode($item->particular) ?>
                        <?php if ($item->location_company): ?><br><small class="text-muted"><?= Html::encode($item->location_company) ?></small><?php endif; ?>
                        <?php if ($item->purpose): ?><br><small class="text-muted"><?= Html::encode($item->purpose) ?></small><?php endif; ?>
                    <?php endif; ?>
                    <?php if ($item->remarks): ?><br><small class="text-muted">Remarks: <?= Html::encode($item->remarks) ?></small><?php endif; ?>
                </td>
                <td><?= number_format($item->amount, 2) ?></td>
                <td>
                    <?php if ($item->getReceiptUrl()): ?>
                        <?= Html::a('View', $item->getReceiptUrl(), ['target' => '_blank']) ?>
                    <?php elseif (!$item->isMileageRow()): ?>
                        <span class="badge-status status-rejected">MISSING</span>
                    <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><th colspan="2" class="text-end">Total</th><th colspan="2">RM <?= number_format($model->total_amount, 2) ?></th></tr>
    </tfoot>
</table>

<h5 class="mt-4 mb-2">History</h5>
<ul class="list-unstyled">
    <?php foreach ($model->statusHistory as $entry): ?>
        <li class="mb-1">
            <small class="text-muted"><?= Html::encode($entry->changed_at) ?></small>
            — <?= Html::encode($entry->changedByStaff->staff_name ?? '-') ?>:
            <?= $entry->from_status ? strtoupper($entry->from_status) . ' → ' : '' ?><strong><?= strtoupper($entry->to_status) ?></strong>
            <?php if ($entry->note): ?><br><small class="text-muted"><?= Html::encode($entry->note) ?></small><?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>

<?php if ($isOwn && $model->canEdit()): ?>
    <div class="pr-action-row">
        <div class="pr-action-card">
            <div class="pr-action-title">Edit This Claim</div>
            <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
            <?= Html::a('Submit for Approval', ['submit', 'id' => $model->id], [
                'class' => 'btn btn-primary',
                'data' => ['method' => 'post', 'confirm' => 'Submit this claim? You won\'t be able to edit it unless it\'s rejected.'],
            ]) ?>
        </div>
        <?php if ($model->status === Claim::STATUS_DRAFT): ?>
            <div class="pr-action-card pr-action-card-danger">
                <div class="pr-action-title text-danger">Discard</div>
                <?= Html::a('Delete Draft', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger',
                    'data' => ['method' => 'post', 'confirm' => 'Delete this draft claim?'],
                ]) ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>