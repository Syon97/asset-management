<?php

namespace app\controllers;

use app\controllers\base\OperationsController;
use Yii;
use app\models\StockIssue;
use app\models\StockIssueSearch;
use app\models\Stock;
use app\models\StockLedger;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

class StockIssueController extends OperationsController
{
    public function actionIndex()
    {
        $searchModel = new StockIssueSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionCreate()
    {
        $model = new StockIssue();
        $model->issue_date = date('Y-m-d');

        if (Yii::$app->request->isPost && $model->load(Yii::$app->request->post())) {
            if (empty($model->issued_by)) {
                Yii::$app->session->setFlash('error', 'Please click "Select" next to Issued By.');
            } elseif (empty($model->holder_id)) {
                Yii::$app->session->setFlash('error', 'Please select who/what this stock is being issued to.');
            } else {
                $stock = Stock::findOne([
                    'catalog_item_id' => $model->catalog_item_id,
                    'department_id' => $model->department_id,
                ]);
                $available = $stock->quantity_on_hand ?? 0;

                if ($model->quantity > $available) {
                    Yii::$app->session->setFlash('error', "Only {$available} unit(s) available at this location for this item.");
                } elseif (!$model->validate()) {
                    Yii::$app->session->setFlash('error', $this->collectErrors($model));
                } else {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model->save(false)) {
                            $ok = Stock::adjust(
                                $model->catalog_item_id,
                                $model->department_id,
                                -$model->quantity,
                                StockLedger::MOVEMENT_OUT,
                                'issue',
                                $model->id,
                                $model->issued_by,
                                'Issued to ' . $model->getHolderLabel()
                            );
                            if ($ok) {
                                $transaction->commit();
                                Yii::$app->session->setFlash('success', 'Stock issued.');
                                return $this->redirect(['view', 'id' => $model->id]);
                            }
                        }
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Failed to issue stock.');
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
                }
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    protected function findModel($id)
    {
        if (($model = StockIssue::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested record does not exist.');
    }

    private function collectErrors($model)
    {
        $messages = [];
        foreach ($model->getErrors() as $attrErrors) {
            $messages = array_merge($messages, $attrErrors);
        }
        return $messages ? implode('<br>', $messages) : 'Failed to save. Please check your input.';
    }
}