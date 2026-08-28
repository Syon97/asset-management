<?php

namespace app\controllers;

use Yii;
use app\models\HardwareAsset;
use app\models\PurchaseRequisition;
use app\models\Stock;
use yii\web\Controller;

class DashboardController extends Controller
{
    /**
     * No per-item reorder level exists yet in the schema, so "low stock"
     * uses a flat threshold as a starting point. Worth adding a proper
     * reorder_level field to Item Catalog later so this can be tuned
     * per item instead of one number for everything.
     */
    const LOW_STOCK_THRESHOLD = 5;

    const WARRANTY_WARNING_DAYS = 30;

    public function actionIndex()
    {
        $totalHardwareAssets = HardwareAsset::find()
            ->where(['status' => HardwareAsset::STATUS_ACTIVE])
            ->count();

        $pendingPrApprovals = PurchaseRequisition::find()
            ->where(['status' => [
                PurchaseRequisition::STATUS_SUBMITTED,
                PurchaseRequisition::STATUS_VERIFIED,
                PurchaseRequisition::STATUS_REVIEWED,
                PurchaseRequisition::STATUS_RECEIVED,
            ]])
            ->count();

        $lowStockItems = Stock::find()
            ->where(['<=', 'quantity_on_hand', self::LOW_STOCK_THRESHOLD])
            ->count();

        $warrantyExpiringSoon = HardwareAsset::find()
            ->where(['status' => HardwareAsset::STATUS_ACTIVE])
            ->andWhere(['between', 'warranty_end_date',
                date('Y-m-d'),
                date('Y-m-d', strtotime('+' . self::WARRANTY_WARNING_DAYS . ' days')),
            ])
            ->count();

        return $this->render('index', [
            'totalHardwareAssets' => $totalHardwareAssets,
            'pendingPrApprovals' => $pendingPrApprovals,
            'lowStockItems' => $lowStockItems,
            'warrantyExpiringSoon' => $warrantyExpiringSoon,
        ]);
    }
}