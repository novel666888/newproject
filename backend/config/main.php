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
        ],
        'accounts' => [
            'class' => 'backend\modules\accounts\Module',
        ],
        'approve' => [
            'class' => 'backend\modules\approve\Module',
        ],
        'basic' => [
            'class' => 'backend\modules\basic\Module',
        ],
        'data' => [
            'class' => 'backend\modules\data\Module',
        ],
        'cost' => [
            'class' => 'backend\modules\cost\Module',
        ],
        'tool' => [
            'class' => 'backend\modules\tool\Module',
        ],
        'mdkweb' => [
            'class' => 'backend\modules\mdkweb\Module',
        ],
        'video' => [
             'class' => 'backend\modules\video\Module',
        ],
        'external' => [
            'class' => 'backend\modules\external\Module',
        ],
        'homepage' => [
            'class' => 'backend\modules\homepage\Module',
        ],
        'accident' => [
            'class' => 'backend\modules\accident\Module',
        ],
        'demand' => [
            'class' => 'backend\modules\demand\Module',
        ],
        'actor' => [
            'class' => 'backend\modules\actor\Module',
        ],
        'check' => [
            'class' => 'backend\modules\check\Module',
        ],

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
