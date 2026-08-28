<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\GoodsReceipt;

/** @var yii\web\View $this */
/** @var app\models\GoodsReceiptSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Goods Receipts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="goods-receipt-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">
        Normal receipts come from a sent Purchase Order — open a PO and use "Receive Goods" there.
        For purchases with no PR/PO (e.g. Shopee, Lazada), use the button below.
    </p>
    <p>
        <?= Html::a('Adhoc Goods Receipt', ['create-direct'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'grn_no',
            'grn_date',

            [
                'attribute' => 'po_id',
                'label' => 'Source',
                'format' => 'raw',
                'value' => function ($model) {
                    if ($model->isDirect()) {
                        return '<span class="badge-status status-verified">ADHOC</span>';
                    }
                    return $model->purchaseOrder->po_no ?? '-';
                },
            ],

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
                'label' => 'Status',
                'format' => 'raw',
                'value' => function ($model) {
                    $badgeClass = $model->status === GoodsReceipt::STATUS_APPROVED ? 'approved'
                        : ($model->status === GoodsReceipt::STATUS_REJECTED ? 'rejected' : 'draft');
                    return Html::tag('span', strtoupper($model->status), ['class' => "badge-status status-{$badgeClass}"]);
                },
                'filter' => GoodsReceipt::statusLabels(),
            ],

            \app\helpers\GridHelper::actionColumn('{view}'),
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>