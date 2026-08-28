<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\models\Category;
use app\models\Department;
use app\models\Supplier;
use app\models\Project;
use app\models\HardwareAsset;

/** @var app\models\HardwareAsset $model */

$categoryOptions = ArrayHelper::map(Category::find()->all(), 'id', 'category_name');
$departmentOptions = ArrayHelper::map(Department::find()->orderBy('name')->all(), 'id', 'name');
$supplierOptions = ArrayHelper::map(Supplier::find()->orderBy('name')->all(), 'id', 'name');
$projectOptions = ArrayHelper::map(Project::find()->orderBy('project_name')->all(), 'id', 'project_name');
?>

<div class="hardware-asset-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <h5 class="mb-3">Asset Details</h5>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'category_id')->dropDownList($categoryOptions, ['prompt' => '-- Select Category --']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'department_id')->dropDownList($departmentOptions, ['prompt' => '-- Select Location --']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'brand')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'model')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'serial_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'status')->dropDownList(HardwareAsset::statusLabels()) ?>
        </div>
    </div>

    <hr>
    <h5 class="mb-3">Purchase &amp; Warranty</h5>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'purchase_price')->textInput(['type' => 'number', 'step' => '0.01']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'purchase_date')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'warranty_start_date')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'warranty_end_date')->textInput(['type' => 'date']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'supplier_id')->dropDownList($supplierOptions, ['prompt' => '-- Select Supplier --']) ?>
        </div>
    </div>

    <hr>
    <h5 class="mb-3">Photos</h5>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'productImageFile')->fileInput() ?>
            <?php if ($model->getImageUrl('product_image')): ?>
                <img src="<?= Html::encode($model->getImageUrl('product_image')) ?>" style="max-width:120px;" class="mt-2 rounded border">
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'serialImageFile')->fileInput() ?>
            <?php if ($model->getImageUrl('serial_image')): ?>
                <img src="<?= Html::encode($model->getImageUrl('serial_image')) ?>" style="max-width:120px;" class="mt-2 rounded border">
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'serialCommandImageFile')->fileInput() ?>
            <?php if ($model->getImageUrl('serialcommand_image')): ?>
                <img src="<?= Html::encode($model->getImageUrl('serialcommand_image')) ?>" style="max-width:120px;" class="mt-2 rounded border">
            <?php endif; ?>
        </div>
    </div>

    <?php if ($model->isNewRecord): ?>
    <hr>
    <h5 class="mb-3">Initial Holder <span class="text-muted fw-normal">(optional - who has this right now. Once created, use Assign/Transfer/Return on the asset page to change this, so history stays accurate.)</span></h5>
    <div class="row">
        <div class="col-md-4">
            <label class="form-label">Holder Type</label>
            <select id="hw-holder-type-select" name="HardwareAsset[current_holder_type]" class="form-select" onchange="onHwHolderTypeChange()">
                <option value="">-- None --</option>
                <?php foreach (HardwareAsset::holderTypeLabels() as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $model->current_holder_type === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">Holder</label>

            <div id="hw-holder-staff-block" class="hw-holder-block" style="display:none;">
                <div class="input-group">
                    <input type="text" id="hw-holder-staff-display" class="form-control" readonly placeholder="Click Select..."
                        value="<?= $model->current_holder_type === HardwareAsset::HOLDER_STAFF ? Html::encode($model->getHolderLabel()) : '' ?>">
                    <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('hw-holder-staff-id','hw-holder-staff-display')">Select</button>
                </div>
                <input type="hidden" name="HardwareAsset[current_holder_id]" id="hw-holder-staff-id" class="hw-holder-input"
                    value="<?= $model->current_holder_type === HardwareAsset::HOLDER_STAFF ? Html::encode($model->current_holder_id) : '' ?>">
            </div>

            <div id="hw-holder-project-block" class="hw-holder-block" style="display:none;">
                <select name="HardwareAsset[current_holder_id]" class="form-select hw-holder-input" disabled>
                    <option value="">-- Select Project --</option>
                    <?php foreach ($projectOptions as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($model->current_holder_type === HardwareAsset::HOLDER_PROJECT && (int)$model->current_holder_id === (int)$id) ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="hw-holder-department-block" class="hw-holder-block" style="display:none;">
                <select name="HardwareAsset[current_holder_id]" class="form-select hw-holder-input" disabled>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departmentOptions as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($model->current_holder_type === HardwareAsset::HOLDER_DEPARTMENT && (int)$model->current_holder_id === (int)$id) ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-md-4">
            <label class="form-label">Assigned By <span class="text-muted">(required if a holder is set above)</span></label>
            <div class="input-group">
                <input type="text" id="hw-assigned-by-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('hw-assigned-by-id','hw-assigned-by-display')">Select</button>
            </div>
            <input type="hidden" name="assigned_by" id="hw-assigned-by-id">
        </div>
    </div>
    <?php endif; ?>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 2]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
function onHwHolderTypeChange() {
    const type = document.getElementById('hw-holder-type-select').value;
    const blocks = {
        staff: document.getElementById('hw-holder-staff-block'),
        project: document.getElementById('hw-holder-project-block'),
        department: document.getElementById('hw-holder-department-block'),
    };
    Object.keys(blocks).forEach(function (key) {
        const block = blocks[key];
        const isActive = key === type;
        block.style.display = isActive ? '' : 'none';
        block.querySelectorAll('.hw-holder-input').forEach(function (input) {
            input.disabled = !isActive;
        });
    });
}
document.addEventListener('DOMContentLoaded', onHwHolderTypeChange);
</script>