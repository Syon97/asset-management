<?php

namespace app\controllers;

use Yii;
use app\models\HardwareAsset;
use app\models\PurchaseRequisition;
use app\models\Stock;
use app\models\Claim;
use yii\filters\AccessControl;
use yii\web\Controller;

class DashboardController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['allow' => true, 'roles' => ['@']],
                ],
            ],
        ];
    }

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
        // Nothing here is actionable by a Generic user (they can't touch
        // procurement/stock/master data) - send them to their own assets
        // instead, which is the one thing actually relevant to them until
        // Claims exists.
        if (!Yii::$app->user->identity->canAccessOperations()) {
            return $this->redirect(['/hardware-asset/index']);
        }

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

        $pendingClaimApprovals = null;
        $pendingClaimList = [];
        if (Yii::$app->user->identity->canApproveClaims()) {
            $pendingClaimApprovals = Claim::pendingApprovalCount();
            $pendingClaimList = Claim::find()
                ->where(['status' => Claim::STATUS_VERIFIED])
                ->orderBy(['submitted_at' => SORT_ASC])
                ->limit(5)
                ->all();
        }

        return $this->render('index', [
            'totalHardwareAssets' => $totalHardwareAssets,
            'pendingPrApprovals' => $pendingPrApprovals,
            'lowStockItems' => $lowStockItems,
            'warrantyExpiringSoon' => $warrantyExpiringSoon,
            'pendingClaimApprovals' => $pendingClaimApprovals,
            'pendingClaimList' => $pendingClaimList,
        ]);
    }
}