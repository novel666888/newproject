<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/3/6
 * Time: 12:39
 */
return [
    //更新账户优化师
    'common\events\AdvertiserEvent.updateOpm' => [
        ['common\services\event\AdvertiserEventOpe','updateAdvertiserOpm'],
    ],

    //更新账户客户、子品牌、二级项目
    'common\events\AdvertiserEvent.updateGuest' => [
        ['common\services\AdvertiserEventOpe','updateAdvertiserGuest'],
    ],
    ];