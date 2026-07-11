<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'join-navy-admin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'timeZone' => 'Asia/Dhaka',
      // 'defaultRoute' => 'user/index',
     'layout'=>'admin', // default layout
    'components' => [
        'request' => [
            'csrfParam' => '_navy_admin',
            'cookieValidationKey' => 'a1231b17f6019bbad974_d_dafafaj-53c91dd75d46cb5ff13e',
            'enableCsrfValidation' => false,
			/*'except' => [
				'site/my-action',
			],
			*/
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_join_navy-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'join_navy-backend',
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
            'class'=>'yii\web\UrlManager',
            'enablePrettyUrl'=>true,
            'showScriptName'=>false,
            'enableStrictParsing'=>false,
            'rules' => [
            ],
        ],
        
    ],
    // 'as beforeRequest' => [  //if guest user access site so, redirect to login page.
    //     'class' => 'yii\filters\AccessControl',
    //     'rules' => [
    //         [
    //             'actions' => ['login', 'error'],
    //             'allow' => true,
    //         ],
    //         [
    //             'allow' => true,
    //             'roles' => ['@'],
    //         ],
    //     ],
    // ],


    // Global action logging
    'on beforeRequest' => function ($event) {
        Yii::$app->controllerMap = []; // ensure controllers are ready 
        Yii::$app->on(\yii\base\Application::EVENT_BEFORE_ACTION, function($event) {           
            $action = $event->action;
            $userId = Yii::$app->user->id ?? 'guest';
            $user = Yii::$app->user->identity;
            $user_name = $user->username ?? 'guest';
            $ip = Yii::$app->request->userIP;
            $method = Yii::$app->request->method;
            $route = $action->uniqueId;            
            // Get POST data if available, otherwise GET data
            $params = [];
            $controller_id = strtolower($event->action->controller->id ?? 'unknown');

            $is_update = false;
            $update_id = '';
            if ($method === 'POST') {
                $params = Yii::$app->request->post();
              if($event->action->id === 'update')   {
                $is_update = true;
                $update_id = Yii::$app->request->get('id') ?? '';
              }
            } else {
                $params = Yii::$app->request->get();
            }

            $logData = [                             
                'route' => $route, 
                'method' => $method, 
                'is_update' => $is_update,
                'update_id' => $update_id,
                'data' => [                   
                    'user'=>[
                        'id'=>$userId,
                        'name'=>$user_name,
                        'ip'=>$ip,
                        'route'=>$route,                                       
                    ],
                    'params' => $params,                   
                ],
            ];            
           
            if($method === 'POST'){
                if($is_update && $update_id){
                    Yii::$app->r2Storage->actionLog(logData: $logData, logFile: 'action_log/'.$controller_id.'/update.ndjson');
                }else{
                    Yii::$app->r2Storage->actionLog(logData: $logData, logFile: 'action_log/'.$controller_id.'/add.ndjson');
                }
            }
            else{
                Yii::$app->r2Storage->actionLog(logData: $logData, logFile: 'action_log/'.$controller_id.'/'.date('Y-m-d').'_'.strtolower($method).'.ndjson');
            }
            
        });
    },

    'as access' => [  // Access control: redirect guest users to login
        'class' => 'yii\filters\AccessControl',
        'rules' => [
            [
                'actions' => ['login', 'error'], // allow these actions for everyone
                'allow' => true,
            ],
            [
                'allow' => true,
                'roles' => ['@'], // all other actions require login
            ],
        ],
    ],


    'params' => $params,
];
