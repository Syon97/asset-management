<?php
use yii\helpers\Html;
use app\models\HardwareAsset;

/** @var app\models\HardwareAsset $model */
$this->title = $model->asset_tag;

$warrantyStatus = $model->warrantyStatus;
$warrantyBadge = [
    'none' => ['label' => 'NO WARRANTY DATA', 'class' => 'status-draft'],
    'active' => ['label' => 'UNDER WARRANTY', 'class' => 'status-approved'],
    'expired' => ['label' => 'WARRANTY EXPIRED', 'class' => 'status-rejected'],
][$warrantyStatus];
?>

<h1><?= Html::encode($model->asset_tag) ?>
    <span class="badge-status <?= $model->status === HardwareAsset::STATUS_ACTIVE ? 'status-approved' : 'status-rejected' ?>">
        <?= strtoupper($model->status) ?>
    </span>
    <?= Html::a('<i class="bi bi-printer"></i> Print QR Label', ['print-label', 'id' => $model->id], [
        'class' => 'btn btn-outline-secondary btn-sm', 'target' => '_blank',
    ]) ?>
</h1>

<div class="row">
    <div class="col-md-8">
        <div class="pr-meta-grid">
            <div class="pr-meta-item">
                <span class="pr-meta-label">Category</span>
                <span class="pr-meta-value"><?= Html::encode($model->category->category_name ?? '-') ?></span>
            </div>
            <div class="pr-meta-item">
                <span class="pr-meta-label">Location</span>
                <span class="pr-meta-value"><?= Html::encode($model->department->name ?? '-') ?></span>
            </div>
            <div class="pr-meta-item">
                <span class="pr-meta-label">Brand / Model</span>
                <span class="pr-meta-value"><?= Html::encode($model->brand) ?> <?= Html::encode($model->model) ?></span>
            </div>
            <div class="pr-meta-item">
                <span class="pr-meta-label">Serial No.</span>
                <span class="pr-meta-value"><?= Html::encode($model->serial_no) ?: '—' ?></span>
            </div>
            <div class="pr-meta-item">
                <span class="pr-meta-label">Supplier</span>
                <span class="pr-meta-value"><?= Html::encode($model->supplier->name ?? '-') ?></span>
            </div>
            <div class="pr-meta-item">
                <span class="pr-meta-label">Purchase Price</span>
                <span class="pr-meta-value"><?= $model->purchase_price !== null ? number_format($model->purchase_price, 2) : '—' ?></span>
            </div>
            <div class="pr-meta-item">
                <span class="pr-meta-label">Purchase Date</span>
                <span class="pr-meta-value"><?= Html::encode($model->purchase_date) ?: '—' ?></span>
            </div>
            <div class="pr-meta-item">
                <span class="pr-meta-label">Warranty</span>
                <span class="pr-meta-value">
                    <?php if ($model->warranty_start_date || $model->warranty_end_date): ?>
                        <?= Html::encode($model->warranty_start_date) ?> &rarr; <?= Html::encode($model->warranty_end_date) ?>
                    <?php else: ?>—<?php endif; ?>
                    <span class="badge-status <?= $warrantyBadge['class'] ?>"><?= $warrantyBadge['label'] ?></span>
                </span>
            </div>
            <div class="pr-meta-item">
                <span class="pr-meta-label">Current Holder</span>
                <span class="pr-meta-value">
                    <?php if ($model->getHolderLabel()): ?>
                        <?= Html::encode(HardwareAsset::holderTypeLabels()[$model->current_holder_type] ?? '') ?>: <?= Html::encode($model->getHolderLabel()) ?>
                    <?php else: ?>Unassigned<?php endif; ?>
                </span>
            </div>
        </div>

        <?php if ($model->remarks): ?>
            <div class="pr-notes-box">
                <strong>Remarks</strong>
                <p class="mb-0"><?= nl2br(Html::encode($model->remarks)) ?></p>
            </div>
        <?php endif; ?>

        <?php if ($model->status === HardwareAsset::STATUS_DISPOSED): ?>
            <div class="alert alert-secondary">
                <strong>Disposed</strong> on <?= Html::encode($model->disposed_at) ?>.
                <?php if ($model->disposal_remarks): ?><br>Reason: <?= Html::encode($model->disposal_remarks) ?><?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row mt-3">
            <?php foreach (['product_image' => 'Product Photo', 'serial_image' => 'Serial No. Photo', 'serialcommand_image' => 'Serial Command Photo'] as $col => $label): ?>
                <?php if ($model->getImageUrl($col)): ?>
                    <div class="col-md-4 mb-3">
                        <div class="pr-meta-label mb-1"><?= $label ?></div>
                        <img src="<?= Html::encode($model->getImageUrl($col)) ?>" class="img-fluid rounded border">
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <hr>
        <h5 class="mb-3">Accessories</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr><th>Type</th><th>No. / Serial</th><th>Status</th><th>Attached</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($model->accessories)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No accessories attached.</td></tr>
                <?php endif; ?>
                <?php foreach ($model->accessories as $acc): ?>
                    <tr>
                        <td><?= Html::encode($acc->accessoryType->name ?? '-') ?></td>
                        <td><?= Html::encode($acc->accessory_no) ?: '—' ?></td>
                        <td>
                            <span class="badge-status <?= $acc->status === 'attached' ? 'status-approved' : 'status-draft' ?>">
                                <?= strtoupper($acc->status) ?>
                            </span>
                        </td>
                        <td><?= Html::encode($acc->attachedByStaff->staff_name ?? '-') ?> on <?= Html::encode($acc->attached_at) ?></td>
                        <td>
                            <?php if ($acc->canDetach()): ?>
                                <?= Html::beginForm(['/hardware-accessory/detach', 'id' => $acc->id], 'post', ['class' => 'd-inline-flex gap-1']) ?>
                                    <input type="hidden" name="detached_by" id="detach-staff-id-<?= $acc->id ?>">
                                    <input type="text" id="detach-staff-display-<?= $acc->id ?>" class="form-control form-control-sm" readonly placeholder="Click Select..." style="width:140px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="StaffPicker.open('detach-staff-id-<?= $acc->id ?>','detach-staff-display-<?= $acc->id ?>')">Select</button>
                                    <button type="submit" class="btn btn-sm btn-danger">Detach</button>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?= Html::a('Attach Accessory', ['/hardware-accessory/attach', 'hardwareAssetId' => $model->id], ['class' => 'btn btn-sm btn-outline-primary mb-4']) ?>

        <h5 class="mb-3">Software Licenses</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr><th>Name</th><th>Version</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
                <?php if (empty($model->softwareLicenses)): ?>
                    <tr><td colspan="4" class="text-center text-muted">No software licenses assigned.</td></tr>
                <?php endif; ?>
                <?php foreach ($model->softwareLicenses as $lic): ?>
                    <tr>
                        <td><?= Html::a(Html::encode($lic->software_name), ['/software-license/view', 'id' => $lic->id]) ?></td>
                        <td><?= Html::encode($lic->software_version) ?: '—' ?></td>
                        <td>
                            <span class="badge-status <?= $lic->status === 'assigned' ? 'status-approved' : 'status-draft' ?>">
                                <?= strtoupper($lic->status) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($lic->canUnassign()): ?>
                                <?= Html::beginForm(['/software-license/unassign', 'id' => $lic->id], 'post', ['class' => 'd-inline-flex gap-1']) ?>
                                    <input type="hidden" name="unassigned_by" id="unassign-staff-id-<?= $lic->id ?>">
                                    <input type="text" id="unassign-staff-display-<?= $lic->id ?>" class="form-control form-control-sm" readonly placeholder="Click Select..." style="width:140px;">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="StaffPicker.open('unassign-staff-id-<?= $lic->id ?>','unassign-staff-display-<?= $lic->id ?>')">Select</button>
                                    <button type="submit" class="btn btn-sm btn-danger">Unassign</button>
                                <?= Html::endForm() ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?= Html::a('Add Software License', ['/software-license/create', 'hardwareAssetId' => $model->id], ['class' => 'btn btn-sm btn-outline-primary mb-4']) ?>

        <hr>
        <h5 class="mb-3">Assignment History</h5>
        <table class="table table-bordered table-sm">
            <thead>
                <tr><th>Holder</th><th>Status</th><th>Assigned</th><th>Returned</th><th>Remarks</th></tr>
            </thead>
            <tbody>
                <?php if (empty($model->assignments)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Never assigned to anyone.</td></tr>
                <?php endif; ?>
                <?php foreach ($model->assignments as $assignment): ?>
                    <tr>
                        <td><?= Html::encode(\app\models\AssetAssignment::holderTypeLabels()[$assignment->holder_type] ?? '') ?>: <?= Html::encode($assignment->getHolderLabel()) ?></td>
                        <td>
                            <span class="badge-status <?= $assignment->status === 'active' ? 'status-approved' : 'status-draft' ?>">
                                <?= strtoupper($assignment->status) ?>
                            </span>
                        </td>
                        <td><?= Html::encode($assignment->assignedByStaff->staff_name ?? '-') ?><br><small class="text-muted"><?= Html::encode($assignment->assigned_at) ?></small></td>
                        <td>
                            <?php if ($assignment->status === 'returned'): ?>
                                <?= Html::encode($assignment->returnedByStaff->staff_name ?? '-') ?><br><small class="text-muted"><?= Html::encode($assignment->returned_at) ?></small>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td><?= Html::encode($assignment->remarks) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        $aaDepartmentOptions = \yii\helpers\ArrayHelper::map(\app\models\Department::find()->orderBy('name')->all(), 'id', 'name');
        $aaProjectOptions = \yii\helpers\ArrayHelper::map(\app\models\Project::find()->orderBy('project_name')->all(), 'id', 'project_name');
        ?>

        <?php if (!$model->hasActiveHolder()): ?>
            <div class="pr-action-card">
                <div class="pr-action-title">Assign This Asset</div>
                <?= Html::beginForm(['/asset-assignment/assign', 'hardwareAssetId' => $model->id], 'post') ?>
                    <?= $this->render('_holder_picker', [
                        'prefix' => 'aa-assign',
                        'departmentOptions' => $aaDepartmentOptions,
                        'projectOptions' => $aaProjectOptions,
                    ]) ?>
                    <div class="mt-2">
                        <label class="form-label">Assigned By</label>
                        <div class="input-group" style="max-width:320px;">
                            <input type="text" id="aa-assign-staff-display" class="form-control" readonly placeholder="Click Select...">
                            <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('aa-assign-staff-id','aa-assign-staff-display')">Select</button>
                        </div>
                        <input type="hidden" name="assigned_by" id="aa-assign-staff-id">
                    </div>
                    <input type="text" name="remarks" class="form-control mt-2" style="max-width:320px;" placeholder="Remarks (optional)">
                    <?= Html::submitButton('Assign', ['class' => 'btn btn-primary mt-3']) ?>
                <?= Html::endForm() ?>
            </div>
        <?php else: ?>
            <div class="pr-action-row">
                <div class="pr-action-card">
                    <div class="pr-action-title">Transfer To Someone Else</div>
                    <?= Html::beginForm(['/asset-assignment/transfer', 'hardwareAssetId' => $model->id], 'post') ?>
                        <?= $this->render('_holder_picker', [
                            'prefix' => 'aa-transfer',
                            'departmentOptions' => $aaDepartmentOptions,
                            'projectOptions' => $aaProjectOptions,
                        ]) ?>
                        <div class="mt-2">
                            <label class="form-label">Transferred By</label>
                            <div class="input-group" style="max-width:320px;">
                                <input type="text" id="aa-transfer-staff-display" class="form-control" readonly placeholder="Click Select...">
                                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('aa-transfer-staff-id','aa-transfer-staff-display')">Select</button>
                            </div>
                            <input type="hidden" name="transferred_by" id="aa-transfer-staff-id">
                        </div>
                        <input type="text" name="remarks" class="form-control mt-2" style="max-width:320px;" placeholder="Remarks (optional)">
                        <?= Html::submitButton('Transfer', ['class' => 'btn btn-warning mt-3']) ?>
                    <?= Html::endForm() ?>
                </div>

                <div class="pr-action-card pr-action-card-danger">
                    <div class="pr-action-title text-danger">Return This Asset</div>
                    <?= Html::beginForm(['/asset-assignment/return', 'hardwareAssetId' => $model->id], 'post') ?>
                        <label class="form-label">Returned By</label>
                        <div class="input-group" style="max-width:320px;">
                            <input type="text" id="aa-return-staff-display" class="form-control" readonly placeholder="Click Select...">
                            <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('aa-return-staff-id','aa-return-staff-display')">Select</button>
                        </div>
                        <input type="hidden" name="returned_by" id="aa-return-staff-id">
                        <input type="text" name="remarks" class="form-control mt-2" style="max-width:320px;" placeholder="Reason (optional)">
                        <?= Html::submitButton('Return', ['class' => 'btn btn-danger mt-3']) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
        <?php endif; ?>

        <br>
        <?= Html::a('Edit', ['update', 'id' => $model->id], ['class' => 'btn btn-secondary mt-2']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-outline-danger mt-2',
            'data' => ['method' => 'post', 'confirm' => 'Delete this hardware asset record?'],
        ]) ?>
    </div>

    <div class="col-md-4 text-center">
        <img src="<?= \yii\helpers\Url::to(['qr', 'id' => $model->id]) ?>" alt="QR code" class="border rounded p-2" style="max-width:220px;">
        <p class="text-muted small mt-2">Scan to open this asset's page</p>
    </div>
</div>

<script>
function onHolderPickerTypeChange(prefix) {
    const type = document.getElementById(prefix + '-holder-type-select').value;
    const blocks = {
        staff: document.getElementById(prefix + '-holder-staff-block'),
        project: document.getElementById(prefix + '-holder-project-block'),
        department: document.getElementById(prefix + '-holder-department-block'),
    };
    Object.keys(blocks).forEach(function (key) {
        const block = blocks[key];
        if (!block) {
            return;
        }
        const isActive = key === type;
        block.style.display = isActive ? '' : 'none';
        block.querySelectorAll('.holder-picker-input').forEach(function (input) {
            input.disabled = !isActive;
        });
    });
}
</script>