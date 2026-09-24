<?php

namespace app\controllers;

use Yii;
use app\models\Department;
use app\models\DepartmentVerifier;
use app\models\AppSetting;
use app\models\Staff;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class DepartmentVerifierController extends Controller
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
                    throw new ForbiddenHttpException('Only Admin can configure claim verifiers.');
                },
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'set-verifier' => ['POST'],
                    'set-gm' => ['POST'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $departments = Department::find()->orderBy(['name' => SORT_ASC])->all();
        $mappings = DepartmentVerifier::find()->indexBy('department_id')->all();

        $gmStaffId = AppSetting::get(AppSetting::KEY_CLAIM_GM_STAFF_ID);
        $gmStaff = $gmStaffId ? Staff::findOne($gmStaffId) : null;

        return $this->render('index', [
            'departments' => $departments,
            'mappings' => $mappings,
            'gmStaff' => $gmStaff,
        ]);
    }

    public function actionSetVerifier($departmentId)
    {
        $staffId = Yii::$app->request->post('staff_id');
        if (empty($staffId)) {
            Yii::$app->session->setFlash('error', 'Please select a staff member.');
            return $this->redirect(['index']);
        }

        $mapping = DepartmentVerifier::findOne(['department_id' => $departmentId]) ?: new DepartmentVerifier(['department_id' => $departmentId]);
        $mapping->staff_id = $staffId;
        $mapping->save(false);

        Yii::$app->session->setFlash('success', 'Verifier updated.');
        return $this->redirect(['index']);
    }

    public function actionSetGm()
    {
        $staffId = Yii::$app->request->post('staff_id');
        if (empty($staffId)) {
            Yii::$app->session->setFlash('error', 'Please select a staff member.');
            return $this->redirect(['index']);
        }

        AppSetting::set(AppSetting::KEY_CLAIM_GM_STAFF_ID, $staffId);

        Yii::$app->session->setFlash('success', 'General Manager (claim escalation contact) updated.');
        return $this->redirect(['index']);
    }
}