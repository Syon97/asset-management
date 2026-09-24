<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var app\models\ClaimStatusHistorySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array $statusLabels */

$this->title = 'Claims Audit Trail';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="claim-audit-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Every status change across every claim, in one place, for compliance and lookup purposes.</p>

    <div class="pr-action-card" style="max-width: 100%;">
        <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <div class="col-md-3">
                <label class="form-label">Claim No.</label>
                <?= Html::textInput('claimNo', $searchModel->claimNo, ['class' => 'form-control', 'placeholder' => 'e.g. CLM-2026-0007']) ?>
            </div>
            <div class="col-md-3">
                <label class="form-label">Changed To</label>
                <select name="toStatus" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($statusLabels as $val => $label): ?>
                        <option value="<?= $val ?>" <?= $searchModel->toStatus === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <?= Html::submitButton('Apply Filters', ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('Clear', ['index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
        <?= Html::endForm() ?>
    </div>

    <?php Pjax::begin(); ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            [
                'attribute' => 'changed_at',
                'label' => 'When',
            ],
            [
                'label' => 'Claim',
                'value' => function ($model) {
                    return Html::a(Html::encode($model->claim->claim_no ?? '-'), ['/claim/view', 'id' => $model->claim_id]);
                },
                'format' => 'raw',
            ],
            [
                'label' => 'Transition',
                'value' => function ($model) {
                    return ($model->from_status ? strtoupper($model->from_status) . ' \u2192 ' : '') . strtoupper($model->to_status);
                },
            ],
            [
                'label' => 'By',
                'value' => function ($model) {
                    return $model->changedByStaff->staff_name ?? '-';
                },
            ],
            [
                'attribute' => 'note',
                'label' => 'Note',
            ],
        ],
    ]); ?>
    <?php Pjax::end(); ?>

</div>