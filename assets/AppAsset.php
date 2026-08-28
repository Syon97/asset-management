<?php

namespace app\assets;

use yii\web\AssetBundle;

class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=IBM+Plex+Sans:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css',
        'css/custom.css',
    ];
    public $js = [
        'js/sidebar.js',
        'js/app.js',
        'js/staff-picker.js',
        'js/catalog-picker.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\bootstrap5\BootstrapPluginAsset',
    ];
}