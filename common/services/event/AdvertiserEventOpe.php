<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2021/3/6
 * Time: 12:48
 */
namespace common\services\event;

use common\models\AdPlanDayMongo;
use common\models\Guest;
use common\models\GuestBrand;
use common\models\SecondaryProject;
use common\models\Users;
use yii\base\Component;

class AdvertiserEventOpe extends Component{

    /**
     * 更新mongo集合ad_plan_day_mongo中的优化师信息
     * author: lijin
     * @param $data
     */
    public static function updateAdvertiserOpm($data){exit('df');
        \Yii::info(json_encode($data),'request_data');
        if (empty($data->advertiserId)){
            \Yii::info('缺少广告主id','data_empty_advertiser_id');
        }
        if (empty($data->extInfo['opm_id'])){
            \Yii::info('缺少优化师id','data_empty_opm_id');
        }
        $opm_info = Users::find()->select('id,username')->where(['id' => $data->extInfo['opm_id']])->asArray()->one();
        $up_data = [
            'optimizer_id' => $opm_info['id'],
            'optimizer_name' => $opm_info['username']
        ];
        AdPlanDayMongo::updateAll($up_data, ['advertiser_id' => $data->advertiserId]);
    }

    /**
     * 更新mongo集合ad_plan_day_mongo中的客户信息
     * author: lijin
     * @param $data
     */
    public static function updateAdvertiserGuest($data){
        \Yii::info(json_encode($data),'request_data');
        if (empty($data->advertiserId)){
            \Yii::info('缺少广告主id','data_empty_advertiser_id');
        }
        if (empty($data->extInfo['guest_id']) || empty($data->extInfo['guest_brand_id']) || empty($data->extInfo['secondary_project_id'])){
            \Yii::info('缺少参数id','data_empty_params_id');
        }
        $info = SecondaryProject::find()->alias('a')->leftJoin(GuestBrand::tableName() . ' as b', 'a.guest_brand_id = b.id')
            ->leftJoin(Guest::tableName() . ' as c', 'a.guest_id = c.id')
            ->select('a.project_name,b.brand_name,c.guest_name')
            ->where(['a.id' => $data->extInfo['secondary_project_id']])
            ->asArray()->one();
        $up_data = [
            'guest_id' => $data->extInfo['guest_id'],
            'guest_brand_id' => $data->extInfo['guest_brand_id'],
            'secondary_project_id' => $data->extInfo['secondary_project_id'],
            'guest_name' => $info['guest_name'],
            'brand_name' => $info['brand_name'],
            'project_name' => $info['project_name']
        ];
        AdPlanDayMongo::updateAll($up_data,['advertiser_id' => $data->advertiserId]);
    }
}