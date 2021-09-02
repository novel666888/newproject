<?php
define('APP_ROOT_PATH', __DIR__ . '/../..');
$___ENV = @file_get_contents(__DIR__ . '/../../deployment_environment');
empty($___ENV) && die('env file missing.');
$___ENV = trim($___ENV);
in_array($___ENV, ['prod', 'test', 'dev']) || die('unknown env.');
if ($___ENV == 'prod') {
    define('YII_ENV', 'prod');
    define('YII_DEBUG', false);
    define('YII_CONSOLE', false);
} else {
    defined('YII_DEBUG') or define('YII_DEBUG', true);
    defined('YII_ENV') or define('YII_ENV', $___ENV);
}

require __DIR__ . "/../../vendor/aliyuncs/oss-sdk-php/autoload.php";
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../vendor/yiisoft/yii2/Yii.php';
require __DIR__ . '/../../common/config/bootstrap.php';
require __DIR__ . '/../config/bootstrap.php';
require __DIR__ . '/../../common/lib/inc_constant.php';

$config = yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../../common/config/main.php', YII_ENV_DEV ? require __DIR__ . '/../../common/config/main-local.php' : [],
    require __DIR__ . '/../config/main.php', YII_ENV_DEV ? require __DIR__ . '/../config/main-local.php' : []
);

(new yii\web\Application($config))->run();
