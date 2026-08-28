<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\CostCenter $model */

$this->title = $model->code;
$this->params['breadcrumbs'][] = ['label' => 'Cost Centers', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="cost-center-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this cost center?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'code',
            'name',
            [
                'attribute' => 'is_active',
                'format' => 'raw',
                'value' => $model->is_active ? 'Active' : 'Inactive',
            ],
            'created_at',
            'updated_at',
        ],
    ]) ?>

</div>