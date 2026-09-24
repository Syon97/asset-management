<?php
use yii\helpers\Html;
use app\models\MileageRate;

/** @var app\models\MileageRate[] $rates */

$this->title = 'Mileage Rates';
$this->params['breadcrumbs'][] = $this->title;

$labels = MileageRate::vehicleTypeLabels();
?>
<div class="mileage-rate-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Used to auto-calculate the amount for mileage claims: distance (km) &times; rate.</p>

    <table class="table table-bordered" style="max-width: 500px;">
        <thead>
            <tr><th>Vehicle Type</th><th>Rate per KM (RM)</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($rates as $rate): ?>
                <tr>
                    <td><?= Html::encode($labels[$rate->vehicle_type] ?? $rate->vehicle_type) ?></td>
                    <td colspan="2">
                        <?= Html::beginForm(['update-rate', 'id' => $rate->id], 'post', ['class' => 'd-flex gap-2 align-items-center']) ?>
                            <input type="number" name="rate_per_km" step="0.01" min="0" value="<?= Html::encode($rate->rate_per_km) ?>" class="form-control" style="max-width:120px;">
                            <?= Html::submitButton('Save', ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= Html::endForm() ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>