<?php
use yii\helpers\Html;

/** @var app\models\Claim $model */
/** @var app\models\ClaimItem[] $items */
/** @var app\models\ClaimCategory[] $categories */

$this->title = 'New Claim';
$this->params['breadcrumbs'][] = ['label' => 'My Claims', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="claim-create">
    <h1><?= Html::encode($this->title) ?></h1>
    <?= $this->render('_form', ['model' => $model, 'items' => $items, 'categories' => $categories, 'remainingLimits' => $remainingLimits]) ?>
</div>