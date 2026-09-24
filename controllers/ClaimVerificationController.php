<?php

namespace app\controllers;

use Yii;
use app\models\Claim;
use app\models\ClaimStatusHistory;
use app\components\ClaimVerificationService;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ClaimVerificationController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'verify' => ['POST'],
                    'reject' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Claims currently at Submitted status that resolve to the logged-in
     * user as verifier - either directly (their department's configured
     * verifier), via GM escalation, or via the Admin catch-all when no
     * verifier is configured at all. Resolution happens per-claim in PHP
     * since it isn't a simple column filter.
     */
    public function actionIndex()
    {
        $myStaffId = Yii::$app->user->identity->staff_id;
        $isAdmin = Yii::$app->user->identity->isAdmin();

        $submitted = Claim::find()
            ->where(['status' => Claim::STATUS_SUBMITTED])
            ->with('staff', 'claimCategory')
            ->orderBy(['submitted_at' => SORT_ASC])
            ->all();

        $myQueue = [];
        foreach ($submitted as $claim) {
            $resolved = ClaimVerificationService::resolveVerifierStaffId($claim);
            if ($resolved === (int) $myStaffId) {
                $myQueue[] = $claim;
            } elseif ($resolved === null && $isAdmin) {
                $myQueue[] = $claim;
            }
        }

        return $this->render('index', [
            'claims' => $myQueue,
        ]);
    }

    public function actionVerify($id)
    {
        $model = $this->findModel($id);
        $this->checkIsResolvedVerifier($model);

        if ($model->status !== Claim::STATUS_SUBMITTED) {
            Yii::$app->session->setFlash('warning', 'This claim is not awaiting verification.');
            return $this->redirect(['index']);
        }

        $verifierStaffId = Yii::$app->user->identity->staff_id;

        $model->status = Claim::STATUS_VERIFIED;
        $model->verified_at = date('Y-m-d H:i:s');
        $model->save(false);

        ClaimStatusHistory::record($model->id, Claim::STATUS_SUBMITTED, Claim::STATUS_VERIFIED, $verifierStaffId);

        \app\components\ClaimNotificationService::notifyVerified($model);

        Yii::$app->session->setFlash('success', $model->claim_no . ' verified and sent to Finance for approval.');
        return $this->redirect(['index']);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);
        $this->checkIsResolvedVerifier($model);

        if ($model->status !== Claim::STATUS_SUBMITTED) {
            Yii::$app->session->setFlash('warning', 'This claim is not awaiting verification.');
            return $this->redirect(['index']);
        }

        $reason = trim(Yii::$app->request->post('rejection_reason', ''));
        if ($reason === '') {
            Yii::$app->session->setFlash('error', 'Please provide a reason for rejecting this claim.');
            return $this->redirect(['index']);
        }

        $verifierStaffId = Yii::$app->user->identity->staff_id;

        $model->status = Claim::STATUS_REJECTED;
        $model->rejected_by = $verifierStaffId;
        $model->rejected_at = date('Y-m-d H:i:s');
        $model->rejection_reason = $reason;
        $model->save(false);

        ClaimStatusHistory::record($model->id, Claim::STATUS_SUBMITTED, Claim::STATUS_REJECTED, $verifierStaffId, $reason);

        \app\components\ClaimNotificationService::notifyRejected($model);

        Yii::$app->session->setFlash('success', $model->claim_no . ' rejected.');
        return $this->redirect(['index']);
    }

    /**
     * Never trust that a claim showing up in the queue view alone is
     * authorization - re-resolve and re-check on the actual mutating
     * action, same defense-in-depth principle used everywhere else in
     * this system.
     */
    private function checkIsResolvedVerifier(Claim $model)
    {
        $myStaffId = Yii::$app->user->identity->staff_id;
        $isAdmin = Yii::$app->user->identity->isAdmin();

        $resolved = ClaimVerificationService::resolveVerifierStaffId($model);
        $authorized = ($resolved === (int) $myStaffId) || ($resolved === null && $isAdmin);

        if (!$authorized) {
            throw new ForbiddenHttpException('You are not the resolved verifier for this claim.');
        }
    }

    protected function findModel($id)
    {
        if (($model = Claim::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested claim does not exist.');
    }
}