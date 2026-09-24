<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\HardwareAsset;

/** @var yii\web\View $this */
/** @var app\models\HardwareAssetSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Hardware Assets';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hardware-asset-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (!Yii::$app->user->isGuest && Yii::$app->user->identity->canAccessOperations()): ?>
    <p>
        <?= Html::a('Add Hardware Asset', ['create'], ['class' => 'btn btn-primary']) ?>
    </p>
    <?php endif; ?>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'asset_tag',
            'brand',
            'model',
            'serial_no',

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
                'attribute' => 'status',
                'label' => 'Status',
                'format' => 'raw',
                'value' => function ($model) {
                    $class = $model->status === HardwareAsset::STATUS_ACTIVE ? 'status-approved' : 'status-rejected';
                    return Html::tag('span', strtoupper($model->status), ['class' => "badge-status {$class}"]);
                },
                'filter' => HardwareAsset::statusLabels(),
            ],

            (!Yii::$app->user->isGuest && Yii::$app->user->identity->canAccessOperations())
                ? \app\helpers\GridHelper::actionColumn()
                : \app\helpers\GridHelper::actionColumn('{view}'),
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>