<?php
use yii\helpers\Html;
use app\models\PurchaseOrder;

/** @var app\models\PurchaseOrder $model */

// Once the real ERP PO number is recorded, that's what actually matters to
// the business - lead with it everywhere, keep our internal po_no as a
// secondary reference rather than the headline.
$displayNo = $model->erp_po_no ?: $model->po_no;
$this->title = $displayNo;
?>

<h1><?= Html::encode($displayNo) ?>
    <?php if ($model->erp_po_no): ?>
        <small class="text-muted" style="font-size: 0.9rem; font-weight: 400;">(Internal Ref: <?= Html::encode($model->po_no) ?>)</small>
    <?php endif; ?>
    <span class="badge-status status-<?= $model->status === PurchaseOrder::STATUS_SENT ? 'submitted' : ($model->status === PurchaseOrder::STATUS_CLOSED || $model->status === PurchaseOrder::STATUS_RECEIVED ? 'approved' : ($model->status === PurchaseOrder::STATUS_REJECTED ? 'rejected' : 'draft')) ?>">
        <?= strtoupper(PurchaseOrder::statusLabels()[$model->status] ?? $model->status) ?>
    </span>
</h1>

<div class="pr-meta-grid">
    <div class="pr-meta-item">
        <span class="pr-meta-label">Date</span>
        <span class="pr-meta-value"><?= Html::encode($model->po_date) ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Source PR</span>
        <span class="pr-meta-value">
            <?= Html::a(Html::encode($model->purchaseRequisition->pr_no ?? '-'), ['/purchase-requisition/view', 'id' => $model->pr_id]) ?>
        </span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Supplier</span>
        <span class="pr-meta-value"><?= Html::encode($model->supplier->name ?? '-') ?></span>
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
        <span class="pr-meta-label">Prepared By</span>
        <span class="pr-meta-value"><?= Html::encode($model->preparedByStaff->staff_name ?? '-') ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Payment Terms</span>
        <span class="pr-meta-value"><?= Html::encode($model->payment_terms) ?: '—' ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Internal Ref</span>
        <span class="pr-meta-value"><?= Html::encode($model->po_no) ?></span>
    </div>
</div>

<?php if ($model->delivery_address): ?>
    <div class="pr-notes-box">
        <strong>Delivery Address</strong>
        <p class="mb-0"><?= nl2br(Html::encode($model->delivery_address)) ?></p>
    </div>
<?php endif; ?>

<?php if ($model->remarks): ?>
    <div class="pr-notes-box">
        <strong>Remarks</strong>
        <p class="mb-0"><?= nl2br(Html::encode($model->remarks)) ?></p>
    </div>
<?php endif; ?>

<div class="pr-action-card" style="max-width: 100%; margin-bottom: 20px;">
    <div class="pr-action-title">ERP Purchase Order <span class="text-muted fw-normal">(the real order issued by the company ERP system — this is just a link back to it, not a replacement)</span></div>
    <?= Html::beginForm(['set-erp-no', 'id' => $model->id], 'post', ['class' => 'row g-2 align-items-end mb-4']) ?>
        <div class="col-md-4">
            <label class="form-label">ERP PO No.</label>
            <input type="text" name="erp_po_no" class="form-control" value="<?= Html::encode($model->erp_po_no) ?>" placeholder="e.g. I26-02018">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-secondary w-100">Save</button>
        </div>
    <?= Html::endForm() ?>

    <div class="pr-action-title">Attached Documents <span class="text-muted fw-normal">(e.g. the ERP-generated PO PDF, record only)</span></div>

    <?php if (empty($model->erp_po_no)): ?>
        <div class="alert alert-warning mb-0">
            Record the ERP PO No. above first — attachments are locked until then, since a file needs a real order number to be filed against.
        </div>
    <?php else: ?>
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
                                <?= Html::a('Remove', ['/po-attachment/delete', 'id' => $att->id], [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'data' => ['method' => 'post', 'confirm' => 'Remove this attachment?'],
                                ]) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?= Html::beginForm(['/po-attachment/upload', 'poId' => $model->id], 'post', ['enctype' => 'multipart/form-data']) ?>
            <input type="hidden" name="uploaded_by" value="<?= Html::encode($model->prepared_by) ?>">
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
    <?php endif; ?>
</div>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Description</th><th>Qty</th><th>Received</th><th>Unit Price</th><th>Total</th><th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($model->items as $item): ?>
            <tr>
                <td>
                    <?= Html::encode($item->description) ?>
                    <?php if ($item->specification): ?><br><small class="text-muted"><?= Html::encode($item->specification) ?></small><?php endif; ?>
                </td>
                <td><?= $item->quantity ?></td>
                <td>
                    <?= $item->quantity_received ?> / <?= $item->quantity ?>
                    <?php if ($item->quantity_received >= $item->quantity): ?>
                        <span class="badge-status status-approved">FULL</span>
                    <?php elseif ($item->quantity_received > 0): ?>
                        <span class="badge-status status-verified">PARTIAL</span>
                    <?php endif; ?>
                </td>
                <td><?= number_format($item->unit_price, 2) ?></td>
                <td><?= number_format($item->total_price, 2) ?></td>
                <td><?= Html::encode($item->remarks) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr><th colspan="4" class="text-end">Total Amount</th><th colspan="2"><?= number_format($model->totalAmount, 2) ?></th></tr>
    </tfoot>
