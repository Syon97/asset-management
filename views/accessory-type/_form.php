<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var app\models\AccessoryType $model */
?>

<div class="accessory-type-form">
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'e.g. Docking Station']) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>