<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Category $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="category-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'category_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'tag_prefix')->textInput(['maxlength' => 10, 'placeholder' => 'e.g. FF, MA, C'])
        ->hint('Used as the prefix for auto-generated asset tags in this category (e.g. "FF-0001/26"). Leave blank to fall back to the generic HW-YYYY-NNNN scheme.') ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'created_at')->textInput([
        'disabled' => true,
    ]) ?>

    <?= $form->field($model, 'updated_at')->textInput([
        'disabled' => true,
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>