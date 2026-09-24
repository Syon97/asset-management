<?php
use yii\helpers\Html;

/** @var app\models\Department[] $departments */
/** @var app\models\DepartmentVerifier[] $mappings */
/** @var app\models\Staff|null $gmStaff */

$this->title = 'Claim Verifiers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="department-verifier-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Each department's verifier reviews their department's employees' claims before Finance approval. A claim from a department with no verifier configured falls to Admin automatically.</p>

    <div class="pr-action-card" style="max-width: 500px; margin-bottom: 24px;">
        <div class="pr-action-title">General Manager (escalation contact)</div>
        <p class="text-muted small">Used when a department's own configured verifier submits their own claim \u2014 it escalates here instead of being self-verified.</p>
        <?= Html::beginForm(['set-gm'], 'post', ['class' => 'd-flex gap-2 align-items-center']) ?>
            <input type="text" id="gm-staff-display" class="form-control" readonly
                value="<?= Html::encode($gmStaff->staff_name ?? '') ?>" placeholder="Not set">
            <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('gm-staff-id','gm-staff-display')">Select</button>
            <input type="hidden" name="staff_id" id="gm-staff-id">
            <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
        <?= Html::endForm() ?>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr><th>Department</th><th>Current Verifier</th><th>Change</th></tr>
        </thead>
        <tbody>
            <?php foreach ($departments as $dept): ?>
                <?php $mapping = $mappings[$dept->id] ?? null; ?>
                <tr>
                    <td><?= Html::encode($dept->name) ?></td>
                    <td><?= $mapping ? Html::encode($mapping->staff->staff_name ?? '-') : '<span class="text-muted">Not set \u2014 falls to Admin</span>' ?></td>
                    <td>
                        <?= Html::beginForm(['set-verifier', 'departmentId' => $dept->id], 'post', ['class' => 'd-flex gap-2 align-items-center']) ?>
                            <input type="text" id="verifier-display-<?= $dept->id ?>" class="form-control form-control-sm" readonly placeholder="Click Select..." style="max-width:200px;">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="StaffPicker.open('verifier-id-<?= $dept->id ?>','verifier-display-<?= $dept->id ?>')">Select</button>
                            <input type="hidden" name="staff_id" id="verifier-id-<?= $dept->id ?>">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-sm btn-primary']) ?>
                        <?= Html::endForm() ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>