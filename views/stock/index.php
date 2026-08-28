<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\StockSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stock';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Quantity on hand per item and location. Stock updates automatically when a Goods Receipt is approved or stock is issued out.</p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'catalog_item_id',
                'label' => 'Item',
                'value' => function ($model) {
                    return $model->catalogItem->item_name ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\ItemCatalog::find()->orderBy('item_name')->all(), 'id', 'item_name'
                ),
            ],

            [
                'attribute' => 'department_id',
                'label' => 'Location',
                'value' => function ($model) {
                    return $model->department->name ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\Department::find()->all(), 'id', 'name'
                ),
            ],

            [
                'label' => 'Qty on Hand',
                'value' => function ($model) {
                    return $model->quantity_on_hand;
                },
                'contentOptions' => ['class' => 'text-end fw-semibold'],
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{ledger}',
                'buttons' => [
                    'ledger' => function ($url, $model) {
                        return Html::a('History', ['/stock/ledger', 'catalogItemId' => $model->catalog_item_id, 'departmentId' => $model->department_id], [
                            'class' => 'btn btn-sm btn-outline-secondary',
                        ]);
                    },
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>