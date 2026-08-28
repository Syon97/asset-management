<?php

namespace app\controllers;

use app\models\Staff;
use app\models\StaffSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * StaffController implements the CRUD actions for Staff model.
 */
class StaffController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Staff models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new StaffSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Staff model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Staff model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        Yii::$app->session->setFlash('warning', 'Staff records are synced from HR databases and cannot be created manually.');
        return $this->redirect(['index']);
    }

    /**
     * Updates an existing Staff model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        Yii::$app->session->setFlash('warning', 'Staff records are synced from HR databases and cannot be edited manually.');
        return $this->redirect(['index']);
    }

    public function actionSync()
    {
        $service = new \app\components\StaffSyncService();
        $stats = $service->sync();

        Yii::$app->session->setFlash('success',
            "Synced from staff_gwidb: {$stats['created']} new, {$stats['updated']} updated (of {$stats['total']} total)."
        );

        return $this->redirect(['index']);
    }

    /**
     * JSON endpoint backing the staff picker modal: searchable, paginated.
     * @return array
     */
    public function actionPicker()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $q = trim((string) Yii::$app->request->get('q', ''));
        $page = max(1, (int) Yii::$app->request->get('page', 1));
        $pageSize = 10;

        $query = Staff::find()->with('department');

        if ($q !== '') {
            $query->andWhere(['or',
                ['like', 'staff_name', $q],
                ['like', 'staff_id', $q],
                ['like', 'department_text', $q],
                ['like', 'position', $q],
            ]);
        }

        $total = (int) $query->count();
        $totalPages = (int) max(1, ceil($total / $pageSize));

        $rows = $query->orderBy(['staff_name' => SORT_ASC])
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->all();

        $items = array_map(function (Staff $staff) {
            return [
                'id' => $staff->id,
                'staff_name' => $staff->staff_name,
                'department' => $staff->department->name ?? $staff->department_text,
                'position' => $staff->position,
            ];
        }, $rows);

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'pageSize' => $pageSize,
            'totalPages' => $totalPages,
        ];
    }

    /**
     * Deletes an existing Staff model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Staff model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Staff the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Staff::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
