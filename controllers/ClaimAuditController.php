<?php

namespace app\controllers;

use Yii;
use app\models\ClaimStatusHistorySearch;
use app\models\Claim;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;

class ClaimAuditController extends Controller
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
                    throw new ForbiddenHttpException('Only Financer or Admin can view the claims audit trail.');
                },
            ],
        ];
    }

    public function actionIndex()
    {
        $searchModel = new ClaimStatusHistorySearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'statusLabels' => Claim::statusLabels(),
        ]);
    }
}