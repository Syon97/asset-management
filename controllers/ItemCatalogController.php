<?php

namespace app\controllers;

use app\controllers\base\OperationsController;
use app\models\ItemCatalog;
use app\models\ItemCatalogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use Yii;

/**
 * ItemCatalogController implements the CRUD actions for ItemCatalog model.
 */
class ItemCatalogController extends OperationsController
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
     * Lists all ItemCatalog models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ItemCatalogSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single ItemCatalog model.
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
     * Creates a new ItemCatalog model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new ItemCatalog();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing ItemCatalog model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionPicker()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $q = trim((string) Yii::$app->request->get('q', ''));
        $page = max(1, (int) Yii::$app->request->get('page', 1));
        $pageSize = 10;

        $query = ItemCatalog::find()->with('category');

        if ($q !== '') {
            $query->andWhere(['like', 'item_name', $q]);
        }

        $total = (int) $query->count();
        $totalPages = (int) max(1, ceil($total / $pageSize));

        $rows = $query->orderBy(['item_name' => SORT_ASC])
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->all();

        $items = array_map(function (ItemCatalog $item) {
            return [
                'id' => $item->id,
                'item_name' => $item->item_name,
                'category' => $item->category->category_name ?? null,
                'item_type' => $item->item_type,
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

    public function actionSimilar()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $name = trim((string) Yii::$app->request->get('name', ''));
        if (mb_strlen($name) < 3) {
            return ['matches' => []];
        }

        $candidates = ItemCatalog::find()->select(['id', 'item_name'])->asArray()->all();

        $matches = [];
        $needle = mb_strtolower($name);
        foreach ($candidates as $candidate) {
            $hay = mb_strtolower($candidate['item_name']);
            similar_text($needle, $hay, $percent);
            if ($percent >= 65) {
                $matches[] = [
                    'id' => $candidate['id'],
                    'item_name' => $candidate['item_name'],
                    'score' => round($percent),
                ];
            }
        }

        usort($matches, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return ['matches' => array_slice($matches, 0, 5)];
    }

    /**
     * Deletes an existing ItemCatalog model.
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

    public function actionDuplicates()
    {
        $allItems = ItemCatalog::find()->orderBy('item_name')->all();

        $clusters = [];
        $assigned = [];

        foreach ($allItems as $i => $itemA) {
            if (isset($assigned[$itemA->id])) {
                continue;
            }
            $cluster = [$itemA];
            $nameA = mb_strtolower($itemA->item_name);

            foreach ($allItems as $j => $itemB) {
                if ($i === $j || isset($assigned[$itemB->id])) {
                    continue;
                }
                $nameB = mb_strtolower($itemB->item_name);
                similar_text($nameA, $nameB, $percent);
                if ($percent >= 70) {
                    $cluster[] = $itemB;
                }
            }

            if (count($cluster) > 1) {
                foreach ($cluster as $clusterItem) {
                    $assigned[$clusterItem->id] = true;
                }
                $clusters[] = $cluster;
            }
        }

        return $this->render('duplicates', [
            'clusters' => $clusters,
        ]);
    }

    public function actionMerge()
    {
        $keepId = (int) Yii::$app->request->post('keep_id');
        $mergeId = (int) Yii::$app->request->post('merge_id');

        if (!$keepId || !$mergeId || $keepId === $mergeId) {
            Yii::$app->session->setFlash('error', 'Please select two different items to merge.');
            return $this->redirect(['duplicates']);
        }

        $keep = ItemCatalog::findOne($keepId);
        $merge = ItemCatalog::findOne($mergeId);
        if (!$keep || !$merge) {
            Yii::$app->session->setFlash('error', 'One of the selected items no longer exists.');
            return $this->redirect(['duplicates']);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            \app\models\PrItem::updateAll(['catalog_item_id' => $keepId], ['catalog_item_id' => $mergeId]);
            \app\models\PoItem::updateAll(['catalog_item_id' => $keepId], ['catalog_item_id' => $mergeId]);
            \app\models\GrnItem::updateAll(['catalog_item_id' => $keepId], ['catalog_item_id' => $mergeId]);
            \app\models\StockIssue::updateAll(['catalog_item_id' => $keepId], ['catalog_item_id' => $mergeId]);
            \app\models\StockLedger::updateAll(['catalog_item_id' => $keepId], ['catalog_item_id' => $mergeId]);

            $mergeStocks = \app\models\Stock::find()->where(['catalog_item_id' => $mergeId])->all();
            foreach ($mergeStocks as $mergeStock) {
                $keepStock = \app\models\Stock::findOne([
                    'catalog_item_id' => $keepId,
                    'department_id' => $mergeStock->department_id,
                ]);
                if ($keepStock) {
                    $keepStock->quantity_on_hand += $mergeStock->quantity_on_hand;
                    $keepStock->save(false);
                    $mergeStock->delete();
                } else {
                    $mergeStock->catalog_item_id = $keepId;
                    $mergeStock->save(false);
                }
            }

            $merge->delete();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', "Merged \"{$merge->item_name}\" into \"{$keep->item_name}\". All history and stock were carried over.");
        return $this->redirect(['duplicates']);
    }

    /**
     * Finds the ItemCatalog model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return ItemCatalog the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = ItemCatalog::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
