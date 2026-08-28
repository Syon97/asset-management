<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\CostCenterSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Cost Centers';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cost-center-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Cost Center', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'code',
            'name',
            [
                'attribute' => 'is_active',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->is_active
                        ? '<span class="badge-status status-approved">ACTIVE</span>'
                        : '<span class="badge-status status-rejected">INACTIVE</span>';
                },
                'filter' => [1 => 'Active', 0 => 'Inactive'],
            ],

            \app\helpers\GridHelper::actionColumn(),
        ],
    ]); ?>

</div>