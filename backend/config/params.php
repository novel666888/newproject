<?php
return [
    'adminEmail' => 'admin@example.com',
    'bossSignCheckSwitch' => 1,//sign校验 0为关闭校验 1开启校验
    'secretKey' => '7g2cf84185d032de45f95q2198a57',
    'jwt-secret' =>'jwt-e8773605ba20edbd6376',

    'permissionWhiteList' => [ //权限白名单

    ],

    'signCheckWhite'=>[ //签名验证白名单

    ],

    'whiteList' => [ //token验证白名单

    ],

    'wxConfig' => [
        'appId' => '',
        'appSecret' => '',
        'mchId' => '',
        'serialNo' => '',
        'notifyUrl' => '',
        'prepayOrderUrl' => 'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi',
    ]
];