</table>

<?php if ($model->status === PurchaseOrder::STATUS_REJECTED): ?>
    <div class="alert alert-danger">
        <strong>Rejected</strong> by <?= Html::encode($model->rejectedByStaff->staff_name ?? '-') ?> on <?= Html::encode($model->rejected_at) ?>.<br>
        <?php if ($model->rejection_reason): ?>Reason: <?= Html::encode($model->rejection_reason) ?><?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($model->status === PurchaseOrder::STATUS_SENT || $model->status === PurchaseOrder::STATUS_CLOSED): ?>
    <p><strong>Approved By:</strong> <?= Html::encode($model->approvedByStaff->staff_name ?? '-') ?> on <?= Html::encode($model->approved_at) ?></p>
<?php endif; ?>

<?php if ($model->status === PurchaseOrder::STATUS_CLOSED): ?>
    <p><strong>Closed By:</strong> <?= Html::encode($model->closedByStaff->staff_name ?? '-') ?> on <?= Html::encode($model->closed_at) ?></p>
<?php endif; ?>

<?php if (in_array($model->status, [PurchaseOrder::STATUS_SENT, PurchaseOrder::STATUS_PARTIALLY_RECEIVED, PurchaseOrder::STATUS_RECEIVED])):
    $grns = \app\models\GoodsReceipt::find()->where(['po_id' => $model->id])->orderBy(['grn_date' => SORT_DESC])->all();
?>
    <div class="pr-action-row">
        <?php if ($model->status !== PurchaseOrder::STATUS_RECEIVED): ?>
            <div class="pr-action-card">
                <div class="pr-action-title">Receive Goods Against This PO</div>
                <?= Html::a('Receive Goods', ['/goods-receipt/create', 'poId' => $model->id], ['class' => 'btn btn-primary']) ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($grns)): ?>
            <div class="pr-action-card">
                <div class="pr-action-title">Goods Receipts for This PO</div>
                <?php foreach ($grns as $grn): ?>
                    <div>
                        <?= Html::a(Html::encode($grn->grn_no), ['/goods-receipt/view', 'id' => $grn->id]) ?>
                        <span class="badge-status status-<?= $grn->status === 'approved' ? 'approved' : ($grn->status === 'rejected' ? 'rejected' : 'draft') ?>">
                            <?= strtoupper($grn->status) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<div class="pr-action-row">

<?php if ($model->status === PurchaseOrder::STATUS_DRAFT): ?>
    <div class="pr-action-card">
        <div class="pr-action-title">Approve & Send <span class="text-muted fw-normal">(sends this PO to the supplier)</span></div>
        <?= Html::beginForm(['approve', 'id' => $model->id], 'post') ?>
            <input type="hidden" name="approved_by" id="approve-po-staff-id">
            <div class="input-group pr-action-picker">
                <input type="text" id="approve-po-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('approve-po-staff-id','approve-po-staff-display')">Select</button>
            </div>
            <?= Html::submitButton('Approve & Send to Supplier', ['class' => 'btn btn-success mt-3']) ?>
        <?= Html::endForm() ?>
    </div>

    <div class="pr-action-card pr-action-card-danger">
        <div class="pr-action-title text-danger">Reject This PO</div>
        <?= Html::beginForm(['reject', 'id' => $model->id], 'post') ?>
            <input type="hidden" name="rejected_by" id="reject-po-staff-id">
            <div class="input-group pr-action-picker">
                <input type="text" id="reject-po-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('reject-po-staff-id','reject-po-staff-display')">Select</button>
            </div>
            <?= Html::textInput('rejection_reason', '', ['class' => 'form-control pr-action-picker mt-2', 'placeholder' => 'Reason for rejection']) ?>
            <?= Html::submitButton('Reject', ['class' => 'btn btn-danger mt-3']) ?>
        <?= Html::endForm() ?>
    </div>

    <div class="pr-action-card">
        <div class="pr-action-title">Discard</div>
        <?= Html::a('Delete Draft', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-outline-danger', 'data' => ['method' => 'post', 'confirm' => 'Delete this draft PO?'],
        ]) ?>
    </div>
<?php endif; ?>

<?php if ($model->canClose()): ?>
    <div class="pr-action-card">
        <div class="pr-action-title">Close This PO</div>
        <?= Html::beginForm(['close', 'id' => $model->id], 'post') ?>
            <input type="hidden" name="closed_by" id="close-po-staff-id">
            <div class="input-group pr-action-picker">
                <input type="text" id="close-po-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('close-po-staff-id','close-po-staff-display')">Select</button>
            </div>
            <?= Html::submitButton('Close PO', ['class' => 'btn btn-secondary mt-3']) ?>
        <?= Html::endForm() ?>
    </div>
<?php endif; ?>

</div>