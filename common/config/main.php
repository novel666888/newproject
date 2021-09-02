<?php
if (YII_ENV_PROD) {
    $__dbConfig = [
        'host' => YII_CONSOLE ? '172.17.43.65' : '172.17.43.67',
        'name' => 'mdk',
        'user' => 'mdk-prod',
        'pass' => 'Mdk@prod',
        'port' => '3306'
    ];
    $_redisConfig = [
        'host' => '172.17.43.69',
        'port' => '63790',
        'password' => 'nw#[7Z:/0Ju$'
    ];
    $_mongoDb = [
        'host' => '172.17.43.69',
        'database' => 'mdk_prod',
        'port' => '27017',
        'user' => 'mdk-prod',
        'pass' => 'Mdk@2020#'
    ];
} elseif (YII_ENV_TEST){
    $__dbConfig = [
        'host' => '172.17.43.72',
        'name' => 'mdk_test',
        'user' => 'mdk-test',
        'pass' => 'Mdk@test',
        'port' => '3306'
    ];
    $_redisConfig = [
        'host' => '172.17.43.72',
        'port' => '63790',
        'password' => '?m3PP2Z#nG!9'
    ];
    $_mongoDb = [
        'host' => '172.17.43.72',
        'database' => 'mdk_test',
        'port' => '27017',
        'user' => 'mdk-test',
        'pass' => 'Mdk@2020#'
    ];
}else{
    /**
     *  服务器配置
     */
    $__dbConfig = [
        'host' => '172.17.43.71',
        'name' => 'mdk_dev',
        'user' => 'mdk-dev',
        'pass' => 'Mdk@dev',
        'port' => '3306'
    ];
    $_redisConfig = [
        'host' => '123.56.99.75',
        'port' => '63790',
        'password' => 'x29jIa!jqfB7',
    ];
    $_mongoDb = [
        'host' => '172.17.43.71',
        'database' => 'mdk_dev',
        'port' => '27017',
        'user' => 'mdk-dev',
        'pass' => 'Mdk@2020#'
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
        'mongodb' => [
            'class' => 'yii\mongodb\Connection',
            "dsn" => 'mongodb://'.$_mongoDb['host'].':'.$_mongoDb['port'].'/'.$_mongoDb['database'],
            'options' => [
                "username" => $_mongoDb['user'],
                "password" => $_mongoDb['pass']
            ]
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
