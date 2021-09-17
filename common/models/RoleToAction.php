<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%role_to_action}}".
 *
 * @property int $id
 * @property int $rid 关联角色ID
 * @property int $aid 关联操作ID
 */
class RoleToAction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%role_to_action}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rid', 'aid'], 'required'],
            [['rid', 'aid'], 'integer'],
        ];
    }

    /**
     * 删除某个操作时，删除角色和这个操作之间的关联关系
     */
    public function delRelationByAid($aid){
        if(!$aid)
            return false;
        $status = self::deleteAll(['aid'=>$aid]);
        if(is_numeric($status))
            return true;
        else
            return false;
    }

    /**
     *  获取某个角色下的所有操作
     */
    public function getMenuByRid($rid,$type=0){
        if(!$rid) return false;
        $aid = self::find()->select(['aid as id'])->where(['rid'=>$rid])->asArray()->all();
        if(empty($aid))
            return [];
        $ids=array_column($aid,'id');

        if($type==1)
            return $ids;

        $where=['and'];
        $where[] = ['in','id',$ids];
        $where[] = ['<','action_type',3];
        $data = Action::find()
            ->where($where)
            ->orderBy(['soft'=>SORT_ASC])
            ->asArray()
            ->all();

        $data = Organize::generateTree($data);
        return $data;
    }

    /**
     *  获取某个角色下的某个页面下的按钮
     */
    public function getButtonByRid($rid,$id){
        if(!$rid || !$id) return false;
        $aid = self::find()->select(['aid as id'])->where(['rid'=>$rid])->asArray()->all();
        if(empty($aid))
            return [];
        $ids=array_column($aid,'id');
        $data = Action::find()->select("id,soft,pid")->where(['and',['in','id',$ids],['pid'=>$id]])->orderBy(['soft'=>SORT_ASC])->asArray()->all();
        return array_column($data,'id');
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'rid' => 'Rid',
            'aid' => 'Aid',
        ];
    }
}
