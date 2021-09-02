<?php
return [
    'adminEmail' => 'admin@example.com',
    //    'events' => require 'events.php',//事件订阅配置
    'api' => [
        'server' => 'https://ad.oceanengine.com/open_api/2/',
        'douyin_server'=>'https://open.douyin.com/',
        'douyin'=>[
            'get_static_code'=>'oauth/authorize/v2/',//静默授权获取code
            'get_code'=>'platform/oauth/connect/',//获取code
            'get_access_token'=>'/oauth/access_token/',//获取access_token
            'refresh_token'=>'/oauth/renew_refresh_token/',//刷新refresh_token
            'refresh_access_token'=>'/oauth/refresh_token/',//刷新access_token
            'video_search'=>'/video/search/',//抓取视频
        ],
        'account' => [
            'advertiserInfo' => 'advertiser/info/',         //广告主信息
            'agent' => 'agent/advertiser/select/',          //广告主列表（代理商）
            'get' => 'advertiser/fund/get/',                //获取广告主账户余额
            'dailyStat' => 'advertiser/fund/daily_stat/',   //广告主日消耗
            'budget'=>'advertiser/budget/get/',             //广告主预算
            'majordomo' => 'majordomo/advertiser/select/',   //广告主列表（管家）
        ],
        'tools' =>[
            'log'=>'tools/log_search/',                   //广告主账号操作日志
            'adConvert' => 'tools/ad_convert/read/',      //查询转化目标详细信息
            'site' => 'tools/site/read/',                 //落地页站点详情
            'createSite' => 'tools/site/create/',         //创建落地页站点
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
            'addVideo' => 'file/video/ad/',              //上传视频
            'bindMaterial' => 'file/material/bind/',     //素材推送
            'image' => 'file/image/get/',                //获取图片素材
            'materialDay'=>'report/integrated/get/'
        ],
        'plan' => [
            'adGet' => 'ad/get/',                         //获取广告计划
            'adGetDay'=>'report/ad/get/',                 //获取广告计划日数据
            'adGetIntergrated'=>'report/integrated/get/', //获取广告计划受众数据
            'adPlanUpdate'=>'ad/update/status/',          //更新计划状态
            'campaignGet' => 'campaign/get/',             //获取广告组
            'createCampaign' => 'campaign/create/',       //创建广告组
            'updateCampaign' => 'campaign/update/ ',      //修改广告组
            'createPlan' => 'ad/create/',                 //创建广告计划
            'flowPackage'=>'tools/union/flow_package/get/', //获取计划穿山甲流量包
        ],
        'creative' => [
            'creativeDay'=>'report/creative/get/' ,      //获取广告创意日数据
            'creativeInfo'=>'creative/get/',             //创意列表
            'getCreative'=>'creative/read_v2/',          //创意详情
            'createCreative' => 'creative/create_v2/',   //创建广告创意
            'createProceduralCreative' => 'creative/procedural_creative/create/',   //创建程序化创意（营销链路）
            'createCustomCreative' => 'creative/custom_creative/create/',   //创建自定义创意（营销链路）
        ],
        'report' => [
            'advertiserGet' => 'report/advertiser/get/',  //广告主日数据
            'adtruetimeCost'=>'report/agent/get_v2/'
        ],
        'accident' => [
            'getConvert' => 'tools/adv_convert/select/'  //获取账户下的转化目标
        ],
        'customAudience' => [
            'getCustomAudience' => 'dmp/custom_audience/select/'  //账户人群定向包
        ],
        'campaign'=>[ //广告组
            'update' => 'campaign/update/status/'//暂停广告组
        ],
        'audiencePackage'=>[ //账户匹配定向包
            'get'=>'audience_package/get/' //获取定向包
        ]
    ],
    //千川接口
    'qc_api' => [
        'server' => 'https://ad.oceanengine.com/open_api/v1.0/',
        'plan' => [
            'planList' => 'qianchuan/ad/get/',//计划列表
        ],
        'creative' => [
            'creativeList' => 'qianchuan/creative/get/',//创意列表
        ],
        'report'=>[
            'advertiserGet'=>'qianchuan/report/advertiser/get/',//账户数据
            'planGet'=>'qianchuan/report/ad/get/',//计划数据
            'creativeGet'=>'qianchuan/report/creative/get/',//创意数据
        ],
    ],
    "convert_type" => [
        'AD_CONVERT_TYPE_LT_ROI'=>'广告变现ROI'
        ,'AD_CONVERT_TYPE_PREMIUM_PAYMENT'=>'保险支付'
        ,'AD_CONVERT_TYPE_GAME_ADDICTION'=>'关键行为'
        ,'AD_CONVERT_TYPE_PRE_LOAN_CREDIT'=>'互联网金融-预授信'
        ,'AD_CONVERT_TYPE_LOAN_CREDIT'=>'互联网金融-授信'
        ,'AD_CONVERT_TYPE_FORM'=>'表单提交'
        ,'AD_CONVERT_TYPE_ACTIVE'=>'激活'
        ,'AD_CONVERT_TYPE_ACTIVE_REGISTER'=>'激活且注册'
        ,'AD_CONVERT_TYPE_PAY'=>'激活且付费'
        ,'AD_CONVERT_TYPE_INSTALL_FINISH'=>'安装完成'
        ,'AD_CONVERT_TYPE_CUSTOMER_EFFECTIVE'=>'有效获客'
        ,'AD_CONVERT_TYPE_NEXT_DAY_OPEN'=>'激活且次留'
        ,'AD_CONVERT_TYPE_PURCHASE_ROI'=>'付费ROI'
        ,'AD_CONVERT_TYPE_LIVE_NATIVE_ACITON'=>'直播间原生互动'
        ,'AD_CONVERT_TYPE_LIVE_FOLLOW_ACITON'=>'直播间关注'
        ,'AD_CONVERT_TYPE_LIVE_COMMENT_ACTION'=>'直播间评论'
        ,'AD_CONVERT_TYPE_LIVE_GIFT_ACTION'=>'直播间内打赏'
        ,'AD_CONVERT_TYPE_LIVE_SLIDECART_CLICK_ACTION'=>'直播间查看购物车'
        ,'AD_CONVERT_TYPE_LIVE_CLICK_PRODUCT_ACTION'=>'直播间查看商品'
        ,'AD_CONVERT_TYPE_LIVE_ENTER_ACTION'=>'直播间观看'
        ,'AD_CONVERT_TYPE_LIVE_SUCCESSORDER_ACTION'=>'直播间成单'
        ,'AD_CONVERT_TYPE_LIVE_STAY_TIME'=>'直播间停留'
        ,'AD_CONVERT_TYPE_NEW_FOLLOW_ACTION'=>'粉丝增长'
        ,'AD_CONVERT_TYPE_LIVE_FANS_ACTION'=>'直播间加入粉丝团'
        ,'AD_CONVERT_TYPE_LIVE_BUSINESS_FITTING'=>'直播间组件点击'
        ,'AD_CONVERT_TYPE_FOLLOW_CLICK_PRODUCT'=>'关注并加购'
        ,'AD_CONVERT_TYPE_CONSULT_EFFECTIVE'=>'有效咨询'
        ,'AD_CONVERT_TYPE_BUTTON'=>'按钮跳转'
        ,'AD_CONVERT_TYPE_OTHER'=>'其他'
        ,'AD_CONVERT_TYPE_PHONE'=>'电话拨打'
        ,'AD_CONVERT_TYPE_VIEW'=>'关键页面浏览'
        ,'AD_CONVERT_TYPE_XPATH'=>'xpath类型转换',
        'AD_CONVERT_TYPE_APP_ORDER'=>'下单（电商）',
        'AD_CONVERT_TYPE_APP_CART'=>'app内添加购物车（电商）',
        'AD_CONVERT_TYPE_AUTHORIZATION'=>'授权（电商）',
        'AD_CONVERT_TYPE_APP_UV '=>'app内访问',
        'AD_CONVERT_TYPE_APP_DETAIL_UV'=>'app内详情页到站uv',
        'AD_CONVERT_TYPE_APP_PAY'=>'app内付费',
    ],
    'age'=>[
        "AGE_BETWEEN_18_23"=>"18-23岁",
        "AGE_BETWEEN_24_30"=>"24-30岁",
        "AGE_BETWEEN_31_40"=>"31-40岁",
        "AGE_BETWEEN_41_49"=>"41-49岁",
        "AGE_ABOVE_50"=>"大于等于50岁",
    ],
    'gender'=>[
        "1"=>"不限",
        "2"=>"男",
        "3"=>"女"
    ],
    //过滤已转化用户类型
    'hide_if_converted'=>[
        'NO_EXCLUDE'=>'不过滤',
        'AD'=>'广告计划',
        'CAMPAIGN'=>'广告组',
        'ADVERTISER'=>'广告账户',
        'APP'=>'APP',
        'CUSTOMER'=>'公司账户',
    ],
    //过滤时间范围
    'converted_time_duration'=>[
        'TODAY'=>'当天',
        'SEVEN_DAY'=>'7天',
        'ONE_MONTH'=>'1个月',
        'THREE_MONTH'=>'3个月',
        'SIX_MONTH'=>'6个月',
        'TWELVE_MONTH'=>'12个月',
    ],
    //手机品牌
    'device_brand'=>[
        'HONOR'=>'荣耀',
        'APPLE'=>'苹果',
        'HUAWEI'=>'华为',
        'XIAOMI'=>'小米',
        'SAMSUNG'=>'三星',
        'OPPO'=>'OPPO',
        'VIVO'=>'VIVO',
        'MEIZU'=>'魅族',
        'GIONEE'=>'金立',
        'COOLPAD'=>'酷派',
        'LENOVO'=>'联想',
        'LETV'=>'乐视',
        'ZTE'=>'中兴',
        'CHINAMOBILE'=>'中国移动',
        'HTC'=>'HTC',
        'PEPPER'=>'小辣椒',
        'NUBIA'=>'努比亚',
        'HISENSE'=>'海信',
        'QIKU'=>'奇酷',
        'TCL'=>'TCL',
        'SONY'=>'索尼',
        'SMARTISAN'=>'锤子手机',
        '360'=>'360手机',
        'ONEPLUS'=>'一加手机',
        'LG'=>'LG',
        'MOTO'=>'摩托罗拉',
        'NOKIA'=>'诺基亚',
        'GOOGLE'=>'谷歌',
    ],
    //智能放量
    'auto_extend_targets'=>[
        'REGION'=>'地域',
        'GENDER'=>'性别',
        'AGE'=>'年龄',
        'AD_TAG'=>'兴趣分类',
        'INTEREST_TAG'=>'兴趣关键词',
        'CUSTOM_AUDIENCE'=>'自定人群-定向',
        'INTEREST_ACTION'=>'行为兴趣'
    ],
    'inventoryInt'=>[
        1=>'头条信息流',
        2=>'头条文章详情页',
        3=>'西瓜信息流',
        4=>'火山信息流',
        5=>'抖音信息流',
        6=>'穿山甲',
        7=>'ohayoo精品游戏',
        8=>'穿山甲开屏广告',
        9=>'搜索广告——抖音位',
        10=>'搜索广告——头条位',
        11=>'通投智选',
        12=>'番茄小说',
        13=>'轻颜相机',
        14=>'皮皮虾',
        15=>'懂车帝',
        16=>'好好学习',
        17=>'faceu',
    ],
    'inventory'=>[
        'INVENTORY_FEED'=>'头条信息流',
        'INVENTORY_TEXT_LINK'=>'头条文章详情页',
        'INVENTORY_VIDEO_FEED'=>'西瓜信息流',
        'INVENTORY_HOTSOON_FEED'=>'火山信息流',
        'INVENTORY_AWEME_FEED'=>'抖音信息流',
        'INVENTORY_UNION_SLOT'=>'穿山甲',
        'UNION_BOUTIQUE_GAME'=>'ohayoo精品游戏',
        'INVENTORY_UNION_SPLASH_SLOT'=>'穿山甲开屏广告',
        'INVENTORY_AWEME_SEARCH'=>'搜索广告——抖音位',
        'INVENTORY_SEARCH'=>'搜索广告——头条位（广告投放）',
        'INVENTORY_UNIVERSAL'=>'通投智选（广告投放）',
        'INVENTORY_BEAUTY'=>'轻颜相机',
        'INVENTORY_PIPIXIA'=>'皮皮虾',
        'INVENTORY_AUTOMOBILE'=>'懂车帝',
        'INVENTORY_STUDY'=>'好好学习',
        'INVENTORY_FACE_U'=>'faceu',
        'INVENTORY_TOMATO_NOVEL'=>'番茄小说'
    ],
    'video_imgurl'=>"https://sf1-ttcdn-tos.pstatp.com/obj/",
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
    ],
    "plan_status"=>[
        "AD_STATUS_DELIVERY_OK"=>"投放中",
        "AD_STATUS_DISABLE"=>"计划暂停",
        "AD_STATUS_AUDIT"=>"新建审核中",
        "AD_STATUS_REAUDIT"=>"修改审核中",
        "AD_STATUS_DONE"=>"已完成（投放达到结束时间）",
        "AD_STATUS_CREATE"=>"计划新建",
        "AD_STATUS_AUDIT_DENY"=>"审核不通过",
        "AD_STATUS_BALANCE_EXCEED"=>"账户余额不足",
        "AD_STATUS_BUDGET_EXCEED"=>"超出预算",
        "AD_STATUS_NOT_START"=>"未到达投放时间",
        "AD_STATUS_NO_SCHEDULE"=>"不在投放时段",
        "AD_STATUS_CAMPAIGN_DISABLE"=>"已被广告组暂停",
        "AD_STATUS_CAMPAIGN_EXCEED"=>"广告组超出预算",
        "AD_STATUS_DELETE"=>"已删除",
        "AD_STATUS_ALL"=>"所有包含已删除",
        "AD_STATUS_NOT_DELETE"=>"所有不包含已删除（状态过滤默认值）",
        "AD_STATUS_ADVERTISER_BUDGET_EXCEED"=>"超出广告主日预算"
    ]
];
