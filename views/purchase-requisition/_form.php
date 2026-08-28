<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\models\CostCenter;
use app\models\PurchaseRequisition;

/** @var app\models\PurchaseRequisition $model */
/** @var app\models\PrItem[] $items */

$costCenterOptions = ArrayHelper::map(CostCenter::find()->andWhere(['is_active' => 1])->orderBy('code')->all(), 'id', 'label');
$customerCodeOptions = PurchaseRequisition::optsCustomerCode();

if (!function_exists('renderPrItemCard')) {
function renderPrItemCard($i, $item)
{
    $catalogName = $item->catalogItem->item_name ?? '';
    $catalogItemId = Html::encode($item->catalog_item_id);
    $catalogDisplay = Html::encode($catalogName);
    $itemType = Html::encode($item->item_type);
    $description = Html::encode($item->description);
    $specification = Html::encode($item->specification);
    $quantity = Html::encode($item->quantity ?: 1);
    $neededDate = Html::encode($item->needed_date);
    $unitPrice = Html::encode($item->unit_price ?: 0);
    $quotationRef = Html::encode($item->quotation_ref_no);
    $purpose = Html::encode($item->purpose);
    $remarks = Html::encode($item->remarks);
    $id = Html::encode($item->id);
    $descriptionError = $item->hasErrors('description')
        ? '<div class="text-danger small mt-1">' . Html::encode($item->getFirstError('description')) . '</div>'
        : '';

    return <<<HTML
    <div class="card mb-3 pr-item-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Item</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label">Catalog Item (optional)</label>
                    <div class="input-group">
                        <input type="text" id="pr-catalog-display-{$i}" class="form-control" readonly placeholder="Click Select..." value="{$catalogDisplay}">
                        <button type="button" class="btn btn-outline-secondary" onclick="CatalogPicker.open('pr-catalog-id-{$i}','pr-catalog-display-{$i}', function(item){ fillPrDescription({$i}, item.item_name); })">Select</button>
                        <button type="button" class="btn btn-outline-danger" onclick="clearCatalogSelection('pr-catalog-id-{$i}','pr-catalog-display-{$i}')" title="Clear selection">&times;</button>
                    </div>
                    <input type="hidden" name="PrItem[{$i}][catalog_item_id]" id="pr-catalog-id-{$i}" value="{$catalogItemId}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <input type="text" name="PrItem[{$i}][item_type]" value="{$itemType}" class="form-control" placeholder="e.g. Size/Code/Colour">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Description<span class="text-muted">(required if no catalog item selected)</span></label>
                    <input type="text" name="PrItem[{$i}][description]" id="pr-description-{$i}" value="{$description}" class="form-control pr-item-description">
                    {$descriptionError}
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-3">
                    <label class="form-label">Specification</label>
                    <input type="text" name="PrItem[{$i}][specification]" value="{$specification}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Qty</label>
                    <input type="number" name="PrItem[{$i}][quantity]" value="{$quantity}" class="form-control pr-item-qty" min="1">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Needed</label>
                    <input type="date" name="PrItem[{$i}][needed_date]" value="{$neededDate}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Unit Price</label>
                    <input type="number" name="PrItem[{$i}][unit_price]" value="{$unitPrice}" class="form-control pr-item-price" step="0.01">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Quotation Ref No.</label>
                    <input type="text" name="PrItem[{$i}][quotation_ref_no]" value="{$quotationRef}" class="form-control">
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-8">
                    <label class="form-label">Purpose (this item)</label>
                    <input type="text" name="PrItem[{$i}][purpose]" value="{$purpose}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="PrItem[{$i}][remarks]" value="{$remarks}" class="form-control">
                </div>
            </div>
            <input type="hidden" name="PrItem[{$i}][id]" value="{$id}">
        </div>
    </div>
HTML;
}
}
?>

