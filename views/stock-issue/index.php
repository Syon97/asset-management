<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\StockIssue;

/** @var yii\web\View $this */
/** @var app\models\StockIssueSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Stock Issues';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-issue-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Issue Stock', ['create'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'issue_date',

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
                'label' => 'From Location',
                'value' => function ($model) {
                    return $model->department->name ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\Department::find()->all(), 'id', 'name'
                ),
            ],

            [
                'attribute' => 'holder_type',
                'label' => 'Issued To',
                'value' => function ($model) {
                    return (StockIssue::holderTypeLabels()[$model->holder_type] ?? '-') . ': ' . $model->getHolderLabel();
                },
                'filter' => StockIssue::holderTypeLabels(),
            ],

            [
                'attribute' => 'quantity',
                'contentOptions' => ['class' => 'text-end'],
            ],

            \app\helpers\GridHelper::actionColumn('{view}'),
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>