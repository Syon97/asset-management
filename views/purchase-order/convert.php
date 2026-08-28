<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\models\Supplier;

/** @var app\models\PurchaseOrder $model */
/** @var app\models\PoItem[] $items */
/** @var app\models\PurchaseRequisition $pr */

$this->title = 'Convert ' . $pr->pr_no . ' to Purchase Order';
$this->params['breadcrumbs'][] = ['label' => 'Purchase Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$supplierOptions = ArrayHelper::map(Supplier::find()->orderBy('name')->all(), 'id', 'name');
?>
<div class="purchase-order-convert">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Converting from <strong><?= Html::encode($pr->pr_no) ?></strong> — items and prices below are pre-filled from the requisition and can be adjusted before saving.</p>

    <?php $form = ActiveForm::begin(); ?>

    <h5 class="mb-3">Order Details</h5>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'po_date')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'supplier_id')->dropDownList(
                $supplierOptions,
                ['prompt' => '-- Select Supplier --']
            ) ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Department</label>
            <input type="text" class="form-control" readonly value="<?= Html::encode($pr->department->name ?? '-') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Cost Center</label>
            <input type="text" class="form-control" readonly value="<?= Html::encode($pr->costCenter->label ?? '-') ?>">
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <label class="form-label">Prepared By</label>
            <div class="input-group">
                <input type="text" id="prepared-by-display" class="form-control" readonly
                    value="<?= Html::encode($model->preparedByStaff->staff_name ?? '') ?>"
                    placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('purchaseorder-prepared_by','prepared-by-display')">Select</button>
            </div>
            <?= Html::activeHiddenInput($model, 'prepared_by', ['id' => 'purchaseorder-prepared_by']) ?>
            <?php if ($model->hasErrors('prepared_by')): ?>
                <div class="text-danger small mt-1"><?= Html::encode($model->getFirstError('prepared_by')) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'payment_terms')->textInput(['maxlength' => true, 'placeholder' => 'e.g. 30 days from invoice']) ?>
        </div>
    </div>

    <?= $form->field($model, 'delivery_address')->textarea(['rows' => 2]) ?>
    <?= $form->field($model, 'remarks')->textarea(['rows' => 2]) ?>

    <hr>
    <h5 class="mb-3">Items</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Description</th>
                <th style="width:10%;">Qty</th>
                <th style="width:14%;">Unit Price</th>
                <th style="width:14%;">Total</th>
                <th style="width:20%;">Remarks</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
                <tr class="po-item-row">
                    <td>
                        <?= Html::encode($item->description) ?>
                        <?php if ($item->specification): ?><br><small class="text-muted"><?= Html::encode($item->specification) ?></small><?php endif; ?>
                        <input type="hidden" name="PoItem[<?= $i ?>][description]" value="<?= Html::encode($item->description) ?>">
                        <input type="hidden" name="PoItem[<?= $i ?>][specification]" value="<?= Html::encode($item->specification) ?>">
                        <input type="hidden" name="PoItem[<?= $i ?>][item_type]" value="<?= Html::encode($item->item_type) ?>">
                        <input type="hidden" name="PoItem[<?= $i ?>][pr_item_id]" value="<?= Html::encode($item->pr_item_id) ?>">
                    </td>
                    <td><input type="number" name="PoItem[<?= $i ?>][quantity]" value="<?= $item->quantity ?>" min="1" class="form-control po-item-qty"></td>
                    <td><input type="number" name="PoItem[<?= $i ?>][unit_price]" value="<?= $item->unit_price ?>" step="0.01" class="form-control po-item-price"></td>
                    <td class="align-middle po-item-total"><?= number_format($item->quantity * $item->unit_price, 2) ?></td>
                    <td><input type="text" name="PoItem[<?= $i ?>][remarks]" value="<?= Html::encode($item->remarks) ?>" class="form-control"></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-group">
        <?= Html::submitButton('Save as Draft PO', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cancel', ['/purchase-requisition/view', 'id' => $pr->id], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
document.querySelectorAll('.po-item-row').forEach(function (row) {
    const qtyInput = row.querySelector('.po-item-qty');
    const priceInput = row.querySelector('.po-item-price');
    const totalCell = row.querySelector('.po-item-total');

    function recalc() {
        const qty = parseFloat(qtyInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;
        totalCell.textContent = (qty * price).toFixed(2);
    }

    qtyInput.addEventListener('input', recalc);
    priceInput.addEventListener('input', recalc);
});
</script>