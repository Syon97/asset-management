<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var app\models\ChangePasswordForm $model */

$this->title = 'Change Password';
?>
<div class="site-change-password" style="max-width: 420px; margin: 60px auto;">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity->must_change_password): ?>
        <p class="text-muted">For security, you need to set your own password before continuing - your account currently uses the default (same as your staff ID).</p>
    <?php endif; ?>

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'currentPassword')->passwordInput() ?>
        <?= $form->field($model, 'newPassword')->passwordInput() ?>
        <?= $form->field($model, 'newPasswordRepeat')->passwordInput() ?>

        <div class="form-group">
            <?= Html::submitButton('Update Password', ['class' => 'btn btn-primary w-100']) ?>
        </div>

    <?php ActiveForm::end(); ?>
</div>