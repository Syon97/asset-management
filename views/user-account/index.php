<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\models\UserAccount;

/** @var yii\web\View $this */
/** @var app\models\UserAccountSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'User Accounts';
$this->params['breadcrumbs'][] = $this->title;

$roleLabels = UserAccount::roleLabels();
?>
<div class="user-account-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <p class="text-muted">Roles and login access for every staff account. Accounts are created/deactivated automatically by the staff sync based on employment status - this screen is for assigning roles and manual overrides.</p>

    <?php Pjax::begin(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'pager' => \app\helpers\GridHelper::pagerConfig(),
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'staffName',
                'label' => 'Staff Name',
                'value' => function ($model) {
                    return $model->staff->staff_name ?? '-';
                },
            ],

            'username',

            [
                'label' => 'Department',
                'value' => function ($model) {
                    return $model->staff->department->name ?? '-';
                },
            ],

            [
                'attribute' => 'role',
                'format' => 'raw',
                'value' => function ($model) use ($roleLabels) {
                    $isSelf = $model->id === Yii::$app->user->id;
                    $options = [];
                    foreach ($roleLabels as $val => $label) {
                        $options[] = Html::tag('option', Html::encode($label), [
                            'value' => $val,
                            'selected' => $model->role === $val,
                        ]);
                    }
                    $selectDisabled = $isSelf ? 'disabled' : '';
                    $form = Html::beginForm(['update-role', 'id' => $model->id], 'post', ['class' => 'd-flex gap-1'])
                        . '<select name="role" class="form-select form-select-sm" ' . $selectDisabled . '>' . implode('', $options) . '</select>'
                        . ($isSelf ? '' : Html::submitButton('Save', ['class' => 'btn btn-sm btn-outline-primary']))
                        . Html::endForm();
                    return $form;
                },
                'filter' => $roleLabels,
            ],

            [
                'attribute' => 'status',
                'format' => 'raw',
                'value' => function ($model) {
                    $badgeClass = $model->status === UserAccount::STATUS_ACTIVE ? 'status-approved' : 'status-rejected';
                    $badge = Html::tag('span', strtoupper($model->status), ['class' => "badge-status {$badgeClass}"]);

                    if ($model->id === Yii::$app->user->id) {
                        return $badge;
                    }

                    $toggleLabel = $model->status === UserAccount::STATUS_ACTIVE ? 'Deactivate' : 'Activate';
                    $toggleClass = $model->status === UserAccount::STATUS_ACTIVE ? 'btn-outline-danger' : 'btn-outline-success';

                    return $badge . ' ' . Html::a($toggleLabel, ['toggle-status', 'id' => $model->id], [
                        'class' => "btn btn-sm {$toggleClass} ms-1",
                        'data' => ['method' => 'post', 'confirm' => "{$toggleLabel} this account?"],
                    ]);
                },
                'filter' => ['active' => 'Active', 'inactive' => 'Inactive'],
            ],

            [
                'attribute' => 'must_change_password',
                'label' => 'Must Change PW',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->must_change_password
                        ? '<span class="badge-status status-verified">YES</span>'
                        : '<span class="text-muted">no</span>';
                },
            ],

            [
                'attribute' => 'last_login_at',
                'label' => 'Last Login',
                'value' => function ($model) {
                    return $model->last_login_at ?: 'Never';
                },
            ],

            [
                'label' => '',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a('Reset Password', ['reset-password', 'id' => $model->id], [
                        'class' => 'btn btn-sm btn-outline-secondary',
                        'data' => ['method' => 'post', 'confirm' => "Reset {$model->username}'s password back to the default? They'll need to change it on next login."],
                    ]);
                },
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>