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
        }
        return true;
    }

}