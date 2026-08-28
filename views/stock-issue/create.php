<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\models\ItemCatalog;
use app\models\Department;
use app\models\Project;
use app\models\StockIssue;

/** @var app\models\StockIssue $model */

$this->title = 'Issue Stock';
$this->params['breadcrumbs'][] = ['label' => 'Stock Issues', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$catalogOptions = ArrayHelper::map(ItemCatalog::find()->orderBy('item_name')->all(), 'id', 'item_name');
$departmentOptions = ArrayHelper::map(Department::find()->orderBy('name')->all(), 'id', 'name');
$projectOptions = ArrayHelper::map(Project::find()->orderBy('project_name')->all(), 'id', 'project_name');
?>
<div class="stock-issue-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'catalog_item_id')->dropDownList($catalogOptions, ['prompt' => '-- Select Item --']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'department_id')->dropDownList($departmentOptions, ['prompt' => '-- Select Location --'])->label('From Location') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'quantity')->textInput(['type' => 'number', 'min' => 1]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'issue_date')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Issued By</label>
            <div class="input-group">
                <input type="text" id="issued-by-display" class="form-control" readonly placeholder="Click Select..."
                    value="<?= Html::encode($model->issuedByStaff->staff_name ?? '') ?>">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('stockissue-issued_by','issued-by-display')">Select</button>
            </div>
            <?= Html::activeHiddenInput($model, 'issued_by', ['id' => 'stockissue-issued_by']) ?>
        </div>
    </div>

    <hr>
    <h5 class="mb-3">Issue To</h5>
    <div class="row">
        <div class="col-md-4">
            <label class="form-label">Recipient Type</label>
            <select id="holder-type-select" name="StockIssue[holder_type]" class="form-select" onchange="onHolderTypeChange()">
                <?php foreach (StockIssue::holderTypeLabels() as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $model->holder_type === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-8">
            <label class="form-label">Recipient</label>

            <div id="holder-staff-block" class="holder-block">
                <div class="input-group">
                    <input type="text" id="holder-staff-display" class="form-control" readonly placeholder="Click Select..."
                        value="<?= $model->holder_type === StockIssue::HOLDER_STAFF ? Html::encode($model->getHolderLabel()) : '' ?>">
                    <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('holder-staff-id','holder-staff-display')">Select</button>
                </div>
                <input type="hidden" name="StockIssue[holder_id]" id="holder-staff-id" class="holder-input"
                    value="<?= $model->holder_type === StockIssue::HOLDER_STAFF ? Html::encode($model->holder_id) : '' ?>">
            </div>

            <div id="holder-project-block" class="holder-block" style="display:none;">
                <select name="StockIssue[holder_id]" class="form-select holder-input" disabled>
                    <option value="">-- Select Project --</option>
                    <?php foreach ($projectOptions as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($model->holder_type === StockIssue::HOLDER_PROJECT && (int)$model->holder_id === (int)$id) ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="holder-department-block" class="holder-block" style="display:none;">
                <select name="StockIssue[holder_id]" class="form-select holder-input" disabled>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departmentOptions as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($model->holder_type === StockIssue::HOLDER_DEPARTMENT && (int)$model->holder_id === (int)$id) ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 2]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Issue Stock', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
function onHolderTypeChange() {
    const type = document.getElementById('holder-type-select').value;
    const blocks = {
        staff: document.getElementById('holder-staff-block'),
        project: document.getElementById('holder-project-block'),
        department: document.getElementById('holder-department-block'),
    };
    Object.keys(blocks).forEach(function (key) {
        const block = blocks[key];
        const isActive = key === type;
        block.style.display = isActive ? '' : 'none';
        block.querySelectorAll('.holder-input').forEach(function (input) {
            input.disabled = !isActive;
        });
    });
}
document.addEventListener('DOMContentLoaded', onHolderTypeChange);
</script>