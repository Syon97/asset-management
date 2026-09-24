<?php

namespace app\controllers;

use Yii;
use app\models\Claim;
use app\models\ClaimItem;
use app\models\ClaimCategory;
use app\models\ClaimStatusHistory;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\UploadedFile;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ClaimController extends Controller
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
                    'submit' => ['POST'],
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Always scoped to the logged-in user's own claims - reviewing other
     * people's claims is a separate screen (Phase 3), not this index.
     */
    public function actionIndex()
    {
        $myStaffId = Yii::$app->user->identity->staff_id;

        $claims = Claim::find()
            ->where(['staff_id' => $myStaffId])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'claims' => $claims,
        ]);
    }

    public function actionCreate()
    {
        $model = new Claim();
        $model->staff_id = Yii::$app->user->identity->staff_id;

        $items = [new ClaimItem()];

        if ($this->loadAndSave($model, $items)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'items' => $items,
            'categories' => $this->activeCategories(),
            'remainingLimits' => $this->remainingLimitsFor($model->staff_id, null),
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnClaim($model);

        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('warning', 'This claim can no longer be edited.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $items = $model->items;
        if (empty($items)) {
            $items = [new ClaimItem()];
        }

        if ($this->loadAndSave($model, $items)) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
            'items' => $items,
            'categories' => $this->activeCategories(),
            'remainingLimits' => $this->remainingLimitsFor($model->staff_id, $model->id),
        ]);
    }

    public function actionView($id)
    {
        $model = $this->findModel($id);

        $isOwn = (int) $model->staff_id === (int) Yii::$app->user->identity->staff_id;
        $canReview = Yii::$app->user->identity->canApproveClaims();
        if (!$isOwn && !$canReview) {
            throw new ForbiddenHttpException('You can only view your own claims.');
        }

        return $this->render('view', [
            'model' => $model,
            'isOwn' => $isOwn,
        ]);
    }

    /**
     * Draft -> Submitted. Just the status flip and history entry - the
     * automatic limit/cutoff validation and human approval review are
     * separate phases, not part of submission itself.
     */
    public function actionSubmit($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnClaim($model);

        if (!$model->canEdit()) {
            Yii::$app->session->setFlash('warning', 'This claim has already been submitted.');
            return $this->redirect(['view', 'id' => $id]);
        }

        if (empty($model->items)) {
            Yii::$app->session->setFlash('error', 'Add at least one item before submitting.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $fromStatus = $model->status;
        $model->status = Claim::STATUS_SUBMITTED;
        $model->submitted_at = date('Y-m-d H:i:s');
        $model->save(false);

        ClaimStatusHistory::record($model->id, $fromStatus, Claim::STATUS_SUBMITTED, Yii::$app->user->identity->staff_id);

        $category = $model->claimCategory;

        // Real check: annual limit. Only categories with a limit set
        // (Mileage has none) get checked; exceeding it auto-rejects the
        // claim, matching the confirmed workflow.
        if ($category && $category->hasLimit()) {
            $usedSoFar = Claim::usedAmountThisYear($model->staff_id, $category->id, $model->id);
            $projectedTotal = $usedSoFar + (float) $model->total_amount;

            if ($projectedTotal > (float) $category->annual_limit_amount) {
                $model->status = Claim::STATUS_REJECTED;
                $model->rejected_at = date('Y-m-d H:i:s');
                $model->rejection_reason = sprintf(
                    'Exceeds annual limit for %s: RM%s already used this year + RM%s this claim = RM%s, over the RM%s limit.',
                    $category->name,
                    number_format($usedSoFar, 2),
                    number_format($model->total_amount, 2),
                    number_format($projectedTotal, 2),
                    number_format($category->annual_limit_amount, 2)
                );
                $model->save(false);

                ClaimStatusHistory::record($model->id, Claim::STATUS_SUBMITTED, Claim::STATUS_REJECTED, Yii::$app->user->identity->staff_id, $model->rejection_reason);

                Yii::$app->session->setFlash('error', $model->claim_no . ' was automatically rejected - ' . $model->rejection_reason);
                return $this->redirect(['view', 'id' => $id]);
            }
        }

        // Passed the real check. Used to auto-verify immediately here,
        // but Verify is now a real human step (the employee's department
        // verifier, or GM/Admin per the resolution logic) - the claim
        // waits at Submitted until that happens.

        // Soft, non-blocking reminder only - per the confirmed
        // requirements there's no hard cutoff, just a nudge to submit
        // promptly.
        $pastCutoffCount = 0;
        foreach ($model->items as $item) {
            if ($this->isPastCutoff($item->item_date)) {
                $pastCutoffCount++;
            }
        }
        if ($pastCutoffCount > 0) {
            Yii::$app->session->setFlash('warning', "Reminder: {$pastCutoffCount} item(s) in this claim are past the recommended cutoff (5th of the month following the expense). This didn't block submission, just a heads-up for next time.");
        }

        $verifierStaffId = \app\components\ClaimVerificationService::resolveVerifierStaffId($model);
        \app\components\ClaimNotificationService::notifySubmitted($model, $verifierStaffId);

        Yii::$app->session->setFlash('success', $model->claim_no . ' submitted and passed automatic validation. Awaiting verification.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Soft cutoff check: expenses should ideally be submitted by the 5th
     * of the month after they happened. Non-blocking - just informs the
     * flash message above, per the confirmed "no hard cutoff" requirement.
     */
    private function isPastCutoff($itemDate)
    {
        if (empty($itemDate)) {
            return false;
        }
        $ts = strtotime($itemDate);
        $cutoff = mktime(0, 0, 0, (int) date('n', $ts) + 1, 5, (int) date('Y', $ts));
        return time() > $cutoff;
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkOwnClaim($model);

        if ($model->status !== Claim::STATUS_DRAFT) {
            Yii::$app->session->setFlash('error', 'Only a draft claim can be deleted.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Draft claim deleted.');
        return $this->redirect(['index']);
    }

    private function loadAndSave($model, &$items)
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            return false;
        }

        if (!$model->load($request->post())) {
            return false;
        }

        // staff_id is never user-editable - always the logged-in user,
        // regardless of what (if anything) came through in POST.
        $model->staff_id = Yii::$app->user->identity->staff_id;

        $newItems = ClaimItem::createMultiple(ClaimItem::class, $items);
        ClaimItem::loadMultiple($newItems, $request->post());
        foreach ($newItems as $i => $item) {
            $item->receiptFile = UploadedFile::getInstance($item, "[$i]receiptFile");

            // Server-side enforcement, not just the JS on the form - a
            // mileage row's amount is always distance x the current rate,
            // regardless of what was actually submitted.
            if (!empty($item->vehicle_type) && $item->distance_km !== null && $item->distance_km !== '') {
                $item->amount = round((float) $item->distance_km * \app\models\MileageRate::currentRateFor($item->vehicle_type), 2);
            }
        }
        $items = $newItems;

        $valid = $model->validate();
        foreach ($items as $item) {
            if (!$item->validate()) {
                $valid = false;
            }
        }

        if (!$valid) {
            Yii::$app->session->setFlash('error', $this->collectErrors($model, $items));
            return false;
        }

        $model->total_amount = array_sum(array_map(function ($item) {
            return (float) $item->amount;
        }, $items));

        $wasRejected = $model->status === Claim::STATUS_REJECTED;
        if ($wasRejected) {
            $model->status = Claim::STATUS_DRAFT;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $isNew = $model->isNewRecord;

            if (!$model->save(false)) {
                $transaction->rollBack();
                return false;
            }

            // Remove any items that existed before but were deleted from
            // the form (only relevant on update).
            if (!$isNew) {
                $keepIds = array_filter(array_map(function ($item) {
                    return $item->id;
                }, $items));
                ClaimItem::deleteAll(['and',
                    ['claim_id' => $model->id],
                    $keepIds ? ['not in', 'id', $keepIds] : '1=1',
                ]);
            }

            foreach ($items as $item) {
                $item->claim_id = $model->id;
                if (!$item->save(false)) {
                    $transaction->rollBack();
                    return false;
                }
                $item->saveUploadedReceipt();
            }

            if ($isNew) {
                ClaimStatusHistory::record($model->id, null, Claim::STATUS_DRAFT, Yii::$app->user->identity->staff_id, 'Claim created.');
            } elseif ($wasRejected) {
                ClaimStatusHistory::record($model->id, Claim::STATUS_REJECTED, Claim::STATUS_DRAFT, Yii::$app->user->identity->staff_id, 'Edited after rejection.');
            }

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', $model->claim_no . ' saved as draft.');
        return true;
    }

    private function activeCategories()
    {
        return ClaimCategory::find()
            ->where(['status' => ClaimCategory::STATUS_ACTIVE])
            ->orderBy(['name' => SORT_ASC])
            ->all();
    }

    /**
     * Informational only - shown on the form so an employee can see how
     * much of their annual limit is left before they submit. The real
     * enforcement happens at actionSubmit, not here.
     */
    private function remainingLimitsFor($staffId, $excludeClaimId)
    {
        $result = [];
        foreach ($this->activeCategories() as $category) {
            if (!$category->hasLimit()) {
                continue;
            }
            $used = Claim::usedAmountThisYear($staffId, $category->id, $excludeClaimId);
            $result[$category->id] = [
                'limit' => (float) $category->annual_limit_amount,
                'used' => $used,
                'remaining' => (float) $category->annual_limit_amount - $used,
            ];
        }
        return $result;
    }

    private function checkOwnClaim($model)
    {
        if ((int) $model->staff_id !== (int) Yii::$app->user->identity->staff_id) {
            throw new ForbiddenHttpException('You can only manage your own claims.');
        }
    }

    protected function findModel($id)
    {
        if (($model = Claim::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested claim does not exist.');
    }

    private function collectErrors($model, $items)
    {
        $messages = [];
        foreach ($model->getErrors() as $attrErrors) {
            $messages = array_merge($messages, $attrErrors);
        }
        foreach ($items as $item) {
            foreach ($item->getErrors() as $attrErrors) {
                $messages = array_merge($messages, $attrErrors);
            }
        }
        return $messages ? implode('<br>', $messages) : 'Failed to save. Please check your input.';
    }
}