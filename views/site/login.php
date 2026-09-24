<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var app\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Login to your account';
?>
<style>
    body { background: var(--color-bg, #F6F7F9); }
</style>

<div class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card border-0 shadow-sm" style="width: 100%; max-width: 400px;">
        <div class="card-body p-4 p-md-5">

            <div class="text-center mb-4">
                <span style="font-size: 22px; font-weight: 700; color: var(--color-primary, #1F4E5F);">
                    Asset<span style="color: var(--color-accent, #E08E2F);">Track</span>
                </span>
                <p class="text-muted small mt-2 mb-0">Log in to continue</p>
                <p class="text-muted small mb-0">First time? Your username and password are both your Staff ID.</p>
            </div>

            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => 'Staff ID']) ?>

                <?= $form->field($model, 'password')->passwordInput(['placeholder' => 'Password']) ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="d-grid mt-4">
                    <?= Html::submitButton('Log In', ['class' => 'btn btn-primary btn-lg']) ?>
                </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>