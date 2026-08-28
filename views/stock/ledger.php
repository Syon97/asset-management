<?php
use yii\helpers\Html;
use app\models\StockLedger;

/** @var app\models\ItemCatalog $catalogItem */
/** @var app\models\Department $department */
/** @var app\models\StockLedger[] $entries */

$this->title = $catalogItem->item_name . ' @ ' . $department->name;
$this->params['breadcrumbs'][] = ['label' => 'Stock', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'History';
?>
<div class="stock-ledger">

    <h1><?= Html::encode($this->title) ?></h1>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th><th>Movement</th><th>Qty</th><th>Reference</th><th>By</th><th>Notes</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($entries)): ?>
                <tr><td colspan="6" class="text-center text-muted">No movement recorded yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($entries as $entry): ?>
                <tr>
                    <td><?= Html::encode($entry->created_at) ?></td>
                    <td>
                        <span class="badge-status <?= $entry->movement_type === StockLedger::MOVEMENT_IN ? 'status-approved' : 'status-rejected' ?>">
                            <?= strtoupper($entry->movement_type) ?>
                        </span>
                    </td>
                    <td><?= $entry->quantity ?></td>
                    <td><?= strtoupper($entry->reference_type) ?> #<?= $entry->reference_id ?></td>
                    <td><?= Html::encode($entry->staff->staff_name ?? '-') ?></td>
                    <td><?= Html::encode($entry->notes) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>