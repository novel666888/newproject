<?php
return [
    'adminEmail' => 'admin@example.com',
    'api' => [
        'server' => 'https://ad.oceanengine.com/open_api/2/',
        'account' => [
            'advertiserInfo' => 'advertiser/info/',         //广告主信息
            'agent' => 'agent/advertiser/select/',          //广告主列表
            'dailyStat' => 'advertiser/fund/daily_stat/',   //广告主日消耗
            'budget'=>'advertiser/budget/get/',             //广告主预算
        ],
        'tools' =>[
            'log'=>'tools/log_search/',                 //广告主账号操作日志
            'adConvert' => 'tools/ad_convert/read/'     //查询转化目标详细信息
        ],
        'adaudience'=>[
            'city'=>'report/audience/city/',            //获取账户城市受众数据
            'gender'=>'report/audience/gender/',        //获取账户性别受众数据
            'age'=>'report/audience/age/',              //获取账户年龄受众数据
            'province'=>'report/audience/province/',    //获取账户省级受众数据
            'adtag'=>'report/audience/tag/',            //获取账户兴趣分类数据
            'platform' => 'report/advertiser/get/',     //获取账户平台分类数据
            'interest_action'=>'report/audience/interest_action/list/',//获取账户行为兴趣
            'aweme'=>'report/audience/aweme/list/',     //获取账户抖音达人数据
        ],
        'material'=>[
            'video'=>'file/video/get/',                  //获取视频素材
            'cover'=>'tools/video_cover/suggest/',       //获取视频智能封面
        ],
        'plan' => [
            'adGet' => 'ad/get/',                         //获取广告计划
            'adGetDay'=>'report/ad/get/',                 //获取广告计划日数据
            'adGetIntergrated'=>'report/integrated/get/'  //获取广告计划受众数据
        ],
        'creative' => [
            'creativeDay'=>'report/creative/get/' ,      //获取广告创意日数据
            'creativeInfo'=>'creative/get/'
        ],
        'report' => [
            'advertiserGet' => 'report/advertiser/get/'  //广告主日数据
        ],
        'customAudience' => [
            'getCustomAudience' => 'dmp/custom_audience/select/'  //广告主日数据
        ],
    ],
    'oauth' => [
        'server' => 'https://ad.oceanengine.com/open_api/oauth2/',
        'token' => [
            'refreshToken' => 'refresh_token/',
            'getToken' => 'access_token/'
        ]
    ],
    'fly_dev' => [ //机器人密钥 测试
        'appid'=>'cli_9f4e2410e13d500d',
        'appSecret'=>'hp19wSYxriYPlts6d4vVtfA7f2kNkRQA'
    ],
        'fly_test' => [ //机器人密钥 测试
        'appid'=>'cli_9f4e2410e13d500d',
        'appSecret'=>'hp19wSYxriYPlts6d4vVtfA7f2kNkRQA'
    ],
        'fly_prod' => [ //机器人密钥 正式
        'appid'=>'cli_9f18c17c51f7900e',
        'appSecret'=>'QOsFkLuMOwPQawaMDhN9LbTsbxs0yKit'
    ],
    'noticeErrType'=>[
        "0"=>"链接错误",
        "1"=>"计划名错误",
        "2"=>"不在上新表内",
        "3"=>"穿山甲",
        "4"=>"智能放量",
        "5"=>"出价",
        "6"=>"转化目标",
        "7"=>"广告位",
        "8"=>"地区",
        "9"=>"性别",
        "10"=>"年龄",
        "11"=>"定向包",
        "12"=>"排除包",
    ]
];
