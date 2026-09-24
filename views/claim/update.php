<?php
use yii\helpers\Html;

/** @var app\models\Claim $model */
/** @var app\models\ClaimItem[] $items */
/** @var app\models\ClaimCategory[] $categories */

$this->title = 'Edit: ' . $model->claim_no;
$this->params['breadcrumbs'][] = ['label' => 'My Claims', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->claim_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Edit';
?>
<div class="claim-update">
    <h1><?= Html::encode($this->title) ?></h1>
    <?php if ($model->status === \app\models\Claim::STATUS_REJECTED): ?>
        <div class="alert alert-warning">This claim was rejected. Editing and saving will keep it in Draft until you resubmit.</div>
    <?php endif; ?>
    <?= $this->render('_form', ['model' => $model, 'items' => $items, 'categories' => $categories, 'remainingLimits' => $remainingLimits]) ?>
</div>