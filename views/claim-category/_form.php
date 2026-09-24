<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\models\ClaimCategory;

/** @var app\models\ClaimCategory $model */
?>
<div class="claim-category-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'annual_limit_amount')->textInput(['type' => 'number', 'step' => '0.01'])
        ->hint('Leave blank for no limit (e.g. Mileage).') ?>

    <?= $form->field($model, 'is_mileage_type')->checkbox() ?>

    <?= $form->field($model, 'status')->dropDownList(ClaimCategory::statusLabels()) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>