<?php
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var app\models\GoodsReceipt $model */
/** @var app\models\GrnItem[] $items */
/** @var app\models\PurchaseOrder $po */

$this->title = 'Receive Goods for ' . $po->po_no;
$this->params['breadcrumbs'][] = ['label' => 'Goods Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$poItemsById = \yii\helpers\ArrayHelper::index($po->items, 'id');
?>
<div class="goods-receipt-create">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Enter the quantity actually received for each line. Leave a line at 0 if nothing from it arrived in this delivery. This GRN stays as Draft — stock only updates once it's approved.</p>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'grn_date')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Department</label>
            <input type="text" class="form-control" readonly value="<?= Html::encode($po->department->name ?? '-') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Received By</label>
            <div class="input-group">
                <input type="text" id="received-by-display" class="form-control" readonly placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('goodsreceipt-received_by','received-by-display')">Select</button>
            </div>
            <?= Html::activeHiddenInput($model, 'received_by', ['id' => 'goodsreceipt-received_by']) ?>
            <?php if ($model->hasErrors('received_by')): ?>
                <div class="text-danger small mt-1"><?= Html::encode($model->getFirstError('received_by')) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 2]) ?>

    <hr>
    <h5 class="mb-3">Items</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width:10%;">Ordered</th>
                <th style="width:12%;">Already Received</th>
                <th style="width:12%;">Remaining</th>
                <th style="width:14%;">Receiving Now</th>
                <th style="width:20%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
                <?php $poItem = $poItemsById[$item->po_item_id] ?? null; ?>
                <?php $remaining = $poItem ? ($poItem->quantity - $poItem->quantity_received) : 0; ?>
                <tr>
                    <td>
                        <?= Html::encode($poItem->description ?? '') ?>
                        <?php if ($poItem && $poItem->specification): ?><br><small class="text-muted"><?= Html::encode($poItem->specification) ?></small><?php endif; ?>
                        <input type="hidden" name="GrnItem[<?= $i ?>][po_item_id]" value="<?= $item->po_item_id ?>">
                        <input type="hidden" name="GrnItem[<?= $i ?>][catalog_item_id]" value="<?= Html::encode($item->catalog_item_id) ?>">
                    </td>
                    <td class="text-center"><?= $poItem->quantity ?? '-' ?></td>
                    <td class="text-center"><?= $poItem->quantity_received ?? '-' ?></td>
                    <td class="text-center"><?= $remaining ?></td>
                    <td>
                        <input type="number" name="GrnItem[<?= $i ?>][quantity_received]" value="<?= $item->quantity_received ?>" min="0" max="<?= $remaining ?>" class="form-control">
                        <?php if ($item->hasErrors('quantity_received')): ?>
                            <div class="text-danger small mt-1"><?= Html::encode($item->getFirstError('quantity_received')) ?></div>
                        <?php endif; ?>
                    </td>
                    <td><input type="text" name="GrnItem[<?= $i ?>][remarks]" value="<?= Html::encode($item->remarks) ?>" class="form-control"></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-group">
        <?= Html::submitButton('Save as Draft GRN', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancel', ['/purchase-order/view', 'id' => $po->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>