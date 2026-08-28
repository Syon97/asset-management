<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\SoftwareLicense;

/** @var yii\web\View $this */
/** @var app\models\SoftwareLicenseSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Software Licenses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="software-license-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Licenses are added from a hardware asset's page — open an asset and use "Add Software License" there.</p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'software_name',
            'software_version',

            [
                'attribute' => 'hardware_asset_id',
                'label' => 'Hardware Asset',
                'value' => function ($model) {
                    return $model->hardwareAsset->asset_tag ?? '-';
                },
            ],

            [
                'attribute' => 'status',
                'label' => 'Status',
                'format' => 'raw',
                'value' => function ($model) {
                    $class = $model->status === SoftwareLicense::STATUS_ASSIGNED ? 'status-approved' : 'status-draft';
                    return Html::tag('span', strtoupper($model->status), ['class' => "badge-status {$class}"]);
                },
                'filter' => [
                    SoftwareLicense::STATUS_ASSIGNED => 'Assigned',
                    SoftwareLicense::STATUS_UNASSIGNED => 'Unassigned',
                ],
            ],

            \app\helpers\GridHelper::actionColumn('{view}'),
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>