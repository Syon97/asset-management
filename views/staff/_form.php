<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Staff $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="staff-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'staff_id')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'staff_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'employment_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'agency')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'join_date')->textInput() ?>

    <?= $form->field($model, 'department_id')->dropDownList(
        \yii\helpers\ArrayHelper::map(\app\models\Department::find()->all(), 'id', 'name'),
        ['prompt' => '-- Select Department --']
    ) ?>

    <?= $form->field($model, 'position')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'location')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'photo')->textInput(['maxlength' => true]) ?>

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
