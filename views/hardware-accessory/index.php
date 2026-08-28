<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\HardwareAccessory;

/** @var yii\web\View $this */
/** @var app\models\HardwareAccessorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Accessories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="hardware-accessory-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Accessories are attached from a hardware asset's page — open an asset and use "Attach Accessory" there.</p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'accessory_type_id',
                'label' => 'Type',
                'value' => function ($model) {
                    return $model->accessoryType->name ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\AccessoryType::find()->all(), 'id', 'name'
                ),
            ],

            'accessory_no',

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
                    $class = $model->status === HardwareAccessory::STATUS_ATTACHED ? 'status-approved' : 'status-draft';
                    return Html::tag('span', strtoupper($model->status), ['class' => "badge-status {$class}"]);
                },
                'filter' => [
                    HardwareAccessory::STATUS_ATTACHED => 'Attached',
                    HardwareAccessory::STATUS_DETACHED => 'Detached',
                ],
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>