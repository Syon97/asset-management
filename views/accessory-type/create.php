<?php
use yii\helpers\Html;

/** @var app\models\AccessoryType $model */

$this->title = 'Create Accessory Type';
$this->params['breadcrumbs'][] = ['label' => 'Accessory Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="accessory-type-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>