<?php

namespace app\helpers;

use yii\helpers\Html;

class GridHelper
{
    public static function pagerConfig()
    {
        return [
            'options' => ['class' => 'pagination'],
            'linkContainerOptions' => ['class' => 'page-item'],
            'linkOptions' => ['class' => 'page-link'],
            'disabledListItemSubTagOptions' => ['class' => 'page-link'],
            'activePageCssClass' => 'active',
            'disabledPageCssClass' => 'disabled',
        ];
    }

    public static function actionColumn($template = '{view} {update} {delete}', $extra = [])
    {
        return array_merge([
            'class' => 'yii\grid\ActionColumn',
            'template' => $template,
            'contentOptions' => ['class' => 'action-column'],
            'buttons' => [
                'view' => function ($url) {
                    return Html::a('<i class="bi bi-eye"></i>', $url, [
                        'class' => 'btn btn-sm btn-outline-secondary action-btn action-view',
                        'title' => 'View',
                    ]);
                },
                'update' => function ($url) {
                    return Html::a('<i class="bi bi-pencil"></i>', $url, [
                        'class' => 'btn btn-sm btn-outline-primary action-btn action-update',
                        'title' => 'Edit',
                    ]);
                },
                'delete' => function ($url) {
                    return Html::a('<i class="bi bi-trash"></i>', $url, [
                        'class' => 'btn btn-sm btn-outline-danger action-btn action-delete',
                        'title' => 'Delete',
                        'data-confirm' => 'Are you sure you want to delete this item?',
                        'data-method' => 'post',
                    ]);
                },
            ],
        ], $extra);
    }
}