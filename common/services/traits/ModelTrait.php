<?php
/**
 * Created by PhpStorm.
 * author: lijin
 * Date: 2020/9/29
 * Time: 16:49
 */
namespace common\services\traits;

use common\lib\Common;
use common\lib\Constant;
use common\models\CrontabTime;
use common\models\Organize;
use common\models\UserLog;
use common\models\Users;
use common\services\FlyBookNotice;
use yii\db\ActiveQuery;

trait ModelTrait{
    public static $defaultPageSize = 15;

    /**
     * 获取分页数据
     *
     * @param ActiveQuery $query
     * @param null $sort 排序
     * @param bool $returnPageInfo 是否返回分页数据
     * @param bool $export 是否导出
     * @return array
     */

    public static function getPagingData(ActiveQuery $query, $sort = null, $returnPageInfo = true, $export = false)
    {
        $result = $query;
        $total = $query->count();
        if (!$export){
            $page = (int)\Yii::$app->getRequest()->get('page');
            if (empty($page)) {
                $page = (int)\Yii::$app->getRequest()->post('page', 1);
            }
            if ($page < 1) {
                $page = 1;
            }
            $pageSize = (int)\Yii::$app->getRequest()->get('pageSize');
            if (empty($pageSize)) {
                $pageSize = (int)\Yii::$app->getRequest()->post('pageSize', self::$defaultPageSize);
            }
            if ($pageSize < 1) {
                $pageSize = self::$defaultPageSize;
            }
        }else{
            $page = 1;
            $pageSize = 20000;
        }
        $offset = $pageSize * ($page - 1);
        $result = $result->limit((int)$pageSize)->offset($offset);
        if ($sort) {
            if(is_array($sort) && (array_key_exists('type', $sort) || array_key_exists('field', $sort))) {
                $sort = "{$sort['field']} {$sort['type']}";
            }
            $result = $result->orderBy($sort);
        }
        if (!$returnPageInfo) {
            return [
                'data' => $result->asArray()->all(),
            ];
        }
        return [
            'code' => Constant::SUCCESS_CODE,
            'message' => empty($total) ? 'data empty!' : 'ok',
            'data' => [
                'list' => $result->asArray()->all(),
                'pageInfo' => [
                    'page' => $page,
                    'pageCount' => ceil($total / $pageSize),
                    'pageSize' => $pageSize,
                    'total' => (int)$total
                ]
            ]
        ];
    }

    public static function add($data)
    {
        $query = new self();
        $query->setAttributes($data);
        $query->save();
        return $query->id;
    }

    public static function edit($id, $data)
    {
        $query = self::find()->where(['id' => $id])->one();
        $query->setAttributes($data);
        $res = $query->save();
        return $res;
    }

    public static function remove($id)
    {
        $query = self::find()->where(['id' => $id])->one();
        $res = $query->delete();
        return $res;
    }
    /**
     * 计划任务执行时间
     * author: lijin
     * @param $type
     * @param $beginTime
     * @param $endTime
     */
    public function planLog($type, $beginTime, $endTime){
        if (empty($type) || empty($beginTime) || empty($endTime)){
            return;
        }
        $useTime = $endTime - $beginTime;
        $crontab = new CrontabTime();
        $crontab->ct_type = $type;
        $crontab->create_time = date('Y-m-d H:i:s');
        $crontab->use_time = sprintf('%.2f', $useTime);
        $crontab->save($crontab);
    }

    /**
     * 记录用户请求ip
     * author: lijin
     * @param $user_id
     * @return bool
     */
    public function userLog($user_id){
        $user = new UserLog();
        $user->user_id = $user_id;
        $user->ip = $_SERVER['REMOTE_ADDR'];
        $user->date = date("Y-m-d");
        $record = UserLog::find()->where(['user_id' => $user_id, 'ip' => $user->ip, 'date' => $user->date])->count();
        if (!$record){
            $user->save($user);
            //一天超过三条ip发通知
            $ip_count = UserLog::find()->where(['user_id' => $user_id, 'date' => $user->date])->count();
            if ($ip_count > 3){
                $user_info = Users::find()->alias('a')
                    ->leftJoin(Organize::tableName(). ' as b', 'a.organize_id = b.id')
                    ->where(['user_id' => $user_id])->asArray()->one();
                if (in_array($user_info['top_two_id'], array_merge(OPTIMIZER_PART, [139, 141]))){
                    $user = new Users();
                    $user_info = $user->getUserInfoById($user_id);
                    $target_user = in_array($user_info['parentOrganize']['organizeIds'][1], [373, 149]) ? $user_info['parentOrganize']['organizeIds'][1] : $user_info['parentOrganize']['organizeIds'][2];
                    if (!empty($target_user)){
                        $notice_user = Users::find()->select('id')->where(['organize_id' => $target_user])->andWhere(['or', ['like', 'role_name', '总监'], ['like', 'role_name', '经理']])->scalar();
                        $flyBook = new FlyBookNotice();
                        $flyBook->sendFlyMsgByUid($notice_user, '多地登录提醒：'.$user_info['username'].'今日已在'.$ip_count.'处进行登录操作');
                    }
                }
            }
        }
        return true;
    }

}