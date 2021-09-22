<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/params.php'
);

return [
    'id' => 'app-backend',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'bootstrap' => ['log'],
    'modules' => [//模块配置
        'user' => [
            'class' => 'backend\modules\user\Module',
        ]
    ],
    'components' => [
        'request' => [
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
                'text/json' => 'yii\web\JsonParser',
            ],
            'csrfParam' => '_csrf-backend',
            'cookieValidationKey' => 'LYJfXBxECbQJcn39WpFIpIiDQlswnZMd',
            'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
        ],
//        'request' => [
//            'csrfParam' => '_csrf-backend',
//            'enableCsrfValidation' => false, // 不开启csrf
//            'parsers' => [
//                'application/json' => 'yii\web\JsonParser',
//                'text/json' => 'yii\web\JsonParser',
//            ],
//        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','info'], //info日志需要的话此处需配置
                    //日志存储地址配置----TODO
                    'logFile' => '@backend/runtime/logs/' . date('Ymd') . '.log',
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
    ],
    'params' => $params,
];
