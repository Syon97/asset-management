<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PrItem $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="pr-item-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'pr_id')->textInput() ?>

    <?= $form->field($model, 'catalog_item_id')->textInput() ?>

    <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'specification')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'quantity')->textInput() ?>

    <?= $form->field($model, 'unit_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'total_price')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'remarks')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
