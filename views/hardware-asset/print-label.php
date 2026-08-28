<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\models\HardwareAsset $model */
$this->title = $model->asset_tag . ' - QR Label';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= Html::encode($this->title) ?></title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; margin: 0; padding: 20px; }
        .label {
            width: 260px;
            border: 1px solid #000;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
            display: inline-block;
        }
        .label img { width: 180px; height: 180px; }
        .label .tag { font-weight: bold; font-size: 13px; margin-top: 6px; }
        .label .model { font-size: 11px; color: #333; }
        .no-print { text-align: center; margin-top: 20px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="label">
    <img src="<?= Url::to(['qr', 'id' => $model->id]) ?>" alt="QR code">
    <div class="tag"><?= Html::encode($model->asset_tag) ?></div>
    <div class="model"><?= Html::encode($model->brand) ?> <?= Html::encode($model->model) ?></div>
    <?php if ($model->serial_no): ?><div class="model"><?= Html::encode($model->serial_no) ?></div><?php endif; ?>
</div>

<div class="no-print">
    <button onclick="window.print()">Print</button>
</div>

</body>
</html>