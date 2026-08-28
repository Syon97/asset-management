<?php
use yii\helpers\Html;
use app\models\StockIssue;

/** @var app\models\StockIssue $model */
$this->title = 'Stock Issue #' . $model->id;
?>

<h1><?= Html::encode($this->title) ?></h1>

<div class="pr-meta-grid">
    <div class="pr-meta-item">
        <span class="pr-meta-label">Date</span>
        <span class="pr-meta-value"><?= Html::encode($model->issue_date) ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Item</span>
        <span class="pr-meta-value"><?= Html::encode($model->catalogItem->item_name ?? '-') ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Quantity</span>
        <span class="pr-meta-value"><?= $model->quantity ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">From Location</span>
        <span class="pr-meta-value"><?= Html::encode($model->department->name ?? '-') ?></span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Issued To</span>
        <span class="pr-meta-value">
            <?= Html::encode(StockIssue::holderTypeLabels()[$model->holder_type] ?? '-') ?>:
            <?= Html::encode($model->getHolderLabel()) ?>
        </span>
    </div>
    <div class="pr-meta-item">
        <span class="pr-meta-label">Issued By</span>
        <span class="pr-meta-value"><?= Html::encode($model->issuedByStaff->staff_name ?? '-') ?></span>
    </div>
</div>

<?php if ($model->remarks): ?>
    <div class="pr-notes-box">
        <strong>Remarks</strong>
        <p class="mb-0"><?= nl2br(Html::encode($model->remarks)) ?></p>
    </div>
<?php endif; ?>