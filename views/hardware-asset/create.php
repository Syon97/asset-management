<?php
use yii\helpers\Html;

/** @var app\models\HardwareAsset $model */

$this->title = 'Add Hardware Asset';
$this->params['breadcrumbs'][] = ['label' => 'Hardware Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hardware-asset-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>