<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\models\AccessoryType;

/** @var app\models\HardwareAccessory $model */
/** @var app\models\HardwareAsset $hardwareAsset */

$this->title = 'Attach Accessory to ' . $hardwareAsset->asset_tag;
$this->params['breadcrumbs'][] = ['label' => $hardwareAsset->asset_tag, 'url' => ['/hardware-asset/view', 'id' => $hardwareAsset->id]];
$this->params['breadcrumbs'][] = 'Attach Accessory';

$typeOptions = ArrayHelper::map(AccessoryType::find()->orderBy('name')->all(), 'id', 'name');
?>
<div class="hardware-accessory-attach">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'accessory_type_id')->dropDownList($typeOptions, ['prompt' => '-- Select Type --']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'accessory_no')->textInput(['maxlength' => true, 'placeholder' => 'Serial / identifier']) ?>
        </div>
        <div class="col-md-4">
            <label class="form-label">Attached By</label>
            <div class="input-group">
                <input type="text" id="attached-by-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('hardwareaccessory-attached_by','attached-by-display')">Select</button>
            </div>
            <?= Html::activeHiddenInput($model, 'attached_by', ['id' => 'hardwareaccessory-attached_by']) ?>
            <?php if ($model->hasErrors('attached_by')): ?>
                <div class="text-danger small mt-1"><?= Html::encode($model->getFirstError('attached_by')) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?= $form->field($model, 'accessoryImageFile')->fileInput() ?>
    <?= $form->field($model, 'remarks')->textarea(['rows' => 2]) ?>

    <div class="form-group mt-3">
        <?= Html::submitButton('Attach', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancel', ['/hardware-asset/view', 'id' => $hardwareAsset->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>