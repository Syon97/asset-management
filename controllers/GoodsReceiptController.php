<?php

namespace app\controllers;

use Yii;
use app\models\GoodsReceipt;
use app\models\GoodsReceiptSearch;
use app\models\GrnItem;
use app\models\PurchaseOrder;
use app\models\PoItem;
use app\models\ItemCatalog;
use app\models\Stock;
use app\models\StockLedger;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class GoodsReceiptController extends Controller
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'approve' => ['POST'],
                    'reject' => ['POST'],
                    'set-external-ref' => ['POST'],
                ],
            ],
        ]);
    }

    public function actionIndex()
    {
        $searchModel = new GoodsReceiptSearch();
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

    /**
     * Records the external order/invoice number - either typed manually
     * or pulled from an uploaded PDF via text extraction.
     */
    public function actionSetExternalRef($id)
    {
        $model = $this->findModel($id);
        $model->external_ref_no = trim(Yii::$app->request->post('external_ref_no'));
        $model->save(false);

        Yii::$app->session->setFlash('success', 'External Ref. No. updated.');
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Step 1 (PO-based path): pick an approved-and-sent PO from its view
     * page. Builds a draft GRN pre-filled with each line's remaining
     * (unreceived) quantity.
     */
    public function actionCreate($poId)
    {
        $po = PurchaseOrder::findOne($poId);
        if ($po === null) {
            throw new NotFoundHttpException('The requested PO does not exist.');
        }

        if (!in_array($po->status, [PurchaseOrder::STATUS_SENT, PurchaseOrder::STATUS_PARTIALLY_RECEIVED])) {
            Yii::$app->session->setFlash('error', 'Goods can only be received against a PO that has been sent.');
            return $this->redirect(['/purchase-order/view', 'id' => $poId]);
        }

        $model = new GoodsReceipt();
        $model->grn_date = date('Y-m-d');
        $model->po_id = $po->id;
        $model->department_id = $po->department_id;

        $items = [];
        foreach ($po->items as $poItem) {
            $remaining = $poItem->quantity - $poItem->quantity_received;
            if ($remaining <= 0) {
                continue;
            }
            $grnItem = new GrnItem();
            $grnItem->po_item_id = $poItem->id;
            $grnItem->catalog_item_id = $poItem->catalog_item_id;
            $grnItem->quantity_received = $remaining;
            $items[] = $grnItem;
        }

        if (empty($items)) {
            Yii::$app->session->setFlash('warning', 'All items on this PO have already been fully received.');
            return $this->redirect(['/purchase-order/view', 'id' => $poId]);
        }

        if ($this->loadAndSave($model, $items)) {
            Yii::$app->session->setFlash('success', $model->grn_no . ' created as draft. Approve it to update stock.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', [
            'model' => $model,
            'items' => $items,
            'po' => $po,
        ]);
    }

    /**
     * Adhoc path: a receipt with no source PR/PO at all (e.g. bought off
     * Shopee/Lazada). Starts with one blank item row.
     */
    public function actionCreateDirect()
    {
        $model = new GoodsReceipt();
        $model->grn_date = date('Y-m-d');

        $items = [new GrnItem()];

        if ($this->loadAndSave($model, $items)) {
            Yii::$app->session->setFlash('success', $model->grn_no . ' created as draft. Approve it to update stock.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create-direct', [
            'model' => $model,
            'items' => $items,
        ]);
    }

    private function loadAndSave($model, &$items)
    {
        $request = Yii::$app->request;

        if (!$request->isPost) {
            return false;
        }

        if (!$model->load($request->post())) {
            return false;
        }

        if (empty($model->received_by)) {
            Yii::$app->session->setFlash('error', 'Please click "Select" next to Received By.');
            return false;
        }

        $newItems = GrnItem::createMultiple(GrnItem::class, $items);
        GrnItem::loadMultiple($newItems, $request->post());
        $items = $newItems;

        $valid = $model->validate();
        foreach ($items as $item) {
            if (!$item->validate()) {
                $valid = false;
                continue;
            }
            // Only enforce the "can't exceed remaining" rule for PO-linked lines.
            if ($item->po_item_id) {
                $poItem = PoItem::findOne($item->po_item_id);
                if ($poItem !== null) {
                    $remaining = $poItem->quantity - $poItem->quantity_received;
                    if ($item->quantity_received > $remaining) {
                        $item->addError('quantity_received', "Cannot receive more than the remaining {$remaining} for \"{$poItem->description}\".");
                        $valid = false;
                    }
                }
            }
        }

        if (!$valid) {
            Yii::$app->session->setFlash('error', $this->collectErrors($model, $items));
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if (!$model->save(false)) {
                $transaction->rollBack();
                return false;
            }
            foreach ($items as $item) {
                $item->grn_id = $model->id;
                if (!$item->save(false)) {
                    $transaction->rollBack();
                    return false;
                }
            }
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
    }

    /**
     * Approving is what actually commits the receipt: auto-creates a
     * catalog entry for any still-free-text line, updates the PO item's
     * received quantity (PO-linked lines only), adjusts stock + writes
     * the ledger entry, and recomputes the PO's overall status (PO-linked
     * receipts only - a direct/adhoc receipt has no PO to recompute).
     */
    public function actionApprove($id)
    {
        $model = $this->findModel($id);

        if (!$model->canApprove()) {
            Yii::$app->session->setFlash('warning', 'This GRN can no longer be approved.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $approvedBy = Yii::$app->request->post('approved_by');
        if (!$approvedBy) {
            Yii::$app->session->setFlash('error', 'Please select who is approving this GRN.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $departmentId = $model->department_id;

            foreach ($model->items as $grnItem) {
                if ($grnItem->quantity_received <= 0) {
                    continue;
                }

                $poItem = $grnItem->po_item_id ? $grnItem->poItem : null;

                $catalogItemId = $grnItem->catalog_item_id ?: ($poItem->catalog_item_id ?? null);
                if (!$catalogItemId) {
                    $descriptionForCatalog = $poItem->description ?? $grnItem->description;
                    $catalogItem = new ItemCatalog();
                    $catalogItem->item_name = $descriptionForCatalog;
                    $catalogItem->item_type = ItemCatalog::ITEM_TYPE_CONSUMABLE;
                    if (!$catalogItem->save(false)) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Failed to auto-create catalog entry for "' . $descriptionForCatalog . '".');
                        return $this->redirect(['view', 'id' => $id]);
                    }
                    $catalogItemId = $catalogItem->id;
                    if ($poItem) {
                        $poItem->catalog_item_id = $catalogItemId;
                        $poItem->save(false);
                    }
                }

                $grnItem->catalog_item_id = $catalogItemId;
                $grnItem->save(false);

                if ($poItem) {
                    $poItem->quantity_received += $grnItem->quantity_received;
                    $poItem->save(false);
                }

                Stock::adjust(
                    $catalogItemId,
                    $departmentId,
                    $grnItem->quantity_received,
                    StockLedger::MOVEMENT_IN,
                    'grn',
                    $model->id,
                    $approvedBy,
                    'Received via ' . $model->grn_no
                );
            }

            if (!$model->isDirect()) {
                $po = PurchaseOrder::findOne($model->po_id);
                if ($po !== null && !in_array($po->status, [PurchaseOrder::STATUS_CLOSED, PurchaseOrder::STATUS_REJECTED])) {
                    $allReceived = true;
                    $anyReceived = false;
                    foreach ($po->items as $poItem) {
                        if ($poItem->quantity_received > 0) {
                            $anyReceived = true;
                        }
                        if ($poItem->quantity_received < $poItem->quantity) {
                            $allReceived = false;
                        }
                    }
                    $po->status = $allReceived
                        ? PurchaseOrder::STATUS_RECEIVED
                        : ($anyReceived ? PurchaseOrder::STATUS_PARTIALLY_RECEIVED : $po->status);
                    $po->save(false);
                }
            }

            $model->approved_by = $approvedBy;
            $model->approved_at = date('Y-m-d H:i:s');
            $model->status = GoodsReceipt::STATUS_APPROVED;
            $model->save(false);

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        Yii::$app->session->setFlash('success', 'GRN approved. Stock updated.');
        return $this->redirect(['view', 'id' => $id]);
    }

    public function actionReject($id)
    {
        $model = $this->findModel($id);

        if (!$model->canReject()) {
            Yii::$app->session->setFlash('warning', 'This GRN can no longer be rejected.');
            return $this->redirect(['view', 'id' => $id]);
        }

        $model->rejected_by = Yii::$app->request->post('rejected_by');
        $model->rejected_at = date('Y-m-d H:i:s');
        $model->rejection_reason = Yii::$app->request->post('rejection_reason');
        $model->status = GoodsReceipt::STATUS_REJECTED;
        $model->save(false);

        Yii::$app->session->setFlash('success', 'GRN rejected. Stock was not affected.');
        return $this->redirect(['view', 'id' => $id]);
    }

    protected function findModel($id)
    {
        if (($model = GoodsReceipt::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested GRN does not exist.');
    }

    private function collectErrors($model, $items)
    {
        $messages = [];
        foreach ($model->getErrors() as $attrErrors) {
            $messages = array_merge($messages, $attrErrors);
        }
        foreach ($items as $item) {
            foreach ($item->getErrors() as $attrErrors) {
                $messages = array_merge($messages, $attrErrors);
            }
        }
        return $messages ? implode('<br>', $messages) : 'Failed to save. Please check your input.';
    }
}