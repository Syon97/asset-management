<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PurchaseRequisition $model */
/** @var app\models\PrItem[] $items */

$this->title = 'Update ' . $model->pr_no;
$this->params['breadcrumbs'][] = ['label' => 'Purchase Requisitions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->pr_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="purchase-requisition-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
    ]) ?>

</div>