<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\models\HardwareAsset;

/** @var app\models\SoftwareLicense $model */
/** @var app\models\SoftwareLicense $oldLicense */

$this->title = 'Reassign License: ' . $model->software_name;
$this->params['breadcrumbs'][] = ['label' => 'Software Licenses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$hardwareOptions = ArrayHelper::map(HardwareAsset::find()->orderBy('asset_tag')->all(), 'id', 'asset_tag');
?>
<div class="software-license-reassign">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">This creates a new assignment record for the same license — the previous assignment (<?= Html::encode($oldLicense->hardwareAsset->asset_tag ?? '-') ?>) stays as history.</p>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'hardware_asset_id')->dropDownList($hardwareOptions, ['prompt' => '-- Select Hardware Asset --']) ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Assigned By</label>
            <div class="input-group">
                <input type="text" id="assigned-by-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('softwarelicense-assigned_by','assigned-by-display')">Select</button>
            </div>
            <?= Html::activeHiddenInput($model, 'assigned_by', ['id' => 'softwarelicense-assigned_by']) ?>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('Reassign', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>