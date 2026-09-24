<?php
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\bootstrap5\ActiveForm;
use app\models\Claim;
use app\models\ClaimCategory;
use app\models\MileageRate;

/** @var app\models\Claim $model */
/** @var app\models\ClaimItem[] $items */
/** @var app\models\ClaimCategory[] $categories */
/** @var array $remainingLimits */

$mileageRates = [
    MileageRate::VEHICLE_CAR => MileageRate::currentRateFor(MileageRate::VEHICLE_CAR),
    MileageRate::VEHICLE_MOTORCYCLE => MileageRate::currentRateFor(MileageRate::VEHICLE_MOTORCYCLE),
];

if (!function_exists('renderClaimItemCard')) {
function renderClaimItemCard($i, $item)
{
    $itemDate = Html::encode($item->item_date);
    $particular = Html::encode($item->particular);
    $location = Html::encode($item->location_company);
    $purpose = Html::encode($item->purpose);
    $amount = Html::encode($item->amount ?: 0);
    $distance = Html::encode($item->distance_km);
    $vehicleCar = $item->vehicle_type === 'car' ? 'selected' : '';
    $vehicleMoto = $item->vehicle_type === 'motorcycle' ? 'selected' : '';
    $hasReceipt = $item->has_receipt ? 'checked' : '';
    $remarks = Html::encode($item->remarks);
    $id = Html::encode($item->id);

    $existingReceipt = '';
    if ($item->getReceiptUrl()) {
        $existingReceipt = '<div class="small mt-1"><a href="' . Html::encode($item->getReceiptUrl()) . '" target="_blank">Current receipt</a></div>';
    }

    return <<<HTML
    <div class="card mb-3 claim-item-card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>Item</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>
            </div>
            <div class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Date</label>
                    <input type="date" name="ClaimItem[{$i}][item_date]" value="{$itemDate}" class="form-control">
                </div>
                <div class="col-md-9 claim-field-medical">
                    <label class="form-label">Particular</label>
                    <input type="text" name="ClaimItem[{$i}][particular]" value="{$particular}" class="form-control">
                </div>
                <div class="col-md-9 claim-field-mileage" style="display:none;">
                    <label class="form-label">Destination / Purpose</label>
                    <input type="text" name="ClaimItem[{$i}][purpose]" value="{$purpose}" class="form-control claim-mileage-purpose">
                </div>
            </div>
            <div class="row g-2 mt-1 claim-field-medical">
                <div class="col-md-6">
                    <label class="form-label">Location &amp; Company Name</label>
                    <input type="text" name="ClaimItem[{$i}][location_company]" value="{$location}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="ClaimItem[{$i}][purpose]" value="{$purpose}" class="form-control claim-medical-purpose">
                </div>
            </div>
            <div class="row g-2 mt-1 claim-field-mileage" style="display:none;">
                <div class="col-md-4">
                    <label class="form-label">Vehicle Type</label>
                    <select name="ClaimItem[{$i}][vehicle_type]" class="form-select claim-vehicle-type">
                        <option value="">-- Select --</option>
                        <option value="car" {$vehicleCar}>Car</option>
                        <option value="motorcycle" {$vehicleMoto}>Motorcycle</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Distance (KM)</label>
                    <input type="number" step="0.1" name="ClaimItem[{$i}][distance_km]" value="{$distance}" class="form-control claim-distance">
                </div>
            </div>
            <div class="row g-2 mt-1">
                <div class="col-md-3">
                    <label class="form-label">Amount (RM)</label>
                    <input type="number" step="0.01" name="ClaimItem[{$i}][amount]" value="{$amount}" class="form-control claim-item-amount">
                </div>
                <div class="col-md-5 claim-field-medical">
                    <label class="form-label">Receipt</label>
                    <input type="file" name="ClaimItem[{$i}][receiptFile]" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                    {$existingReceipt}
                </div>
                <div class="col-md-4">
                    <label class="form-label">Remarks</label>
                    <input type="text" name="ClaimItem[{$i}][remarks]" value="{$remarks}" class="form-control">
                </div>
            </div>
            <input type="hidden" name="ClaimItem[{$i}][id]" value="{$id}">
            <input type="hidden" name="ClaimItem[{$i}][has_receipt]" value="0">
        </div>
    </div>
HTML;
}
}
?>

