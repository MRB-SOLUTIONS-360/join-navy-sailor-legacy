<?php

namespace frontend\assets;

use yii\web\AssetBundle;

/**
 * Main frontend application asset bundle.
 */
class AppNavyAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'navy/css/boxicons.min.css',
        'navy/css/bootstrap-icons.css',
        'navy/css/bootstrap.min.css',
        'navy/css/animate.css',
       // 'navy/css/nice-select.css',
        'navy/css/style.css',
        'navy/css/responsive.css',
        'navy/css/style_step.css',
    ];
    public $js = [
       ['navy/js/jquery-3.6.0.min.js','position' => \yii\web\View::POS_HEAD],
        'navy/js/bootstrap.min.js',
       // 'navy/js/jquery.nice-select.js',
        'navy/js/wow.min.js',
        'navy/js/main.js',
    ];
    public $depends = [
        //'yii\web\YiiAsset',
        //'yii\bootstrap5\BootstrapAsset',
    ];
}
