<?php
/** @var yii\web\View $this */
/** @var int $totalHardwareAssets */
/** @var int $pendingPrApprovals */
/** @var int $lowStockItems */
/** @var int $warrantyExpiringSoon */
/** @var int|null $pendingClaimApprovals */
/** @var app\models\Claim[] $pendingClaimList */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
?>
<div class="dashboard-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Asset Management System — overview will appear here.</p>

    <div class="row mt-4 gy-4">
        <div class="col-md-3">
            <a href="<?= Url::to(['/hardware-asset/index']) ?>" class="text-decoration-none text-reset">
                <div class="card card-stat">
                    <div class="card-body">
                        <div class="stat-label">Total Hardware Assets</div>
                        <div class="stat-value"><?= $totalHardwareAssets ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= Url::to(['/purchase-requisition/index']) ?>" class="text-decoration-none text-reset">
                <div class="card card-stat accent-amber">
                    <div class="card-body">
                        <div class="stat-label">Pending PR Approvals</div>
                        <div class="stat-value"><?= $pendingPrApprovals ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= Url::to(['/stock/index']) ?>" class="text-decoration-none text-reset">
                <div class="card card-stat accent-danger">
                    <div class="card-body">
                        <div class="stat-label">Low Stock Items</div>
                        <div class="stat-value"><?= $lowStockItems ?></div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="<?= Url::to(['/hardware-asset/index']) ?>" class="text-decoration-none text-reset">
                <div class="card card-stat accent-success">
                    <div class="card-body">
                        <div class="stat-label">Warranty Expiring Soon</div>
                        <div class="stat-value"><?= $warrantyExpiringSoon ?></div>
                    </div>
                </div>
            </a>
        </div>
        <?php if ($pendingClaimApprovals !== null): ?>
        <div class="col-md-3">
            <div class="card card-stat accent-amber h-100">
                <div class="card-body">
                    <a href="<?= Url::to(['/claim-approval/index']) ?>" class="text-decoration-none text-reset">
                        <div class="stat-label">Pending Claim Approvals</div>
                        <div class="stat-value"><?= $pendingClaimApprovals ?></div>
                    </a>
                    <?php if (!empty($pendingClaimList)): ?>
                        <ul class="list-unstyled small mt-2 mb-0">
                            <?php foreach ($pendingClaimList as $claim): ?>
                                <li><?= Html::a(Html::encode($claim->claim_no), ['/claim/view', 'id' => $claim->id]) ?> <span class="text-muted">(<?= Html::encode($claim->staff->staff_name ?? '-') ?>, RM <?= number_format($claim->total_amount, 2) ?>)</span></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>