<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ItemCatalog $model */

$this->title = 'Create Item Catalog';
$this->params['breadcrumbs'][] = ['label' => 'Item Catalogs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-catalog-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
