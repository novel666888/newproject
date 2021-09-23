<?php
if (YII_ENV_PROD) {
    $__dbConfig = [
        'host' => '',
        'name' => '',
        'user' => '',
        'pass' => '',
        'port' => ''
    ];
    $_redisConfig = [
        'host' => '',
        'port' => '',
        'password' => ''
    ];
} elseif (YII_ENV_TEST){
    $__dbConfig = [
        'host' => '',
        'name' => '',
        'user' => '',
        'pass' => '',
        'port' => ''
    ];
    $_redisConfig = [
        'host' => '',
        'port' => '',
        'password' => ''
    ];
}else{
    $__dbConfig = [
        'host' => '',
        'name' => '',
        'user' => '',
        'pass' => '',
        'port' => ''
    ];
    $_redisConfig = [
        'host' => '',
        'port' => '',
        'password' => '',
    ];
}

return [
    'timeZone' => 'Asia/Shanghai',
    'language' => 'zh-CN',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@mdklog' => '/data/logs/api',
        '@filecache' => '@runtime/cache/',
    ],
    'bootstrap' => ['log','queue'],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'i18n'         => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
//                    'basePath'=>'@message'
                ],
            ],
        ],
        'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=' . $__dbConfig['host'] . ';dbname=' . $__dbConfig['name'],
            'username' => $__dbConfig['user'],
            'password' => $__dbConfig['pass'],
            'charset' => 'utf8mb4',
            'tablePrefix' => 'tab_'
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => $_redisConfig['host'],
            'port' => $_redisConfig['port'],
            'database' => 0,
            'password'=>$_redisConfig['password']?$_redisConfig['password']:null,
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'useFileTransport' =>false,//这句一定有，false发送邮件，true只是生成邮件在runtime文件夹下，不发邮件
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => 'smtpdm.aliyun.com',  //每种邮箱的host配置不一样
                'username' => 'system@mail.tuotuo.com.cn',
                'password' => 'TuoMail2019',
                'port' => '80',
//                'encryption' => 'ssl',
            ],
            'messageConfig'=>[
                'charset'=>'UTF-8',
                'from'=>['system@mail.tuotuo.com.cn'=>'网站名']
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'redis' => 'redis',
            'channel' => 'queue',
            'as log' => \yii\queue\LogBehavior::class,
//            'path' => '@backend/runtime/queue',
        ],
    ],
//    'as demoBehaviors'=>'common\events\EventBehavior',
];