<div class="purchase-requisition-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <h5 class="mb-3">Requisition Details</h5>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'pr_date')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Requested By</label>
            <div class="input-group">
                <input type="text" id="requested-by-display" class="form-control" readonly
                    value="<?= Html::encode(($model->requestedByStaff->staff_name ?? '') . (($model->requestedByStaff->department->name ?? '') ? ' — ' . $model->requestedByStaff->department->name : '')) ?>"
                    placeholder="Click Select...">
                <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('purchaserequisition-requested_by','requested-by-display', function(item){ document.getElementById('department-display').value = item.department || ''; })">Select</button>
            </div>
            <?= Html::activeHiddenInput($model, 'requested_by', ['id' => 'purchaserequisition-requested_by']) ?>
            <?php if ($model->hasErrors('requested_by')): ?>
                <div class="text-danger small mt-1"><?= Html::encode($model->getFirstError('requested_by')) ?></div>
            <?php endif; ?>
        </div>
        <div class="col-md-3">
            <label class="form-label">Department</label>
            <input type="text" id="department-display" class="form-control" readonly
                value="<?= Html::encode($model->department->name ?? '') ?>">
            <div class="form-text">Auto-filled from the requester's own department.</div>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'cost_center_id')->dropDownList(
                $costCenterOptions,
                ['prompt' => '-- Select Cost Center --']
            ) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model, 'account_code')->textInput(['maxlength' => true, 'placeholder' => 'For Accounts/Purchasing use']) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model, 'customer_code')->dropDownList(
                $customerCodeOptions,
                ['prompt' => '-- None --']
            ) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'order_type')->radioList(
                PurchaseRequisition::optsOrderType(),
                ['item' => function ($index, $label, $name, $checked, $value) {
                    return '<label class="form-check form-check-inline">'
                        . Html::radio($name, $checked, ['value' => $value, 'class' => 'form-check-input'])
                        . ' ' . Html::encode($label) . '</label>';
                }]
            ) ?>
        </div>
    </div>

    <?= $form->field($model, 'purpose')->textarea(['rows' => 2])->label('General Notes / Overall Purpose') ?>

    <hr>
    <h5 class="mb-3">Quotation Attachments <span class="text-muted fw-normal">(record only, not shown on the printed form)</span></h5>

    <?php if (!$model->isNewRecord && !empty($model->attachments)): ?>
        <table class="table table-bordered table-sm mb-3">
            <thead>
                <tr><th>File</th><th>Uploaded By</th><th>Date</th><th>Remarks</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($model->attachments as $att): ?>
                    <tr>
                        <td><?= Html::a(Html::encode($att->original_name), $att->downloadUrl, ['target' => '_blank']) ?></td>
                        <td><?= Html::encode($att->uploadedByStaff->staff_name ?? '-') ?></td>
                        <td><?= Html::encode($att->created_at) ?></td>
                        <td><?= Html::encode($att->remarks) ?></td>
                        <td>
                            <?= Html::a('Remove', ['/pr-attachment/delete', 'id' => $att->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data' => ['method' => 'post', 'confirm' => 'Remove this attachment?'],
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="row g-2 mb-3">
        <div class="col-md-8">
            <label class="form-label">Add Files</label>
            <input type="file" name="attachmentFiles[]" class="form-control" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx">
            <div class="form-text">Uploaded under the Requested By staff shown above.</div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Remarks</label>
            <input type="text" name="attachment_remarks" class="form-control" placeholder="Optional">
        </div>
    </div>

    <hr>
    <h5 class="mb-3">Items</h5>
    <div id="pr-items-list">
        <?php foreach ($items as $i => $item): ?>
            <?= renderPrItemCard($i, $item) ?>
        <?php endforeach; ?>
    </div>

    <button type="button" id="add-item-row" class="btn btn-sm btn-secondary mb-3">+ Add Item</button>

    <div class="pr-action-card" style="max-width: 320px;">
        <div class="pr-action-title mb-1">Total Amount</div>
        <div style="font-size: 22px; font-weight: 600;">
            <span id="pr-grand-total">0.00</span>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
function fillPrDescription(rowIndex, catalogName) {
    const descInput = document.getElementById('pr-description-' + rowIndex);
    if (descInput && !descInput.value.trim()) {
        descInput.value = catalogName;
    }
}

function recalcPrGrandTotal() {
    let total = 0;
    document.querySelectorAll('.pr-item-card').forEach(function (card) {
        const qtyInput = card.querySelector('.pr-item-qty');
        const priceInput = card.querySelector('.pr-item-price');
        const qty = parseFloat(qtyInput ? qtyInput.value : 0) || 0;
        const price = parseFloat(priceInput ? priceInput.value : 0) || 0;
        total += qty * price;
    });
    const el = document.getElementById('pr-grand-total');
    if (el) {
        el.textContent = total.toFixed(2);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = <?= count($items) ?>;

    recalcPrGrandTotal();

    <?php foreach ($items as $i => $item): ?>
        attachCatalogSimilarityCheck('pr-description-<?= $i ?>');
    <?php endforeach; ?>

    function cardHtml(index) {
        return `
            <div class="card mb-3 pr-item-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>New Item</strong>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Catalog Item (optional)</label>
                            <div class="input-group">
                                <input type="text" id="pr-catalog-display-${index}" class="form-control" readonly placeholder="Click Select...">
                                <button type="button" class="btn btn-outline-secondary" onclick="CatalogPicker.open('pr-catalog-id-${index}','pr-catalog-display-${index}', function(item){ fillPrDescription(${index}, item.item_name); })">Select</button>
                                <button type="button" class="btn btn-outline-danger" onclick="clearCatalogSelection('pr-catalog-id-${index}','pr-catalog-display-${index}')" title="Clear selection">&times;</button>
                            </div>
                            <input type="hidden" name="PrItem[${index}][catalog_item_id]" id="pr-catalog-id-${index}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <input type="text" name="PrItem[${index}][item_type]" class="form-control" placeholder="e.g. Size/Code/Colour">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <input type="text" name="PrItem[${index}][description]" id="pr-description-${index}" class="form-control pr-item-description">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-3">
                            <label class="form-label">Specification</label>
                            <input type="text" name="PrItem[${index}][specification]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Qty</label>
                            <input type="number" name="PrItem[${index}][quantity]" class="form-control pr-item-qty" min="1" value="1">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date Needed</label>
                            <input type="date" name="PrItem[${index}][needed_date]" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unit Price</label>
                            <input type="number" name="PrItem[${index}][unit_price]" class="form-control pr-item-price" step="0.01" value="0">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Quotation Ref No.</label>
                            <input type="text" name="PrItem[${index}][quotation_ref_no]" class="form-control">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-8">
                            <label class="form-label">Purpose (this item)</label>
                            <input type="text" name="PrItem[${index}][purpose]" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="PrItem[${index}][remarks]" class="form-control">
                        </div>
                    </div>
                    <input type="hidden" name="PrItem[${index}][id]" value="">
                </div>
            </div>
        `;
    }

    document.getElementById('add-item-row').addEventListener('click', function () {
        const list = document.getElementById('pr-items-list');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = cardHtml(rowIndex);
        list.appendChild(wrapper.firstElementChild);
        attachCatalogSimilarityCheck('pr-description-' + rowIndex);
        rowIndex++;
        recalcPrGrandTotal();
    });

    document.getElementById('pr-items-list').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (btn) {
            btn.closest('.pr-item-card').remove();
            recalcPrGrandTotal();
        }
    });

    document.getElementById('pr-items-list').addEventListener('input', function (e) {
        if (e.target.classList.contains('pr-item-qty') || e.target.classList.contains('pr-item-price')) {
            recalcPrGrandTotal();
        }
    });
});
</script>