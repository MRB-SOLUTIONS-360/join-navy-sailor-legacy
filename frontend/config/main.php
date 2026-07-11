<?php

use \yii\web\Request;

$baseUrl = (substr_count((new Request)->getBaseUrl(), '/frontend/web') > 0) ? str_replace('/frontend/web', '', (new Request)->getBaseUrl()) : (new Request)->getBaseUrl();

$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-frontend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'frontend\controllers',
    'timeZone' => 'Asia/Dhaka',
    'defaultRoute' => 'check-eligibility/index',
    'layout' => 'mainNavy', // default layout
    'components' => [
        'request' => [
            'csrfParam' => '_navy',
            'cookieValidationKey' => 'a1231db17ddev_2s)edfdasf6019bbad974@1dd75d46cb5ff13e',
            'baseUrl' => $baseUrl,
			
			'enableCsrfValidation' => false,
			/*'except' => [
				'site/my-action',
			],
			*/
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'enableSession' => true,
            'loginUrl' => ['candidate/login'],
            'identityCookie' => ['name' => '_join_bd_navy_front', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'join_bd_navy_front',
            /// 'savePath' => __DIR__ . '/../runtime', // a temporary folder on frontend
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'urlManager' => [
            'baseUrl' => $baseUrl,
            'class' => 'yii\web\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [],
        ],
        'view' => [
            'theme' => [
                'basePath' => '@app/',
                'baseUrl' => '@web/',
                // 'baseUrl'=>(strpos((new Request)->getBaseUrl(),'web')=== false)?'@web/web/':'@web/', // url for reright web folder
            ],
        ],

    ],
    'params' => $params,
];
