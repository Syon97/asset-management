<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\models\Department;
use app\models\Supplier;

/** @var app\models\GoodsReceipt $model */
/** @var app\models\GrnItem[] $items */

$this->title = 'Adhoc Goods Receipt';
$this->params['breadcrumbs'][] = ['label' => 'Goods Receipts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$departmentOptions = ArrayHelper::map(Department::find()->orderBy('name')->all(), 'id', 'name');
$supplierOptions = ArrayHelper::map(Supplier::find()->orderBy('name')->all(), 'id', 'name');

if (!function_exists('renderGrnDirectItemCard')) {
function renderGrnDirectItemCard($i, $item)
{
    $catalogName = $item->catalogItem->item_name ?? '';
    $description = Html::encode($item->description);
    $quantity = Html::encode($item->quantity_received ?: 1);
    $unitPrice = Html::encode($item->unit_price ?: 0);
    $remarks = Html::encode($item->remarks);
    $catalogItemId = Html::encode($item->catalog_item_id);
    $catalogDisplay = Html::encode($catalogName);

    return <<<HTML
    <div class="card mb-3 grn-item-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Item</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Catalog Item (optional)</label>
                    <div class="input-group">
                        <input type="text" id="grn-catalog-display-{$i}" class="form-control" readonly placeholder="Click Select..." value="{$catalogDisplay}">
                        <button type="button" class="btn btn-outline-secondary" onclick="CatalogPicker.open('grn-catalog-id-{$i}','grn-catalog-display-{$i}')">Select</button>
                        <button type="button" class="btn btn-outline-danger" onclick="clearCatalogSelection('grn-catalog-id-{$i}','grn-catalog-display-{$i}')" title="Clear selection">&times;</button>
                    </div>
                    <input type="hidden" name="GrnItem[{$i}][catalog_item_id]" id="grn-catalog-id-{$i}" value="{$catalogItemId}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Description <span class="text-muted">(required if no catalog item selected)</span></label>
                    <input type="text" name="GrnItem[{$i}][description]" id="grn-description-{$i}" value="{$description}" class="form-control">
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-3">
                    <label class="form-label">Qty Received</label>
                    <input type="number" name="GrnItem[{$i}][quantity_received]" value="{$quantity}" min="1" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Unit Price</label>
                    <input type="number" name="GrnItem[{$i}][unit_price]" value="{$unitPrice}" step="0.01" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="GrnItem[{$i}][remarks]" value="{$remarks}" class="form-control">
                </div>
            </div>
        </div>
    </div>
HTML;
}
}
?>
<div class="goods-receipt-create-direct">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">For purchases that didn't go through a formal PR/PO (e.g. bought off Shopee, Lazada, or a local shop). This still stays as Draft until approved — stock only updates once it's approved, same as any other GRN.</p>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'grn_date')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'department_id')->dropDownList($departmentOptions, ['prompt' => '-- Select Location --']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'supplier_id')->dropDownList($supplierOptions, ['prompt' => '-- None / Not Listed --']) ?>
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
    <div id="grn-items-list">
        <?php foreach ($items as $i => $item): ?>
            <?= renderGrnDirectItemCard($i, $item) ?>
        <?php endforeach; ?>
    </div>

    <button type="button" id="add-item-row" class="btn btn-sm btn-secondary mb-3">+ Add Item</button>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save as Draft GRN', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = <?= count($items) ?>;

    // Wire up the "did you mean...?" similarity check for each existing row's description field.
    <?php foreach ($items as $i => $item): ?>
        attachCatalogSimilarityCheck('grn-description-<?= $i ?>');
    <?php endforeach; ?>

    function cardHtml(index) {
        return `
            <div class="card mb-3 grn-item-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>New Item</strong>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Catalog Item (optional)</label>
                            <div class="input-group">
                                <input type="text" id="grn-catalog-display-${index}" class="form-control" readonly placeholder="Click Select...">
                                <button type="button" class="btn btn-outline-secondary" onclick="CatalogPicker.open('grn-catalog-id-${index}','grn-catalog-display-${index}')">Select</button>
                                <button type="button" class="btn btn-outline-danger" onclick="clearCatalogSelection('grn-catalog-id-${index}','grn-catalog-display-${index}')" title="Clear selection">&times;</button>
                            </div>
                            <input type="hidden" name="GrnItem[${index}][catalog_item_id]" id="grn-catalog-id-${index}">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Description <span class="text-muted">(required if no catalog item selected)</span></label>
                            <input type="text" name="GrnItem[${index}][description]" id="grn-description-${index}" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-3">
                            <label class="form-label">Qty Received</label>
                            <input type="number" name="GrnItem[${index}][quantity_received]" class="form-control" min="1" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Unit Price</label>
                            <input type="number" name="GrnItem[${index}][unit_price]" class="form-control" step="0.01" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="GrnItem[${index}][remarks]" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    document.getElementById('add-item-row').addEventListener('click', function () {
        const list = document.getElementById('grn-items-list');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = cardHtml(rowIndex);
        const newCard = wrapper.firstElementChild;
        list.appendChild(newCard);
        attachCatalogSimilarityCheck('grn-description-' + rowIndex);
        rowIndex++;
    });

    document.getElementById('grn-items-list').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (btn) {
            btn.closest('.grn-item-card').remove();
        }
    });
});
</script>