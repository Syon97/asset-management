<?php
use yii\helpers\Html;

/** @var app\models\ClaimCategory $model */

$this->title = 'Add Claim Category';
$this->params['breadcrumbs'][] = ['label' => 'Claim Categories', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="claim-category-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>