<?php
/**
 * 常量文件
 */

//审批状态
define('APPROVE_UNCHECKED', 1);  //待审核
define('APPROVE_REFUSED',   2);  //已拒绝
define('APPROVE_FINISHED',  3);  //已完成
define('APPROVE_RECALL',    4);  //已撤回
define('APPROVE_CHECK',     5);  //已查收

define('ADMIN_USER',         1);  //管理员
define('SALE_PART',         141); //销售部
define('OPTIMIZER_PART',    [133, 135, 373, 369, 149]); //运营部门组织架构id
define('VIDEO_PART',        [139, 149, 373]); //视频部门组织架构id
define('SELLER_PART',       [141]); //销售部门组织架构id
define('OPERATION_PART',    [263]); //运营支持部
define('VIDEO_RADAR_PART',       [1, 817, 819, 167, 165, 163, 161, 159, 157, 129, 131, 149, 151, 153, 155, 229, 231, 233, 235, 731, 733, 735, 833, 749, 753, 755, 763, 767, 773, 775, 777, 783, 787, 789, 791, 845, 821, 825, 835, 837, 849]);//视频雷达--我的视频、未匹配视频角色权限
define('VIDEO_DEPARTMENT',  ['武汉' =>149, '合肥' => 373, '视频一部' => 195, '视频二部' => 197, '视频三部' => 433]);//首页视频相关部门（武汉、合肥、视频一部、视频二部、视频三部）


define('FLY_NOTICE_USER',    [349,341]); //飞书监测通知人
define('ACCOUNT_APPROVE',  '账户分配提醒');
define('CONTRACT_EXPIRED', '合同到期提醒');
define('UPDATE_TOKEN_FAIL','同步token失败');

//mongo配置
if (YII_ENV_PROD){
    define('MONGO_DATABASE', 'mdk_prod');
    define('API_URL', 'http://api.mdkbj.com/');
}elseif (YII_ENV_TEST){
    define('MONGO_DATABASE', 'mdk_test');
    define('API_URL', 'http://testapi.mdkbj.com/');
}else{
    define('MONGO_DATABASE', 'mdk_dev');
    define('API_URL', 'http://devapi.mdkbj.com/');
}
define('WX_MSG_URL', 'http://106.55.56.234:9899/');//微信消息url

define('ACCESS_ID','LTAIdAXnj4p76huF');
define('ACCESS_SECRET','m4i9vY1bvhmpkskVVmNb67RlC1Z5lj');
define('OSS_ENDPOINT','oss-cn-beijing.aliyuncs.com');
define('ENDPOINT_PATH','http://oss-cn-beijing.aliyuncs.com');
define('OSS_HOST','http://oss.mdkbj.com');
define('OSS_BUCKET' ,'mdkbjpro');
define('OSS_PATH' ,'https://mdkbjpro.oss-cn-beijing.aliyuncs.com');
define('OSS_TEXT_BUCKET' ,'mdktext');
define('OSS_TEXT_PATH' ,'http://mdktext.oss-cn-beijing.aliyuncs.com');

define('OSS_UEDITOR_COVER','ueditordev/');
define('OSS_DOC_COVER','docdev/');
define('OSS_TEXT_COVER','textdev/');
define('OSS_PIC_COVER','mdknew/picture/');

//redis 键值
define('REDIS_FOR_NOTICE','redis_for_notice'); //待打电话通知
define('REDIS_AD_CLOSE_PLAN','redis_ad_close_plan');  //账户关停，计划依然再跑队列
define('REDIS_CLOSE_PLAN','redis_close_plan');    //二次检查待关闭队列

