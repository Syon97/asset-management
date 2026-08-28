<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\StaffSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="staff-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'staff_id') ?>

    <?= $form->field($model, 'staff_name') ?>

    <?= $form->field($model, 'employment_type') ?>

    <?= $form->field($model, 'agency') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'join_date') ?>

    <?php // echo $form->field($model, 'department_id') ?>

    <?php // echo $form->field($model, 'position') ?>

    <?php // echo $form->field($model, 'location') ?>

    <?php // echo $form->field($model, 'photo') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
