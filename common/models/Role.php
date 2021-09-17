<?php

namespace common\models;

use Yii;
use common\models\RoleToAction;
use yii\behaviors\TimestampBehavior;
/**
 * This is the model class for table "{{%role}}".
 *
 * @property int $id
 * @property string $role_name 角色名称
 * @property int|null $sort 排序，为了更好的展示角色数据
 */
class Role extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_name'], 'required'],
            [['sort'], 'integer'],
            [['role_name'], 'string', 'max' => 255],
            [['create_time','update_time'], 'safe'],
        ];
    }

    /**
     *  根据用户角色ID获取名称
     */
    public function  getRoleNameById($roleId){
         $data = self::findOne($roleId);
         if(!$data)
             return '';
         return $data->role_name;
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_name' => '职位',
            'sort' => '排序',
            'create_time'=>"创建时间",
            'update_time'=>"修改时间"
        ];
    }

    /**
     * 添加角色和 菜单的关联关系
     */
    public function addRelation($rid,$aids){
        if(!is_array($aids)){
            $aids = explode(",",$aids);
        }
       $isDel =  RoleToAction::deleteAll(['rid'=>$rid]);
       if(is_numeric($isDel)){
           $data = [];
           foreach($aids as $k=>$v){
               $data[$k]=$v;
               $aData = Action::find()->where(['and',['id'=>$v],["!=",'pid',0]])->asArray()->one();
               if(!empty($aData)){
                   if(!in_array($aData['pid'],$aids)){
                       $data[count($aids)+$k]=$aData['pid'];

                       $bData =  Action::find()->where(['and',['id'=>$aData['pid']],["!=",'pid',0]])->asArray()->one();
                       if(!empty($bData)){
                           if(!in_array($bData['pid'],$aids)) {
                               $data[999 - $k] = $bData['pid'];
                           }
                       }

                   }
               }
           }
           $aData = array_unique($data);
           $data =[];
           foreach($aData as $k=>$v){
               $data[$k]['rid']=$rid;
               $data[$k]['aid']=$v;
           }
           $model = new RoleToAction();
           foreach($data as $v)
           {
               $_model = clone $model;
               $_model->setAttributes($v);
               $_model->save();
           }
           return true;
       }
       return false;
    }

    /**
     * 删除角色下面绑定的菜单
     * @param $rid
     */
    public function delRelation($rid){
        if(!$rid) return false;
        $isDel =  RoleToAction::deleteAll(['rid'=>$rid]);
        if(is_numeric($isDel)){
            return true;
        }else{
            return false;
        }
    }

    /**
     *  获取某用户下绑定的菜单
     * @param $uid
     */
     public function getMenuByRid($rid){
         if(!$rid)
             return [];
         $menu = (new RoleToAction())->getMenuByRid($rid);
         return $menu;
     }

     /**
      * 验证角色是否可以访问这个菜单
      */
     public function  roleAuth($rid,$iid){
         $aids = (new RoleToAction())->getMenuByRid($rid,1);
         $where = ['and',['in','aid',$aids],['iid'=>$iid]];
         $cnt = ActionToInterface::find()->where($where)->count();
         if($cnt>0){
             return true;
         }
         return false;
     }

}
