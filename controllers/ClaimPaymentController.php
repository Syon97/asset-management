<?php

namespace app\controllers;

use Yii;
use app\models\Claim;
use app\models\ClaimStatusHistory;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ClaimPaymentController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function ($rule, $action) {
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->canApproveClaims();
                        },
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException('Only Financer or Admin can mark claims as paid.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'mark-paid' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Approved-but-unpaid claims, grouped by payment route. The route
     * isn't its own concept to manage - it's just derived from the
     * claim's own category (is_mileage_type), same as everywhere else in
     * this system that avoided inventing a parallel structure for
     * something already implied by existing data.
     */
    public function actionIndex()
    {
        $approved = Claim::find()
            ->with('claimCategory', 'staff')
            ->where(['status' => Claim::STATUS_APPROVED])
            ->orderBy(['approved_at' => SORT_ASC])
            ->all();

        $payrollClaims = [];
        $mileageClaims = [];
        foreach ($approved as $claim) {
            if ($claim->claimCategory && $claim->claimCategory->is_mileage_type) {
                $mileageClaims[] = $claim;
            } else {
                $payrollClaims[] = $claim;
            }
        }

        return $this->render('index', [
            'payrollClaims' => $payrollClaims,
            'mileageClaims' => $mileageClaims,
        ]);
    }

    public function actionMarkPaid()
    {
        $ids = Yii::$app->request->post('claim_ids', []);
        if (empty($ids)) {
            Yii::$app->session->setFlash('error', 'Please select at least one claim.');
            return $this->redirect(['index']);
        }

        $staffId = Yii::$app->user->identity->staff_id;
        $marked = 0;

        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach (Claim::findAll(['id' => $ids]) as $claim) {
                if ($claim->status !== Claim::STATUS_APPROVED) {
                    continue;
                }

                $claim->status = Claim::STATUS_PAID;
                $claim->paid_by = $staffId;
                $claim->paid_at = date('Y-m-d H:i:s');
                $claim->save(false);

                ClaimStatusHistory::record($claim->id, Claim::STATUS_APPROVED, Claim::STATUS_PAID, $staffId);
                \app\components\ClaimNotificationService::notifyPaid($claim);
                $marked++;
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', "{$marked} claim(s) marked as paid.");
        return $this->redirect(['index']);
    }
}