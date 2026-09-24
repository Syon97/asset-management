<?php
use yii\helpers\Html;

/** @var app\models\Claim[] $claims */

$this->title = 'Claims Awaiting My Verification';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="claim-verification-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Claims from your department (or escalated to you) waiting for your verification before they go to Finance.</p>

    <?php if (empty($claims)): ?>
        <p class="text-muted">Nothing waiting for your verification.</p>
    <?php endif; ?>

    <?php foreach ($claims as $claim): ?>
        <div class="pr-action-card" style="max-width: 100%; margin-bottom: 20px;">
            <div class="pr-action-title">
                <?= Html::a(Html::encode($claim->claim_no), ['/claim/view', 'id' => $claim->id], ['target' => '_blank']) ?>
                — <?= Html::encode($claim->staff->staff_name ?? '-') ?>
                <span class="text-muted fw-normal">(<?= Html::encode($claim->claimCategory->name ?? '-') ?>, RM <?= number_format($claim->total_amount, 2) ?>)</span>
            </div>

            <table class="table table-bordered table-sm mb-3">
                <thead>
                    <tr><th>Date</th><th>Details</th><th>Amount (RM)</th><th>Receipt</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($claim->items as $item): ?>
                        <tr>
                            <td><?= Html::encode($item->item_date) ?></td>
                            <td>
                                <?php if ($item->isMileageRow()): ?>
                                    <?= Html::encode($item->purpose) ?>
                                    <small class="text-muted">(<?= Html::encode(\app\models\MileageRate::vehicleTypeLabels()[$item->vehicle_type] ?? '') ?>, <?= Html::encode($item->distance_km) ?> km)</small>
                                <?php else: ?>
                                    <?= Html::encode($item->particular) ?>
                                    <?php if ($item->location_company): ?><small class="text-muted"> — <?= Html::encode($item->location_company) ?></small><?php endif; ?>
                                <?php endif; ?>
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
            </table>

            <div class="row g-2">
                <div class="col-md-3">
                    <?= Html::beginForm(['verify', 'id' => $claim->id], 'post') ?>
                        <?= Html::submitButton('Verify', ['class' => 'btn btn-success w-100']) ?>
                    <?= Html::endForm() ?>
                </div>
                <div class="col-md-9">
                    <?= Html::beginForm(['reject', 'id' => $claim->id], 'post', ['class' => 'd-flex gap-2']) ?>
                        <?= Html::textInput('rejection_reason', '', ['class' => 'form-control', 'placeholder' => 'Reason for rejection (required)']) ?>
                        <?= Html::submitButton('Reject', ['class' => 'btn btn-danger']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>