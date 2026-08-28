<?php

use app\models\Project;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\ProjectSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Projects';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Project', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'project_code',
            'project_name',
            [
                'attribute' => 'department_id',
                'label' => 'Department',
                'value' => function ($model) {
                    return $model->department->name ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\Department::find()->all(), 'id', 'name'
                ),
            ],
            [
                'attribute' => 'status',
                'filter' => [
                    'active' => 'Active',
                    'completed' => 'Completed',
                    'on_hold' => 'On Hold',
                ],
            ],

            \app\helpers\GridHelper::actionColumn(),
        ],
    ]); ?>


</div>
