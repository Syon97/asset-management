<?php
use yii\helpers\Html;
use app\models\GoodsReceipt;

/** @var app\models\GoodsReceipt $model */
$this->title = $model->grn_no;
?>

<h1><?= Html::encode($model->grn_no) ?>
    <span class="badge-status status-<?= $model->status === GoodsReceipt::STATUS_APPROVED ? 'approved' : ($model->status === GoodsReceipt::STATUS_REJECTED ? 'rejected' : 'draft') ?>">
        <?= strtoupper($model->status) ?>
    </span>
    <?php if ($model->isDirect()): ?>
        <span class="badge-status status-verified">ADHOC / NO PO</span>
    <?php endif; ?>
</h1>

<div class="pr-meta-grid">
    <div class="pr-meta-item">
        <span class="pr-meta-label">Date</span>
        <span class="pr-meta-value"><?= Html::encode($model->grn_date) ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label"><?= $model->isDirect() ? 'Source' : 'Source PO' ?></span>
        <span class="pr-meta-value">
            <?php if ($model->isDirect()): ?>
                Adhoc Purchase
            <?php else: ?>
                <?= Html::a(Html::encode($model->purchaseOrder->po_no ?? '-'), ['/purchase-order/view', 'id' => $model->po_id]) ?>
            <?php endif; ?>
        </span>
    </div>
    <?php if ($model->isDirect()): ?>
        <div class="pr-meta-item">
            <span class="pr-meta-label">Supplier</span>
            <span class="pr-meta-value"><?= Html::encode($model->supplier->name ?? '-') ?></span>
        </div>
        <div class="pr-meta-item">
            <span class="pr-meta-label">External Ref. No.</span>
            <span class="pr-meta-value"><?= Html::encode($model->external_ref_no) ?: '—' ?></span>
        </div>
    <?php endif; ?>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Department</span>
        <span class="pr-meta-value"><?= Html::encode($model->department->name ?? '-') ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Received By</span>
        <span class="pr-meta-value"><?= Html::encode($model->receivedByStaff->staff_name ?? '-') ?></span>
    </div>
</div>

<?php if ($model->remarks): ?>
    <div class="pr-notes-box">
        <strong>Remarks</strong>
        <p class="mb-0"><?= nl2br(Html::encode($model->remarks)) ?></p>
    </div>
<?php endif; ?>

<div class="pr-action-card" style="max-width: 100%; margin-bottom: 20px;">
    <?php if ($model->isDirect()): ?>
        <div class="pr-action-title">External Order / Invoice No.</div>
        <?= Html::beginForm(['set-external-ref', 'id' => $model->id], 'post', ['class' => 'row g-2 align-items-end mb-4']) ?>
            <div class="col-md-4">
                <input type="text" name="external_ref_no" id="grn-external-ref-input" class="form-control" value="<?= Html::encode($model->external_ref_no) ?>" placeholder="e.g. Shopee order ID or invoice no.">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Save</button>
            </div>
        <?= Html::endForm() ?>
    <?php endif; ?>

    <div class="pr-action-title">Invoice / Receipt Attachments <span class="text-muted fw-normal">(record only)</span></div>

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
                            <?php if (str_ends_with(strtolower($att->stored_name), '.pdf')): ?>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="scanGrnAttachment(<?= $att->id ?>, this)">Scan for Info</button>
                            <?php endif; ?>
                            <?= Html::a('Remove', ['/grn-attachment/delete', 'id' => $att->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data' => ['method' => 'post', 'confirm' => 'Remove this attachment?'],
                            ]) ?>
                            <div id="scan-result-<?= $att->id ?>" class="small mt-1"></div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <?= Html::beginForm(['/grn-attachment/upload', 'grnId' => $model->id], 'post', ['enctype' => 'multipart/form-data']) ?>
        <input type="hidden" name="uploaded_by" value="<?= Html::encode($model->received_by) ?>">
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
            <th>Item</th><th>Qty Received (this delivery)</th>
            <?php if ($model->isDirect()): ?><th>Unit Price</th><th>Total</th><?php endif; ?>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($model->items as $item): ?>
            <tr>
                <td><?= Html::encode($item->catalogItem->item_name ?? $item->displayDescription ?? '-') ?></td>
                <td><?= $item->quantity_received ?></td>
                <?php if ($model->isDirect()): ?>
                    <td><?= $item->unit_price !== null ? number_format($item->unit_price, 2) : '—' ?></td>
                    <td><?= $item->total_price !== null ? number_format($item->total_price, 2) : '—' ?></td>
                <?php endif; ?>
                <td><?= Html::encode($item->remarks) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php if ($model->status === GoodsReceipt::STATUS_REJECTED): ?>
    <div class="alert alert-danger">
        <strong>Rejected</strong> by <?= Html::encode($model->rejectedByStaff->staff_name ?? '-') ?> on <?= Html::encode($model->rejected_at) ?>.<br>
        <?php if ($model->rejection_reason): ?>Reason: <?= Html::encode($model->rejection_reason) ?><?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($model->status === GoodsReceipt::STATUS_APPROVED): ?>
    <p><strong>Approved By:</strong> <?= Html::encode($model->approvedByStaff->staff_name ?? '-') ?> on <?= Html::encode($model->approved_at) ?></p>
