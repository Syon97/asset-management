<?php
use yii\helpers\Html;
use app\models\PurchaseRequisition;

/** @var app\models\PurchaseRequisition $model */
/** @var array $staffList */
$this->title = $model->pr_no;

$stageLabels = [
    PurchaseRequisition::STATUS_SUBMITTED => 'Requested',
    PurchaseRequisition::STATUS_VERIFIED => 'Verified',
    PurchaseRequisition::STATUS_REVIEWED => 'Reviewed',
    PurchaseRequisition::STATUS_RECEIVED => 'Received',
    PurchaseRequisition::STATUS_APPROVED => 'Approved',
];
?>

<h1><?= Html::encode($model->pr_no) ?>
    <span class="badge-status status-<?= $model->status ?>"><?= strtoupper($model->status) ?></span>
    <?= Html::a('<i class="bi bi-printer"></i> Print', ['print', 'id' => $model->id], [
        'class' => 'btn btn-outline-secondary btn-sm', 'target' => '_blank',
    ]) ?>
</h1>

<div class="pr-meta-grid">
    <div class="pr-meta-item">
        <span class="pr-meta-label">Date</span>
        <span class="pr-meta-value"><?= Html::encode($model->pr_date) ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Department</span>
        <span class="pr-meta-value"><?= Html::encode($model->department->name ?? '-') ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Cost Center</span>
        <span class="pr-meta-value"><?= Html::encode($model->costCenter->label ?? '-') ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Order Type</span>
        <span class="pr-meta-value">
            <span class="badge-status <?= $model->order_type === PurchaseRequisition::ORDER_TYPE_NEW ? 'status-approved' : 'status-verified' ?>">
                <?= Html::encode(PurchaseRequisition::optsOrderType()[$model->order_type] ?? '-') ?>
            </span>
        </span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Account Code</span>
        <span class="pr-meta-value"><?= Html::encode($model->account_code) ?: '—' ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Customer Code</span>
        <span class="pr-meta-value"><?= Html::encode($model->customer_code) ?: '—' ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Requested By</span>
        <span class="pr-meta-value"><?= Html::encode($model->requestedByStaff->staff_name ?? '-') ?></span>
    </div>
</div>

<?php if ($model->purpose): ?>
    <div class="pr-notes-box">
        <strong>General Notes</strong>
        <p class="mb-0"><?= nl2br(Html::encode($model->purpose)) ?></p>
    </div>
<?php endif; ?>

