<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%action}}".
 *
 * @property int $id
 * @property string $path 操作路径
 * @property string $name 操作名称
 * @property int $action_type 菜单类型 1 一级菜单 2 二级菜单 3 操作 4 页面按钮
 * @property int|null $pid 父操作
 * @property string|null $icon 菜单图标
 * @property string|null $soft 排序
 */
class Action extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%action}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'action_type','pid'], 'required'],
            [['action_type', 'pid'], 'integer'],
            [['path', 'name', 'icon', 'soft'], 'string', 'max' => 50],
        ];
    }

    /**
     * @param $id
     * @param $interIds
     * 绑定操作接口权限
     */
    public function addRelations($id,$interIds){
        if(!$id){
            return false;
        }
        if(empty($interIds)){
            return false;
        }
        //清除之前绑定，重新添加
        ActionToInterface::deleteAll(['aid'=>$id]);

        $model = new ActionToInterface();
        $data = [];
        foreach($interIds as $k=>$v){
             $data[$k]['aid']=$id;
             $data[$k]['iid']=$v;
        }
        foreach($data as $k=>$v){
            $_model = clone $model;
            $_model->setAttributes($v);
            $_model->save();
        }
        return true;
    }

    /**
     * 查询当前菜单或多个菜单下绑定的接口
     */
    public function getBindInterface($id){
        if(is_array($id)){
            $where=['in','aid',$id];
        }else{
            $where=['aid'=>$id];
        }
        $data = ActionToInterface::find()->where($where)->asArray()->all();
        $ids  = array_column($data,'iid');
        return $ids;
    }

    /**
     *  删除操作
     *  $type ==1 $id 是操作的ID  $type == 2 $id 是接口的ID
     */
    public function delRelation($id,$type){
        if($type==1){
            $where = ['aid'=>$id];
        }else{
            $where =['iid'=>$id];
        }
        $status = ActionToInterface::deleteAll($where);
        if(is_numeric($status)){
            return true;
        }
        return false;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'path' => '操作路径',
            'name' => '操作名称',
            'action_type' => '操作类型',
            'pid' => '上级ID',
            'icon' => '菜单Icon',
            'soft' => '排序',
        ];
    }
}
