<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ItemCatalog $model */

$this->title = 'Update Item Catalog: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Item Catalogs', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="item-catalog-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
