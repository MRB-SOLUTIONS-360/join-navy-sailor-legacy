<?php
return [
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'session' => [
            'class' => 'yii\web\Session',
            'cookieParams' => [
                // 'httpOnly' => true,
                'secure' => true, // Only send cookies over HTTPS
            ],
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'r2Storage' => [
            'class' => 'common\components\R2Storage',
            'accessKey' => '',
            'secretKey' => '',
            'endpoint' => '',
            'fileUrl' => '',
            'bucket' => 'sailor-images',
            'region' => 'auto',
            'verifySsl' => false,
        ],
    ],
];
