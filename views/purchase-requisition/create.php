<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PurchaseRequisition $model */
/** @var app\models\PrItem[] $items */

$this->title = 'Create Purchase Requisition';
$this->params['breadcrumbs'][] = ['label' => 'Purchase Requisitions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-requisition-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
    ]) ?>

</div>