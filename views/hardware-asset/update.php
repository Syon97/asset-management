<?php
use yii\helpers\Html;

/** @var app\models\HardwareAsset $model */

$this->title = 'Update: ' . $model->asset_tag;
$this->params['breadcrumbs'][] = ['label' => 'Hardware Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->asset_tag, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="hardware-asset-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>