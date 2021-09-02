<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2020/12/28
 * Time: 17:37
 */
namespace common\queue;

use common\models\Advertiser;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class TestQueue extends BaseObject implements JobInterface
{

    public $name;
    public $age;
    public function execute($queue)
    {
        // TODO: Implement execute() method.
        $res = 'name：'.$this->name.',age：'.$this->age;
        file_put_contents('/data/api/MdkAdmin/backend/runtime/test.txt', $res.PHP_EOL, FILE_APPEND);
        exit;
    }

}