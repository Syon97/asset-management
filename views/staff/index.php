<?php

use app\models\Staff;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\StaffSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Staff';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="staff-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::beginForm(['sync'], 'post', ['id' => 'staff-sync-form']) ?>
            <button type="submit" class="btn btn-success" id="staff-sync-btn">
                <i class="bi bi-arrow-repeat"></i> Sync Now
            </button>
        <?= Html::endForm() ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'staff_id',
            'staff_name',
            'department_text',
            [
                'attribute' => 'department_id',
                'label' => 'Matched Department',
                'value' => function ($model) {
                    return $model->department ? $model->department->name : '(unmatched)';
                },
            ],
            'position',
            'synced_at',
            // ['class' => 'yii\grid\ActionColumn', 'template' => '{view} {delete}'],
            \app\helpers\GridHelper::actionColumn('{view} {delete}'),
        ],
    ]) ?>


</div>
