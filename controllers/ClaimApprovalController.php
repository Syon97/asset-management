<?php

namespace app\controllers;

use Yii;
use app\models\Claim;
use app\models\ClaimStatusHistory;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ClaimApprovalController extends Controller
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
                    throw new ForbiddenHttpException('Only Financer or Admin can review claims.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'approve' => ['POST'],
                    'reject' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Claims ready for human review - "Verified" is currently an
     * automatic pass-through (see ClaimController::actionSubmit), so in
     * practice this is every recently-submitted claim until real
     * business-rule validation exists.
     */
    public function actionIndex()
    {
        $pending = Claim::find()
            ->where(['status' => Claim::STATUS_VERIFIED])
            ->orderBy(['submitted_at' => SORT_ASC])
            ->all();

        return $this->render('index', [
            'pending' => $pending,
        ]);
    }

    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if (!in_array($model->status, [Claim::STATUS_SUBMITTED, Claim::STATUS_VERIFIED], true)) {
            Yii::$app->session->setFlash('warning', 'This claim is not awaiting approval.');
            return $this->redirect(['index']);
        }

        $reviewerStaffId = Yii::$app->user->identity->staff_id;
        $fromStatus = $model->status;

        $model->status = Claim::STATUS_APPROVED;
        $model->approved_by = $reviewerStaffId;
        $model->approved_at = date('Y-m-d H:i:s');
        $model->save(false);

        ClaimStatusHistory::record($model->id, $fromStatus, Claim::STATUS_APPROVED, $reviewerStaffId);

        \app\components\ClaimNotificationService::notifyApproved($model);

        Yii::$app->session->setFlash('success', $model->claim_no . ' approved.');
        return $this->redirect(['index']);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);

        if (!in_array($model->status, [Claim::STATUS_SUBMITTED, Claim::STATUS_VERIFIED], true)) {
            Yii::$app->session->setFlash('warning', 'This claim is not awaiting approval.');
            return $this->redirect(['index']);
        }

        $reason = trim(Yii::$app->request->post('rejection_reason', ''));
        if ($reason === '') {
            Yii::$app->session->setFlash('error', 'Please provide a reason for rejecting this claim.');
            return $this->redirect(['index']);
        }

        $reviewerStaffId = Yii::$app->user->identity->staff_id;
        $fromStatus = $model->status;

        $model->status = Claim::STATUS_REJECTED;
        $model->rejected_by = $reviewerStaffId;
        $model->rejected_at = date('Y-m-d H:i:s');
        $model->rejection_reason = $reason;
        $model->save(false);

        ClaimStatusHistory::record($model->id, $fromStatus, Claim::STATUS_REJECTED, $reviewerStaffId, $reason);

        \app\components\ClaimNotificationService::notifyRejected($model);

        Yii::$app->session->setFlash('success', $model->claim_no . ' rejected.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = Claim::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested claim does not exist.');
    }
}