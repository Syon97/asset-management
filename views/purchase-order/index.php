<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\PurchaseOrder;

/** @var yii\web\View $this */
/** @var app\models\PurchaseOrderSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Purchase Orders';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="purchase-order-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Purchase Orders are created by converting an approved Purchase Requisition — open a PR and use "Convert to PO" there.</p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'erp_po_no',
                'label' => 'PO No.',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->erp_po_no) {
                        return Html::encode($model->erp_po_no)
                            . '<br><small class="text-muted">Ref: ' . Html::encode($model->po_no) . '</small>';
                    }
                    return Html::encode($model->po_no)
                        . '<br><small class="text-muted">No ERP PO No. yet</small>';
                },
            ],

            'po_date',

            [
                'attribute' => 'pr_id',
                'label' => 'Source PR',
                'value' => function ($model) {
                    return $model->purchaseRequisition->pr_no ?? '-';
                },
            ],

            [
                'attribute' => 'supplier_id',
                'label' => 'Supplier',
                'value' => function ($model) {
                    return $model->supplier->name ?? '-';
                },
                'filter' => \yii\helpers\ArrayHelper::map(
                    \app\models\Supplier::find()->all(), 'id', 'name'
                ),
            ],

            [
                'attribute' => 'status',
                'label' => 'Status',
                'format' => 'raw',
                'value' => function ($model) {
                    $badgeClass = $model->status === PurchaseOrder::STATUS_SENT ? 'submitted'
                        : (in_array($model->status, [PurchaseOrder::STATUS_CLOSED, PurchaseOrder::STATUS_RECEIVED]) ? 'approved'
                        : ($model->status === PurchaseOrder::STATUS_REJECTED ? 'rejected' : 'draft'));
                    return Html::tag('span', strtoupper(PurchaseOrder::statusLabels()[$model->status] ?? $model->status), [
                        'class' => "badge-status status-{$badgeClass}",
                    ]);
                },
                'filter' => PurchaseOrder::statusLabels(),
            ],

            [
                'label' => 'Total Amount',
                'value' => function ($model) {
                    return number_format($model->totalAmount, 2);
                },
                'contentOptions' => ['class' => 'text-end'],
            ],

            \app\helpers\GridHelper::actionColumn('{view} {delete}', [
                'visibleButtons' => [
                    'delete' => function ($model) {
                        return $model->status === PurchaseOrder::STATUS_DRAFT;
                    },
                ],
            ]),
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>