<div class="claim-form">
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <div class="row">
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <select id="claim-category-select" name="Claim[claim_category_id]" class="form-select" onchange="onClaimCategoryChange()">
                <option value="">-- Select Category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category->id ?>" data-mileage="<?= $category->is_mileage_type ? '1' : '0' ?>" <?= (int) $model->claim_category_id === (int) $category->id ? 'selected' : '' ?>>
                        <?= Html::encode($category->name) ?><?= $category->hasLimit() ? ' (limit RM ' . number_format($category->annual_limit_amount, 2) . '/yr)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($model->hasErrors('claim_category_id')): ?>
                <div class="text-danger small mt-1"><?= Html::encode($model->getFirstError('claim_category_id')) ?></div>
            <?php endif; ?>
            <div id="claim-limit-info" class="alert py-2 px-3 mt-2 mb-0" style="display:none;"></div>
        </div>
    </div>

    <?= $form->field($model, 'remarks')->textarea(['rows' => 2])->label('Notes (optional)') ?>

    <hr>
    <h5 class="mb-3">Items</h5>
    <div id="claim-items-list">
        <?php foreach ($items as $i => $item): ?>
            <?= renderClaimItemCard($i, $item) ?>
        <?php endforeach; ?>
    </div>

    <button type="button" id="add-item-row" class="btn btn-sm btn-secondary mb-3">+ Add Item</button>

    <div class="pr-action-card" style="max-width: 320px;">
        <div class="pr-action-title mb-1">Total Amount (RM)</div>
        <div style="font-size: 22px; font-weight: 600;">
            <span id="claim-grand-total">0.00</span>
        </div>
    </div>

    <div class="form-group mt-3">
        <?= Html::submitButton('Save as Draft', ['class' => 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<script>
const claimMileageRates = <?= json_encode($mileageRates) ?>;
const claimRemainingLimits = <?= json_encode($remainingLimits) ?>;

function updateClaimLimitInfo() {
    const select = document.getElementById('claim-category-select');
    const infoEl = document.getElementById('claim-limit-info');
    const limitInfo = select.value ? claimRemainingLimits[select.value] : null;

    if (!select.value) {
        infoEl.style.display = 'none';
        return;
    }

    if (!limitInfo) {
        infoEl.className = 'alert alert-secondary py-2 px-3 mt-2 mb-0';
        infoEl.style.display = '';
        infoEl.innerHTML = 'No annual limit for this category.';
        return;
    }

    const thisClaimTotal = parseFloat(document.getElementById('claim-grand-total').textContent) || 0;
    const projected = limitInfo.used + thisClaimTotal;
    const over = projected - limitInfo.limit;

    infoEl.style.display = '';

    if (over > 0) {
        infoEl.className = 'alert alert-danger py-2 px-3 mt-2 mb-0';
        infoEl.innerHTML = `<strong>⚠ This claim will be auto-rejected.</strong><br>`
            + `RM ${limitInfo.used.toFixed(2)} has been approved this year. This claim adds RM ${thisClaimTotal.toFixed(2)}. `
            + `Your limit is RM ${limitInfo.limit.toFixed(2)}. You are RM ${over.toFixed(2)} over.`;
    } else if (limitInfo.limit > 0 && projected / limitInfo.limit >= 0.8) {
        infoEl.className = 'alert alert-warning py-2 px-3 mt-2 mb-0';
        infoEl.innerHTML = `RM ${limitInfo.used.toFixed(2)} has been approved this year. This claim adds RM ${thisClaimTotal.toFixed(2)}. `
            + `Your remaining balance will be RM ${(limitInfo.limit - projected).toFixed(2)}.`;
    } else {
        infoEl.className = 'alert alert-success py-2 px-3 mt-2 mb-0';
        infoEl.innerHTML = `RM ${limitInfo.used.toFixed(2)} has been approved this year. This claim adds RM ${thisClaimTotal.toFixed(2)}. `
            + `Your remaining balance will be RM ${(limitInfo.limit - projected).toFixed(2)}.`;
    }
}

function onClaimCategoryChange() {
    const select = document.getElementById('claim-category-select');
    const opt = select.options[select.selectedIndex];
    const isMileage = opt && opt.dataset.mileage === '1';

    document.querySelectorAll('.claim-field-medical').forEach(el => {
        el.style.display = isMileage ? 'none' : '';
        el.querySelectorAll('input, select').forEach(input => { input.disabled = isMileage; });
    });
    document.querySelectorAll('.claim-field-mileage').forEach(el => {
        el.style.display = isMileage ? '' : 'none';
        el.querySelectorAll('input, select').forEach(input => { input.disabled = !isMileage; });
    });

    updateClaimLimitInfo();

    if (isMileage) {
        document.querySelectorAll('.claim-item-card').forEach(recalcMileageAmount);
    }
}

function recalcMileageAmount(card) {
    const vehicleSelect = card.querySelector('.claim-vehicle-type');
    const distanceInput = card.querySelector('.claim-distance');
    const amountInput = card.querySelector('.claim-item-amount');
    if (!vehicleSelect || !distanceInput || !amountInput) {
        return;
    }
    const rate = claimMileageRates[vehicleSelect.value] || 0;
    const distance = parseFloat(distanceInput.value) || 0;
    amountInput.value = (rate * distance).toFixed(2);
    recalcClaimGrandTotal();
}

function recalcClaimGrandTotal() {
    let total = 0;
    document.querySelectorAll('.claim-item-amount').forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    const el = document.getElementById('claim-grand-total');
    if (el) {
        el.textContent = total.toFixed(2);
    }
    updateClaimLimitInfo();
}

document.addEventListener('DOMContentLoaded', function () {
    let rowIndex = <?= count($items) ?>;

    onClaimCategoryChange();
    recalcClaimGrandTotal();

    function cardHtml(index) {
        return `
            <div class="card mb-3 claim-item-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong>New Item</strong>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"><i class="bi bi-trash"></i></button>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="ClaimItem[${index}][item_date]" class="form-control">
                        </div>
                        <div class="col-md-9 claim-field-medical">
                            <label class="form-label">Particular</label>
                            <input type="text" name="ClaimItem[${index}][particular]" class="form-control">
                        </div>
                        <div class="col-md-9 claim-field-mileage" style="display:none;">
                            <label class="form-label">Destination / Purpose</label>
                            <input type="text" name="ClaimItem[${index}][purpose]" class="form-control claim-mileage-purpose">
                        </div>
                    </div>
                    <div class="row g-2 mt-1 claim-field-medical">
                        <div class="col-md-6">
                            <label class="form-label">Location &amp; Company Name</label>
                            <input type="text" name="ClaimItem[${index}][location_company]" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Purpose</label>
                            <input type="text" name="ClaimItem[${index}][purpose]" class="form-control claim-medical-purpose">
                        </div>
                    </div>
                    <div class="row g-2 mt-1 claim-field-mileage" style="display:none;">
                        <div class="col-md-4">
                            <label class="form-label">Vehicle Type</label>
                            <select name="ClaimItem[${index}][vehicle_type]" class="form-select claim-vehicle-type">
                                <option value="">-- Select --</option>
                                <option value="car">Car</option>
                                <option value="motorcycle">Motorcycle</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Distance (KM)</label>
                            <input type="number" step="0.1" name="ClaimItem[${index}][distance_km]" class="form-control claim-distance">
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-3">
                            <label class="form-label">Amount (RM)</label>
                            <input type="number" step="0.01" name="ClaimItem[${index}][amount]" value="0" class="form-control claim-item-amount">
                        </div>
                        <div class="col-md-5 claim-field-medical">
                            <label class="form-label">Receipt</label>
                            <input type="file" name="ClaimItem[${index}][receiptFile]" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="ClaimItem[${index}][remarks]" class="form-control">
                        </div>
                    </div>
                    <input type="hidden" name="ClaimItem[${index}][has_receipt]" value="0">
                </div>
            </div>
        `;
    }

    document.getElementById('add-item-row').addEventListener('click', function () {
        const list = document.getElementById('claim-items-list');
        const wrapper = document.createElement('div');
        wrapper.innerHTML = cardHtml(rowIndex);
        list.appendChild(wrapper.firstElementChild);
        rowIndex++;
        onClaimCategoryChange();
    });

    document.getElementById('claim-items-list').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-row');
        if (btn) {
            btn.closest('.claim-item-card').remove();
            recalcClaimGrandTotal();
        }
    });

    document.getElementById('claim-items-list').addEventListener('input', function (e) {
        if (e.target.classList.contains('claim-distance')) {
            recalcMileageAmount(e.target.closest('.claim-item-card'));
        } else if (e.target.classList.contains('claim-item-amount')) {
            recalcClaimGrandTotal();
        }
    });

    document.getElementById('claim-items-list').addEventListener('change', function (e) {
        if (e.target.classList.contains('claim-vehicle-type')) {
            recalcMileageAmount(e.target.closest('.claim-item-card'));
        }
    });
});
</script>