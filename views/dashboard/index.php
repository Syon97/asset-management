<?php
/** @var yii\web\View $this */
/** @var int $totalHardwareAssets */
/** @var int $pendingPrApprovals */
/** @var int $lowStockItems */
/** @var int $warrantyExpiringSoon */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Dashboard';
?>
<div class="dashboard-index">
    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Asset Management System — overview will appear here.</p>

    <div class="row mt-4">
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
    </div>
</div>