<?php

namespace app\controllers;

use Yii;
use app\models\MileageRate;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class MileageRateController extends Controller
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
                    throw new ForbiddenHttpException('Only Admin can configure mileage rates.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'update-rate' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $rates = MileageRate::find()->orderBy(['vehicle_type' => SORT_ASC])->all();

        return $this->render('index', [
            'rates' => $rates,
        ]);
    }

    public function actionUpdateRate($id)
    {
        $model = MileageRate::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('The requested rate does not exist.');
        }

        $newRate = Yii::$app->request->post('rate_per_km');
        if (!is_numeric($newRate) || $newRate < 0) {
            Yii::$app->session->setFlash('error', 'Please enter a valid rate.');
            return $this->redirect(['index']);
        }

        $model->rate_per_km = $newRate;
        $model->save(false, ['rate_per_km']);

        Yii::$app->session->setFlash('success', MileageRate::vehicleTypeLabels()[$model->vehicle_type] . ' rate updated to RM ' . number_format($newRate, 2) . '/km.');
        return $this->redirect(['index']);
    }
}