<?php
use yii\helpers\Html;
use app\assets\AppAsset;

AppAsset::register($this);

$route = Yii::$app->controller->route;

function navActive($prefixes, $route) {
    foreach ((array)$prefixes as $p) {
        if (strpos($route, $p) === 0) return 'active';
    }
    return '';
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <script>
        if (localStorage.getItem('sidebarCollapsed') === '1') {
            document.documentElement.classList.add('sidebar-collapsed-init');
        }
    </script>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<aside class="app-sidebar">
    <div class="brand">
        <span class="brand-text">Asset<span>Track</span></span>
        <button id="sidebar-toggle" class="sidebar-toggle-btn" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <button id="sidebar-reopen" class="sidebar-reopen-btn" type="button" aria-label="Expand sidebar">
        <i class="bi bi-chevron-right"></i>
    </button>

    <div class="nav-group-label">Overview</div>
    <a class="nav-link <?= navActive('dashboard', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/dashboard/index']) ?>">
        <i class="bi bi-speedometer2"></i> <span class="nav-text">Dashboard</span>
    </a>

    <div class="nav-group-label">Master Data</div>
    <a class="nav-link <?= navActive('department', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/department/index']) ?>"><i class="bi bi-diagram-3"></i> <span class="nav-text">Department</span></a>
    <a class="nav-link <?= navActive('category', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/category/index']) ?>"><i class="bi bi-tags"></i> <span class="nav-text">Category</span></a>
    <a class="nav-link <?= navActive('supplier', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/supplier/index']) ?>"><i class="bi bi-truck"></i> <span class="nav-text">Supplier</span></a>
    <a class="nav-link <?= navActive('item-catalog', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/item-catalog/index']) ?>"><i class="bi bi-box-seam"></i> <span class="nav-text">Item Catalog</span></a>
    <a class="nav-link <?= navActive('project', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/project/index']) ?>"><i class="bi bi-kanban"></i> <span class="nav-text">Project</span></a>
    <a class="nav-link <?= navActive('cost-center', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/cost-center/index']) ?>"><i class="bi bi-cash-coin"></i> <span class="nav-text">Cost Center</span></a>
    <a class="nav-link <?= navActive('accessory-type', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/accessory-type/index']) ?>"><i class="bi bi-usb-symbol"></i> <span class="nav-text">Accessory Type</span></a>
    <a class="nav-link <?= navActive('staff', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/staff/index']) ?>"><i class="bi bi-people"></i> <span class="nav-text">Staff</span></a>

    <div class="nav-group-label">Procurement</div>
    <a class="nav-link <?= navActive('purchase-requisition', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/purchase-requisition/index']) ?>"><i class="bi bi-file-earmark-text"></i> <span class="nav-text">Purchase Requisition</span></a>
    <a class="nav-link <?= navActive('purchase-order', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/purchase-order/index']) ?>"><i class="bi bi-cart-check"></i> <span class="nav-text">Purchase Order</span></a>
    <a class="nav-link <?= navActive('goods-receipt', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/goods-receipt/index']) ?>"><i class="bi bi-clipboard-check"></i> <span class="nav-text">Goods Receipt</span></a>
    <a class="nav-link <?= navActive('stock-issue', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/stock-issue/index']) ?>"><i class="bi bi-box-arrow-right"></i> <span class="nav-text">Issue Stock</span></a>
    <a class="nav-link <?= navActive('stock/', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/stock/index']) ?>"><i class="bi bi-boxes"></i> <span class="nav-text">Stock</span></a>

    <div class="nav-group-label">Assets</div>
    <a class="nav-link <?= navActive('hardware-asset', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/hardware-asset/index']) ?>"><i class="bi bi-laptop"></i> <span class="nav-text">Hardware Assets</span></a>
    <a class="nav-link <?= navActive('accessor', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/hardware-accessory/index']) ?>"><i class="bi bi-usb-plug"></i> <span class="nav-text">Accessories</span></a>
    <a class="nav-link <?= navActive('software', $route) ?>" href="<?= Yii::$app->urlManager->createUrl(['/software-license/index']) ?>"><i class="bi bi-cd"></i> <span class="nav-text">Software Licenses</span></a>
</aside>

<header class="app-topbar">

    <?php if (!empty($this->params['breadcrumbs'])): ?>
        <?= yii\widgets\Breadcrumbs::widget([
            'links' => $this->params['breadcrumbs'],
            'homeLink' => ['label' => 'Home', 'url' => Yii::$app->homeUrl],
            'options' => ['class' => 'breadcrumb mb-0'],
            'itemTemplate' => "<li class=\"breadcrumb-item\">{link}</li>\n",
            'activeItemTemplate' => "<li class=\"breadcrumb-item active\">{link}</li>\n",
        ]) ?>
    <?php else: ?>
        <span class="fw-semibold"><?= Html::encode($this->title) ?></span>
    <?php endif; ?>

</header>

<main class="app-main">
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
        <div class="alert <?= $cssClass ?> alert-dismissible fade show" role="alert">
            <?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php
        endforeach;
    endforeach;
    Yii::$app->session->removeAllFlashes();
    ?>
    <?= $content ?>
</main>

<?= $this->render('/common/_staff_picker_modal') ?>
<?= $this->render('/common/_catalog_picker_modal') ?>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>