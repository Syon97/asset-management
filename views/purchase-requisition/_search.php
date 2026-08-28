<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\PurchaseRequisitionSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="purchase-requisition-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'pr_no') ?>

    <?= $form->field($model, 'pr_date') ?>

    <?= $form->field($model, 'department_id') ?>

    <?= $form->field($model, 'cost_center_id') ?>

    <?= $form->field($model, 'requested_by') ?>

    <?php // echo $form->field($model, 'purpose') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'order_type') ?>

    <?php // echo $form->field($model, 'account_code') ?>

    <?php // echo $form->field($model, 'customer_code') ?>

    <?php // echo $form->field($model, 'verified_by') ?>

    <?php // echo $form->field($model, 'verified_at') ?>

    <?php // echo $form->field($model, 'approved_by') ?>

    <?php // echo $form->field($model, 'approved_at') ?>

    <?php // echo $form->field($model, 'rejected_by') ?>

    <?php // echo $form->field($model, 'rejected_at') ?>

    <?php // echo $form->field($model, 'rejection_reason') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>