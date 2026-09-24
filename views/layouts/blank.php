<?php
use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<?php
$flashAlertClass = [
    'success' => 'alert-success',
    'error' => 'alert-danger',
    'danger' => 'alert-danger',
    'warning' => 'alert-warning',
    'info' => 'alert-info',
];
foreach (Yii::$app->session->getAllFlashes() as $flashType => $flashMessage):
    $cssClass = $flashAlertClass[$flashType] ?? 'alert-secondary';
    foreach ((array) $flashMessage as $msg):
?>
    <div class="alert <?= $cssClass ?> alert-dismissible fade show m-3" role="alert" style="max-width: 500px; margin-left: auto; margin-right: auto;">
        <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php
    endforeach;
endforeach;
Yii::$app->session->removeAllFlashes();
?>

<?= $content ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>