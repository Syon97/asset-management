<?php
use yii\helpers\Html;

/** @var app\models\AccessoryType $model */

$this->title = 'Update: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Accessory Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="accessory-type-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>