<?php
use yii\helpers\Html;

/** @var app\models\ClaimCategory $model */

$this->title = 'Update: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Claim Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="claim-category-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>