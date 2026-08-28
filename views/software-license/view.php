<?php
use yii\helpers\Html;
use app\models\SoftwareLicense;

/** @var app\models\SoftwareLicense $model */
$this->title = $model->software_name;
?>

<h1><?= Html::encode($model->software_name) ?>
    <span class="badge-status <?= $model->status === SoftwareLicense::STATUS_ASSIGNED ? 'status-approved' : 'status-draft' ?>">
        <?= strtoupper($model->status) ?>
    </span>
</h1>

<div class="pr-meta-grid">
    <div class="pr-meta-item">
        <span class="pr-meta-label">Version</span>
        <span class="pr-meta-value"><?= Html::encode($model->software_version) ?: '—' ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">License Key</span>
        <span class="pr-meta-value"><?= Html::encode($model->license_key) ?: '—' ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Hardware Asset</span>
        <span class="pr-meta-value">
            <?php if ($model->hardwareAsset): ?>
                <?= Html::a(Html::encode($model->hardwareAsset->asset_tag), ['/hardware-asset/view', 'id' => $model->hardware_asset_id]) ?>
            <?php else: ?>—<?php endif; ?>
        </span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Assigned By</span>
        <span class="pr-meta-value"><?= Html::encode($model->assignedByStaff->staff_name ?? '-') ?> on <?= Html::encode($model->assigned_at) ?></span>
    </div>
    <?php if ($model->status === SoftwareLicense::STATUS_UNASSIGNED): ?>
        <div class="pr-meta-item">
            <span class="pr-meta-label">Unassigned By</span>
            <span class="pr-meta-value"><?= Html::encode($model->unassignedByStaff->staff_name ?? '-') ?> on <?= Html::encode($model->unassigned_at) ?></span>
        </div>
    <?php endif; ?>
</div>

<?php if ($model->remarks): ?>
    <div class="pr-notes-box">
        <strong>Remarks</strong>
        <p class="mb-0"><?= nl2br(Html::encode($model->remarks)) ?></p>
    </div>
<?php endif; ?>

<?php if ($model->status === SoftwareLicense::STATUS_UNASSIGNED): ?>
    <?= Html::a('Reassign to Another Asset', ['reassign', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
<?php endif; ?>