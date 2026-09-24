<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\Claim;

/** @var app\models\ClaimReportSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $summary */
/** @var app\models\ClaimCategory[] $categories */

$this->title = 'Claims Report';
$this->params['breadcrumbs'][] = $this->title;

$statusLabels = Claim::statusLabels();
$currentYear = (int) date('Y');
?>
<div class="claim-report-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="pr-action-card" style="max-width: 100%;">
        <div class="pr-action-title">Filters</div>
        <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <div class="col-md-3">
                <label class="form-label">Employee</label>
                <div class="input-group">
                    <input type="text" id="report-staff-display" class="form-control" readonly placeholder="All employees">
                    <button type="button" class="btn btn-outline-secondary" onclick="StaffPicker.open('report-staff-id','report-staff-display')">Select</button>
                </div>
                <input type="hidden" name="staffId" id="report-staff-id" value="<?= Html::encode($searchModel->staffId) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Category</label>
                <select name="categoryId" class="form-select">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat->id ?>" <?= (int) $searchModel->categoryId === (int) $cat->id ? 'selected' : '' ?>><?= Html::encode($cat->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Month</label>
                <select name="month" class="form-select">
                    <option value="">All</option>
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m ?>" <?= (int) $searchModel->month === $m ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Year</label>
                <select name="year" class="form-select">
                    <option value="">All</option>
                    <?php for ($y = $currentYear; $y >= $currentYear - 4; $y--): ?>
                        <option value="<?= $y ?>" <?= (int) $searchModel->year === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($statusLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $searchModel->status === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-12 mt-2">
                <?= Html::submitButton('Apply Filters', ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('Clear', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
        <?= Html::endForm() ?>
    </div>

    <div class="pr-action-row">
        <div class="pr-action-card">
            <div class="pr-action-title">By Category</div>
            <table class="table table-sm mb-0">
                <?php foreach ($summary['byCategory'] as $row): ?>
                    <tr>
                        <td><?= Html::encode($row['category_name']) ?></td>
                        <td class="text-end"><?= $row['cnt'] ?> claim(s)</td>
                        <td class="text-end">RM <?= number_format($row['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($summary['byCategory'])): ?>
                    <tr><td class="text-muted">No data for this filter.</td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="pr-action-card">
            <div class="pr-action-title">By Status</div>
            <table class="table table-sm mb-0">
                <?php foreach ($summary['byStatus'] as $row): ?>
                    <tr>
                        <td><?= strtoupper($row['status']) ?></td>
                        <td class="text-end"><?= $row['cnt'] ?> claim(s)</td>
                        <td class="text-end">RM <?= number_format($row['total'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($summary['byStatus'])): ?>
                    <tr><td class="text-muted">No data for this filter.</td></tr>
                <?php endif; ?>
            </table>
        </div>
        <div class="pr-action-card">
            <div class="pr-action-title">Overall</div>
            <div style="font-size: 26px; font-weight: 600;"><?= $summary['grandCount'] ?> claims</div>
            <div class="text-muted">RM <?= number_format($summary['grandTotal'], 2) ?> total</div>
        </div>
    </div>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'claim_no',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->claim_no), ['/claim/view', 'id' => $model->id]);
                },
                'format' => 'raw',
            ],
            [
                'label' => 'Employee',
                'value' => function ($model) {
                    return $model->staff->staff_name ?? '-';
                },
            ],
            [
                'label' => 'Category',
                'value' => function ($model) {
                    return $model->claimCategory->name ?? '-';
                },
            ],
            [
                'attribute' => 'total_amount',
                'label' => 'Amount (RM)',
                'value' => function ($model) {
                    return number_format($model->total_amount, 2);
                },
            ],
            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    return '<span class="badge-status status-draft">' . strtoupper($model->status) . '</span>';
                },
            ],
            [
                'attribute' => 'created_at',
                'label' => 'Created',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>