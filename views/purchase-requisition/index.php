<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\PurchaseRequisitionSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Purchase Requisitions';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-requisition-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Purchase Requisition', ['create'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'pr_no',
            'pr_date',

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
                'attribute' => 'cost_center_id',
                'label' => 'Cost Center',
                'value' => function ($model) {
                    return $model->costCenter->code ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\CostCenter::find()->all(), 'id', 'code'
                ),
            ],

            [
                'attribute' => 'requested_by',
                'label' => 'Requested By',
                'value' => function ($model) {
                    return $model->requestedByStaff->staff_name ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\Staff::find()->all(), 'id', 'staff_name'
                ),
            ],

            [
                'attribute' => 'status',
                'label' => 'Status',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::tag('span', strtoupper($model->status), [
                        'class' => "badge-status status-{$model->status}",
                    ]);
                },
                'filter' => [
                    'draft' => 'Draft',
                    'submitted' => 'Submitted',
                    'verified' => 'Verified',
                    'reviewed' => 'Reviewed',
                    'received' => 'Received',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ],
            ],

            [
                'label' => 'Total Amount',
                'value' => function ($model) {
                    return number_format($model->totalAmount, 2);
                },
                'contentOptions' => ['class' => 'text-end'],
            ],

            \app\helpers\GridHelper::actionColumn('{view} {update} {delete}', [
                'visibleButtons' => [
                    'update' => function ($model) {
                        return $model->status === \app\models\PurchaseRequisition::STATUS_DRAFT;
                    },
                    'delete' => function ($model) {
                        return $model->status === \app\models\PurchaseRequisition::STATUS_DRAFT;
                    },
                ],
            ]),
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>