<?php endif; ?>

<?php if ($model->status === GoodsReceipt::STATUS_DRAFT): ?>
    <div class="pr-action-row">
        <div class="pr-action-card">
            <div class="pr-action-title">Approve <span class="text-muted fw-normal">(this updates stock)</span></div>
            <?= Html::beginForm(['approve', 'id' => $model->id], 'post') ?>
                <input type="hidden" name="approved_by" id="approve-grn-staff-id">
                <div class="input-group pr-action-picker">
                    <input type="text" id="approve-grn-staff-display" class="form-control" readonly placeholder="Click Select...">
                    <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('approve-grn-staff-id','approve-grn-staff-display')">Select</button>
                </div>
                <?= Html::submitButton('Approve GRN', ['class' => 'btn btn-success mt-3']) ?>
            <?= Html::endForm() ?>
        </div>

        <div class="pr-action-card pr-action-card-danger">
            <div class="pr-action-title text-danger">Reject This GRN</div>
            <?= Html::beginForm(['reject', 'id' => $model->id], 'post') ?>
                <input type="hidden" name="rejected_by" id="reject-grn-staff-id">
                <div class="input-group pr-action-picker">
                    <input type="text" id="reject-grn-staff-display" class="form-control" readonly placeholder="Click Select...">
                    <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('reject-grn-staff-id','reject-grn-staff-display')">Select</button>
                </div>
                <?= Html::textInput('rejection_reason', '', ['class' => 'form-control pr-action-picker mt-2', 'placeholder' => 'Reason for rejection']) ?>
                <?= Html::submitButton('Reject', ['class' => 'btn btn-danger mt-3']) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
<?php endif; ?>

<script>
function scanGrnAttachment(attachmentId, buttonEl) {
    const resultEl = document.getElementById('scan-result-' + attachmentId);
    buttonEl.disabled = true;
    buttonEl.textContent = 'Scanning...';
    resultEl.innerHTML = '';

    fetch('<?= \yii\helpers\Url::to(['/grn-attachment/extract']) ?>?id=' + attachmentId)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            buttonEl.disabled = false;
            buttonEl.textContent = 'Scan for Info';

            if (!data.ok) {
                resultEl.innerHTML = '<span class="text-muted">' + data.message + '</span>';
                return;
            }
            if (!data.found_anything) {
                resultEl.innerHTML = '<span class="text-muted">Read the PDF but didn\'t recognize an order no. or total in it.</span>';
                return;
            }

            let html = '<div class="border rounded p-2 bg-light">';
            if (data.order_no) {
                html += 'Detected order/invoice no.: <strong>' + data.order_no + '</strong> ';
                html += '<button type="button" class="btn btn-xs btn-outline-primary" style="padding:1px 6px;font-size:11px;" onclick="useDetectedRef(\'' + data.order_no.replace(/'/g, "\\'") + '\')">Use this</button><br>';
            }
            if (data.total) {
                html += 'Detected total: <strong>RM ' + data.total + '</strong> — compare against what you entered for this delivery.';
            }
            html += '</div>';
            resultEl.innerHTML = html;
        })
        .catch(function () {
            buttonEl.disabled = false;
            buttonEl.textContent = 'Scan for Info';
            resultEl.innerHTML = '<span class="text-danger">Scan failed.</span>';
        });
}

function useDetectedRef(value) {
    const input = document.getElementById('grn-external-ref-input');
    if (input) {
        input.value = value;
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>