<div class="pr-action-card" style="max-width: 100%; margin-bottom: 28px;">
    <div class="pr-action-title">Quotation Attachments <span class="text-muted fw-normal">(record only, not shown on the printed form)</span></div>

    <?php if (empty($model->attachments)): ?>
        <p class="text-muted mb-3">No files attached yet.</p>
    <?php else: ?>
        <table class="table table-bordered table-sm mb-3">
            <thead>
                <tr><th>File</th><th>Uploaded By</th><th>Date</th><th>Remarks</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($model->attachments as $att): ?>
                    <tr>
                        <td><?= Html::a(Html::encode($att->original_name), $att->downloadUrl, ['target' => '_blank']) ?></td>
                        <td><?= Html::encode($att->uploadedByStaff->staff_name ?? '-') ?></td>
                        <td><?= Html::encode($att->created_at) ?></td>
                        <td><?= Html::encode($att->remarks) ?></td>
                        <td>
                            <?= Html::a('Remove', ['/pr-attachment/delete', 'id' => $att->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data' => ['method' => 'post', 'confirm' => 'Remove this attachment?'],
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?= Html::beginForm(['/pr-attachment/upload', 'prId' => $model->id], 'post', ['enctype' => 'multipart/form-data']) ?>
        <input type="hidden" name="uploaded_by" value="<?= Html::encode($model->requested_by) ?>">
        <div class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Files</label>
                <input type="file" name="attachmentFiles[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            </div>
            <div class="col-md-4">
                <label class="form-label">Remarks</label>
                <input type="text" name="remarks" class="form-control" placeholder="Optional">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Upload</button>
            </div>
        </div>
    <?= Html::endForm() ?>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Type</th><th>Description</th><th>Specification</th><th>Qty</th>
            <th>Date Needed</th><th>Unit Price</th><th>Total</th><th>Purpose</th>
            <th>Quotation Ref No.</th><th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($model->items as $item): ?>
            <tr>
                <td><?= Html::encode($item->item_type) ?></td>
                <td><?= Html::encode($item->catalogItem->item_name ?? $item->description) ?></td>
                <td><?= Html::encode($item->specification) ?></td>
                <td><?= $item->quantity ?></td>
                <td><?= Html::encode($item->needed_date) ?></td>
                <td><?= number_format($item->unit_price, 2) ?></td>
                <td><?= number_format($item->total_price, 2) ?></td>
                <td><?= Html::encode($item->purpose) ?></td>
                <td><?= Html::encode($item->quotation_ref_no) ?></td>
                <td><?= Html::encode($item->remarks) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><th colspan="6" class="text-end">Total Amount</th><th colspan="4"><?= number_format($model->totalAmount, 2) ?></th></tr>
    </tfoot>
</table>

<h5 class="mt-4 mb-3">Approval Chain</h5>
<div class="row mb-4">
    <div class="col-md">
        <strong>Requested By</strong><br>
        <?= $model->requestedByStaff->staff_name ?? '-' ?><br>
        <small class="text-muted"><?= $model->pr_date ?></small>
    </div>
    <div class="col-md">
        <strong>Verified By</strong> <span class="text-muted">(Dept Manager)</span><br>
        <?= $model->verifiedByStaff->staff_name ?? '-' ?><br>
        <small class="text-muted"><?= $model->verified_at ?></small>
    </div>
    <div class="col-md">
        <strong>Received By</strong> <span class="text-muted">(Purchasing)</span><br>
        <?= $model->receivedByStaff->staff_name ?? '-' ?><br>
        <small class="text-muted"><?= $model->received_at ?></small>
    </div>
    <div class="col-md">
        <strong>Reviewed By</strong> <span class="text-muted">(Senior Manager)</span><br>
        <?= $model->reviewedByStaff->staff_name ?? '-' ?><br>
        <small class="text-muted"><?= $model->reviewed_at ?></small>
    </div>
    <div class="col-md">
        <strong>Approved By</strong> <span class="text-muted">(General Manager)</span><br>
        <?= $model->approvedByStaff->staff_name ?? '-' ?><br>
        <small class="text-muted"><?= $model->approved_at ?></small>
    </div>
</div>

<?php if ($model->status === PurchaseRequisition::STATUS_REJECTED): ?>
    <div class="alert alert-danger">
        <strong>Rejected</strong> at the <?= $stageLabels[$model->rejected_stage] ?? $model->rejected_stage ?> stage
        by <?= $model->rejectedByStaff->staff_name ?? '-' ?> on <?= $model->rejected_at ?>.<br>
        <?php if ($model->rejection_reason): ?>Reason: <?= Html::encode($model->rejection_reason) ?><?php endif; ?>
    </div>
<?php endif; ?>

<div class="pr-action-row">

<?php if ($model->status === PurchaseRequisition::STATUS_DRAFT): ?>
    <div class="pr-action-card">
        <div class="pr-action-title">This PR is still a draft</div>
        <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-secondary']) ?>
        <?= Html::a('Submit for Verification', ['submit', 'id' => $model->id], [
            'class' => 'btn btn-primary', 'data' => ['method' => 'post', 'confirm' => 'Submit this PR?'],
        ]) ?>
    </div>
<?php endif; ?>

<?php if ($model->status === PurchaseRequisition::STATUS_SUBMITTED): ?>
    <div class="pr-action-card">
        <div class="pr-action-title">Next Step: Verify <span class="text-muted fw-normal">(Dept Manager)</span></div>
        <?= Html::beginForm(['verify', 'id' => $model->id], 'post') ?>
            <input type="hidden" name="verified_by" id="verify-staff-id">
            <div class="input-group pr-action-picker">
                <input type="text" id="verify-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('verify-staff-id','verify-staff-display')">Select</button>
            </div>
            <?= Html::submitButton('Mark as Verified', ['class' => 'btn btn-warning mt-3']) ?>
        <?= Html::endForm() ?>
    </div>
<?php endif; ?>

<?php if ($model->status === PurchaseRequisition::STATUS_VERIFIED): ?>
    <div class="pr-action-card">
        <div class="pr-action-title">Next Step: Receive <span class="text-muted fw-normal">(Purchasing)</span></div>
        <?= Html::beginForm(['receive', 'id' => $model->id], 'post') ?>
            <input type="hidden" name="received_by" id="receive-staff-id">
            <div class="input-group pr-action-picker">
                <input type="text" id="receive-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('receive-staff-id','receive-staff-display')">Select</button>
            </div>
            <?= Html::submitButton('Mark as Received', ['class' => 'btn btn-warning mt-3']) ?>
        <?= Html::endForm() ?>
    </div>
<?php endif; ?>

<?php if ($model->status === PurchaseRequisition::STATUS_RECEIVED): ?>
    <div class="pr-action-card">
        <div class="pr-action-title">Next Step: Review <span class="text-muted fw-normal">(Senior Manager)</span></div>
        <?= Html::beginForm(['review', 'id' => $model->id], 'post') ?>
            <input type="hidden" name="reviewed_by" id="review-staff-id">
            <div class="input-group pr-action-picker">
                <input type="text" id="review-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('review-staff-id','review-staff-display')">Select</button>
            </div>
            <?= Html::submitButton('Mark as Reviewed', ['class' => 'btn btn-warning mt-3']) ?>
        <?= Html::endForm() ?>
    </div>
<?php endif; ?>

<?php if ($model->status === PurchaseRequisition::STATUS_REVIEWED): ?>
    <div class="pr-action-card">
        <div class="pr-action-title">Next Step: Approve <span class="text-muted fw-normal">(General Manager)</span></div>
        <?= Html::beginForm(['approve', 'id' => $model->id], 'post') ?>
            <input type="hidden" name="approved_by" id="approve-staff-id">
            <div class="input-group pr-action-picker">
                <input type="text" id="approve-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('approve-staff-id','approve-staff-display')">Select</button>
            </div>
            <?= Html::submitButton('Approve', ['class' => 'btn btn-success mt-3']) ?>
        <?= Html::endForm() ?>
    </div>
<?php endif; ?>

<?php if ($model->canReject()): ?>
    <div class="pr-action-card pr-action-card-danger">
        <div class="pr-action-title text-danger">Reject This PR</div>
        <?= Html::beginForm(['reject', 'id' => $model->id], 'post') ?>
            <input type="hidden" name="rejected_by" id="reject-staff-id">
            <div class="input-group pr-action-picker">
                <input type="text" id="reject-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('reject-staff-id','reject-staff-display')">Select</button>
            </div>
            <?= Html::textInput('rejection_reason', '', ['class' => 'form-control pr-action-picker mt-2', 'placeholder' => 'Reason for rejection']) ?>
            <?= Html::submitButton('Reject', ['class' => 'btn btn-danger mt-3']) ?>
        <?= Html::endForm() ?>
    </div>
<?php endif; ?>

<?php if ($model->status === PurchaseRequisition::STATUS_APPROVED):
    $existingPo = \app\models\PurchaseOrder::findOne(['pr_id' => $model->id]);
?>
    <div class="pr-action-card">
        <?php if ($existingPo): ?>
            <div class="pr-action-title">Already converted to
                <?= Html::a(Html::encode($existingPo->po_no), ['/purchase-order/view', 'id' => $existingPo->id]) ?>
            </div>
        <?php else: ?>
            <div class="pr-action-title">Ready for Procurement</div>
            <?= Html::a('Convert to Purchase Order', ['/purchase-order/convert', 'prId' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php endif; ?>
    </div>
<?php endif; ?>

</div>