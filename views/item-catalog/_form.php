<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\ItemCatalog $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="item-catalog-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'item_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'category_id')->dropDownList(
        \yii\helpers\ArrayHelper::map(\app\models\Category::find()->all(), 'id', 'category_name'),
        ['prompt' => '-- Select Category --']
    ) ?>

    <?= $form->field($model, 'item_type')->dropDownList([
        'hardware' => 'Hardware',
        'accessory' => 'Accessory',
        'software' => 'Software',
        'consumable' => 'Consumable',
    ]) ?>

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
