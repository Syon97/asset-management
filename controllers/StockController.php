<?php

namespace app\controllers;

use Yii;
use app\models\Stock;
use app\models\StockSearch;
use app\models\StockLedger;
use app\models\ItemCatalog;
use app\models\Department;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class StockController extends Controller
{
    public function actionIndex()
    {
        $searchModel = new StockSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Movement history for one item at one location.
     */
    public function actionLedger($catalogItemId, $departmentId)
    {
        $catalogItem = ItemCatalog::findOne($catalogItemId);
        $department = Department::findOne($departmentId);

        if ($catalogItem === null || $department === null) {
            throw new NotFoundHttpException('Item or location not found.');
        }

        $entries = StockLedger::find()
            ->where(['catalog_item_id' => $catalogItemId, 'department_id' => $departmentId])
            ->orderBy(['created_at' => SORT_DESC])
            ->all();

        return $this->render('ledger', [
            'catalogItem' => $catalogItem,
            'department' => $department,
            'entries' => $entries,
        ]);
    }
}