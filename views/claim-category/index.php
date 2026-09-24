<?php
use yii\helpers\Html;
use app\models\ClaimCategory;

/** @var app\models\ClaimCategory[] $categories */

$this->title = 'Claim Categories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="claim-category-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Categories, their annual claim limits, and whether they use the mileage-style fields.</p>

    <p><?= Html::a('Add Category', ['create'], ['class' => 'btn btn-primary']) ?></p>

    <table class="table table-bordered">
        <thead>
            <tr><th>Name</th><th>Annual Limit</th><th>Mileage Type</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $category): ?>
                <tr>
                    <td><?= Html::encode($category->name) ?></td>
                    <td><?= $category->hasLimit() ? 'RM ' . number_format($category->annual_limit_amount, 2) . ' / year' : 'No limit' ?></td>
                    <td><?= $category->is_mileage_type ? 'Yes' : 'No' ?></td>
                    <td>
                        <span class="badge-status <?= $category->status === ClaimCategory::STATUS_ACTIVE ? 'status-approved' : 'status-draft' ?>">
                            <?= strtoupper($category->status) ?>
                        </span>
                    </td>
                    <td>
                        <?= Html::a('Edit', ['update', 'id' => $category->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= Html::a('Delete', ['delete', 'id' => $category->id], [
                            'class' => 'btn btn-sm btn-outline-danger',
                            'data' => ['method' => 'post', 'confirm' => 'Delete this claim category?'],
                        ]) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</div>