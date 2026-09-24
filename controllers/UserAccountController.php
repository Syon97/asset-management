<?php

namespace app\controllers;

use Yii;
use app\models\UserAccount;
use app\models\UserAccountSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class UserAccountController extends Controller
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
                            return !Yii::$app->user->isGuest && Yii::$app->user->identity->isAdmin();
                        },
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    throw new ForbiddenHttpException('Only Admin can manage user accounts.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'update-role' => ['POST'],
                    'toggle-status' => ['POST'],
                    'reset-password' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new UserAccountSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionUpdateRole($id)
    {
        $model = $this->findModel($id);

        $newRole = Yii::$app->request->post('role');
        if (!in_array($newRole, array_keys(UserAccount::roleLabels()), true)) {
            Yii::$app->session->setFlash('error', 'Invalid role.');
            return $this->redirect(['index']);
        }

        // Guard against an admin accidentally demoting themselves and
        // getting locked out of this very screen.
        if ($model->id === Yii::$app->user->id && $newRole !== UserAccount::ROLE_ADMIN) {
            Yii::$app->session->setFlash('error', "You can't change your own role away from Admin.");
            return $this->redirect(['index']);
        }

        $model->role = $newRole;
        $model->save(false, ['role']);

        Yii::$app->session->setFlash('success', "{$model->username}'s role updated to " . UserAccount::roleLabels()[$newRole] . '.');
        return $this->redirect(['index']);
    }

    public function actionToggleStatus($id)
    {
        $model = $this->findModel($id);

        if ($model->id === Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', "You can't deactivate your own account.");
            return $this->redirect(['index']);
        }

        $model->status = $model->status === UserAccount::STATUS_ACTIVE
            ? UserAccount::STATUS_INACTIVE
            : UserAccount::STATUS_ACTIVE;
        $model->save(false, ['status']);

        Yii::$app->session->setFlash('success', "{$model->username} is now " . strtoupper($model->status) . '.');
        return $this->redirect(['index']);
    }

    /**
     * Resets an account's password back to its default (staff ID) and
     * forces a change on next login - useful if someone's genuinely
     * locked out and needs a way back in without IT knowing their
     * current password.
     */
    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);

        $model->setPassword($model->username);
        $model->must_change_password = true;
        $model->save(false, ['password_hash', 'must_change_password']);

        Yii::$app->session->setFlash('success', "{$model->username}'s password reset to default. They'll be asked to set a new one on next login.");
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = UserAccount::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested account does not exist.');
    }
}