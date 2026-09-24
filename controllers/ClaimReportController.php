<?php

namespace app\controllers;

use Yii;
use app\models\ClaimReportSearch;
use app\models\ClaimCategory;
use app\models\Staff;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;

class ClaimReportController extends Controller
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
                    throw new ForbiddenHttpException('Only Financer or Admin can view claim reports.');
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new ClaimReportSearch();
        $params = Yii::$app->request->queryParams;

        $dataProvider = $searchModel->search($params);
        $summary = $searchModel->summarize($params);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'summary' => $summary,
            'categories' => ClaimCategory::find()->orderBy(['name' => SORT_ASC])->all(),
        ]);
    }
}