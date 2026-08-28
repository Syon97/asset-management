<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CostCenter $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="cost-center-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'e.g. MIS-102']) ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'e.g. MIS / IT']) ?>

    <?= $form->field($model, 'is_active')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>