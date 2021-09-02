<?php
$params = array_merge(
    require __DIR__ . '/params.php',
    require __DIR__ . '/../../common/config/params.php'
);//不要调换前后顺序

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'Asia/Shanghai',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
          ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning','info'],
                    'logFile' => '@console/runtime/logs/' . date('Ymd') . '.log',
                ],
            ],
        ],
    ],
    'params' => $params,
];
