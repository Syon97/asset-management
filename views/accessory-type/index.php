<?php

use yii\helpers\Html;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\AccessoryTypeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Accessory Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="accessory-type-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Accessory Type', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            \app\helpers\GridHelper::actionColumn(),
        ],
    ]); ?>

</div>