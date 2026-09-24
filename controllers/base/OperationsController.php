<?php

namespace app\controllers\base;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

/**
 * Base for every controller covering master data, procurement, and asset
 * management. Per the agreed permission matrix, Purchaser/Financer/Admin
 * all have identical access to everything AssetTrack does - the three
 * roles only get distinguished once the Claims system exists (Financer
 * approves claims, Admin configures them). Generic users are blocked
 * entirely here; their only access is their own assigned assets, handled
 * separately in HardwareAssetController since it needs per-row filtering
 * rather than a blanket allow/deny.
 *
 * Guests get redirected to login automatically (AccessControl's default
 * behavior); a logged-in Generic user gets a 403.
 */
class OperationsController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->canAccessOperations();
                        },
                    ],
                ],
            ],
        ]);
    }
}