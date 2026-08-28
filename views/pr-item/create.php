<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\PrItem $model */

$this->title = 'Create Pr Item';
$this->params['breadcrumbs'][] = ['label' => 'Pr Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="pr-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
