<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Project $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="project-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'project_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'project_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'department_id')->textInput() ?>

    <?= $form->field($model, 'status')->dropDownList([ 'active' => 'Active', 'completed' => 'Completed', 'on_hold' => 'On hold', ], ['prompt' => '']) ?>

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
