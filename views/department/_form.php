<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Department $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="department-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'parent_id')->dropDownList(
        \yii\helpers\ArrayHelper::map(\app\models\Department::find()->where(['<>', 'id', $model->id])->all(), 'id', 'name'),
        ['prompt' => '-- No parent (top level) --']
    ) ?>

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
