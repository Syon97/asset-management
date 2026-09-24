<?php

namespace app\controllers;

use Yii;
use app\models\ClaimCategory;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ClaimCategoryController extends Controller
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
                    throw new ForbiddenHttpException('Only Admin can configure claim categories.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $categories = ClaimCategory::find()->orderBy(['name' => SORT_ASC])->all();

        return $this->render('index', [
            'categories' => $categories,
        ]);
    }

    public function actionCreate()
    {
        $model = new ClaimCategory();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Claim category created.');
            return $this->redirect(['index']);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Claim category updated.');
            return $this->redirect(['index']);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if ($model->getIsNewRecord() === false) {
            $inUse = \app\models\Claim::find()->where(['claim_category_id' => $model->id])->exists();
            if ($inUse) {
                Yii::$app->session->setFlash('error', 'This category has claims already using it - deactivate it instead of deleting.');
                return $this->redirect(['index']);
            }
        }

        $model->delete();
        Yii::$app->session->setFlash('success', 'Claim category deleted.');
        return $this->redirect(['index']);
    }

    protected function findModel($id)
    {
        if (($model = ClaimCategory::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested claim category does not exist.');
    }
}