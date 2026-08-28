<?php
use yii\helpers\Html;

/** @var string $prefix */
/** @var array $departmentOptions */
/** @var array $projectOptions */
?>
<div class="row g-2">
    <div class="col-md-4">
        <label class="form-label">Holder Type</label>
        <select id="<?= $prefix ?>-holder-type-select" name="holder_type" class="form-select" onchange="onHolderPickerTypeChange('<?= $prefix ?>')">
            <option value="">-- Select --</option>
            <option value="staff">Staff</option>
            <option value="project">Project</option>
            <option value="department">Department</option>
        </select>
    </div>
    <div class="col-md-8">
        <label class="form-label">Holder</label>

        <div id="<?= $prefix ?>-holder-staff-block" class="holder-picker-block" style="display:none;">
            <div class="input-group">
                <input type="text" id="<?= $prefix ?>-holder-staff-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('<?= $prefix ?>-holder-id','<?= $prefix ?>-holder-staff-display')">Select</button>
            </div>
            <input type="hidden" name="holder_id" id="<?= $prefix ?>-holder-id" class="holder-picker-input">
        </div>

        <div id="<?= $prefix ?>-holder-project-block" class="holder-picker-block" style="display:none;">
            <select name="holder_id" class="form-select holder-picker-input" disabled>
                <option value="">-- Select Project --</option>
                <?php foreach ($projectOptions as $id => $name): ?>
                    <option value="<?= $id ?>"><?= Html::encode($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="<?= $prefix ?>-holder-department-block" class="holder-picker-block" style="display:none;">
            <select name="holder_id" class="form-select holder-picker-input" disabled>
                <option value="">-- Select Department --</option>
                <?php foreach ($departmentOptions as $id => $name): ?>
                    <option value="<?= $id ?>"><?= Html::encode($name) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
</div>