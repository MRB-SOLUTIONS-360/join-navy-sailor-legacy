<?php

namespace backend\assets;

use yii\web\AssetBundle;

/**
 * Main backend application asset bundle.
 */
class AppAdminAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        
        ['adminAsset/css/app.css', 'id' => 'app-style'],

        'adminAsset/css/icons.min.css',

    ];
    public $js = [
        //'adminAsset/js/vendor.min.js',
        
        'adminAsset/vendor/daterangepicker/moment.min.js',
        'adminAsset/vendor/daterangepicker/daterangepicker.js',
        'adminAsset/vendor/apexcharts/apexcharts.min.js',
        
       ///'adminAsset/js/pages/demo.dashboard-analytics.js',

         'adminAsset/js/app.min.js',

        //['adminAsset/js/app.min.js','position' => \yii\web\View::POS_HEAD],
    ];
    public $depends = [
         'yii\web\YiiAsset',
        // 'yii\bootstrap5\BootstrapAsset',
    ];
}
