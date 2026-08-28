<?php
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $clusters */

$this->title = 'Possible Duplicate Items';
$this->params['breadcrumbs'][] = ['label' => 'Item Catalog', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="item-catalog-duplicates">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Groups of catalog items with similar names. Review each group and merge any that are really the same thing — merging carries over all history (PR/PO/GRN references, stock quantity, and the stock ledger) to whichever one you keep, then removes the other.</p>

    <?php if (empty($clusters)): ?>
        <div class="alert alert-success">No likely duplicates found.</div>
    <?php endif; ?>

    <?php foreach ($clusters as $ci => $cluster): ?>
        <div class="pr-action-card" style="max-width: 100%;">
            <div class="pr-action-title">Possible Duplicate Group <?= $ci + 1 ?></div>

            <?= Html::beginForm(['merge'], 'post') ?>
                <table class="table table-bordered table-sm mb-3">
                    <thead>
                        <tr><th style="width:5%;">Keep</th><th>Item Name</th><th>Category</th><th>Type</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cluster as $item): ?>
                            <tr>
                                <td class="text-center">
                                    <input type="radio" name="keep_id" value="<?= $item->id ?>" required>
                                </td>
                                <td><?= Html::a(Html::encode($item->item_name), ['view', 'id' => $item->id], ['target' => '_blank']) ?></td>
                                <td><?= Html::encode($item->category->category_name ?? '-') ?></td>
                                <td><?= Html::encode($item->item_type) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <label class="form-label">Merge which one into the selected "keep"?</label>
                <select name="merge_id" class="form-select mb-2" style="max-width:400px;" required>
                    <option value="">-- Select the item to remove --</option>
                    <?php foreach ($cluster as $item): ?>
                        <option value="<?= $item->id ?>"><?= Html::encode($item->item_name) ?></option>
                    <?php endforeach; ?>
                </select>
                <div>
                    <button type="submit" class="btn btn-danger" onclick="return confirm('This will move all history to the kept item and permanently remove the other. Continue?');">Merge</button>
                </div>
            <?= Html::endForm() ?>
        </div>
    <?php endforeach; ?>

</div>