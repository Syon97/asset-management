<?php

use app\models\ItemCatalog;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ItemCatalogSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Item Catalogs';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-catalog-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Item Catalog', ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a('Find Duplicates', ['duplicates'], ['class' => 'btn btn-outline-secondary']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'item_name',
            [
                'attribute' => 'category_id',
                'label' => 'Category',
                'value' => function ($model) {
                    return $model->category->category_name ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\Category::find()->all(), 'id', 'category_name'
                ),
            ],
            [
                'attribute' => 'item_type',
                'filter' => [
                    'hardware' => 'Hardware',
                    'accessory' => 'Accessory',
                    'software' => 'Software',
                    'consumable' => 'Consumable',
                ],
            ],

            \app\helpers\GridHelper::actionColumn(),
        ],
    ]); ?>


</div>
