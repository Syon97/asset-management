<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var app\models\SoftwareLicense $model */
/** @var app\models\HardwareAsset $hardwareAsset */

$this->title = 'Add Software License to ' . $hardwareAsset->asset_tag;
$this->params['breadcrumbs'][] = ['label' => $hardwareAsset->asset_tag, 'url' => ['/hardware-asset/view', 'id' => $hardwareAsset->id]];
$this->params['breadcrumbs'][] = 'Add Software License';
?>
<div class="software-license-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'software_name')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'software_version')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'license_key')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <label class="form-label">Assigned By</label>
            <div class="input-group">
                <input type="text" id="assigned-by-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('softwarelicense-assigned_by','assigned-by-display')">Select</button>
            </div>
            <?= Html::activeHiddenInput($model, 'assigned_by', ['id' => 'softwarelicense-assigned_by']) ?>
            <?php if ($model->hasErrors('assigned_by')): ?>
                <div class="text-danger small mt-1"><?= Html::encode($model->getFirstError('assigned_by')) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 2]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Add License', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancel', ['/hardware-asset/view', 'id' => $hardwareAsset->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>