<?php

namespace common\models;

use Yii;
/**
 * This is the model class for table "{{%users}}".
 * 用户表 处理用户操作
 * @property int $id
 * @property string $username 用户名
 * @property string $password 密码
 * @property int $sex 性别
 * @property string $email 邮箱
 * @property string $phone 手机号
 * @property string|null $token 用户token
 * @property string|null $role_id 冗余所属职位ID
 * @property string|null $role_name 冗余所属职位名称
 * @property int|null $organize_id 缓冗余所在组织架构ID
 * @property string|null $organize_name 冗余所在组织架构名称
 * @property int|null $parent_organize_id 缓冗余所属组织架构ID
 * @property string|null $parent_organize_name 冗余所属组织架构名称
 * @property int $worked 1在职 2离职
 * @property string|null $create_time 创建时间
 * @property string|null $departure_time 离职时间
 * @property string|null $update_time 修改时间
 * @property int|null $last_login_time 最后登录时间
 * @property int|null $cooperation_organize_id 协作者某组织架构ID
 * @property string|null $cooperation_organize_name 协作者某组织架构name
 * @property string|null $slat 用于加密密码
 * @property string|null $fly_user_id 飞书用户ID
 * @property string|null $fly_open_id 飞书openid
 */
class Users extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%users}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username','password', 'role_id','phone','slat'], 'required'],
            [['sex','worked'], 'default',"value"=>1],
            [['phone','email','fly_user_id','fly_open_id'], 'unique'],
            [['sex', 'organize_id', 'worked', 'role_id', 'last_login_time', 'cooperation_organize_id','parent_organize_id'], 'integer'],
            [['create_time', 'departure_time', 'update_time'], 'safe'],
            [['username', 'token', 'role_name', 'organize_name', 'cooperation_organize_name','parent_organize_name'], 'string', 'max' => 255],
            [['password'], 'string', 'max' => 32],
            [['email'], 'string', 'max' => 50],
            [['phone'], 'string', 'max' => 13],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => '用户名',
            'password' => '密码',
            'sex' => '性别',
            'email' => '邮箱',
            'phone' => '手机号',
            'token' => 'token',
            'role_id' => '角色ID',
            'role_name' => '角色名称',
            'organize_id' => '组织架构ID',
            'organize_name' => '组织架构名称',
            'parent_organize_id'=>'所属部门',
            'parent_organize_name'=>"所属部门",
            'worked' => '是否在职 1 在职 2 离职',
            'create_time' => '创建时间',
            'departure_time' => '离职时间',
            'update_time' => '最后更新时间',
            'last_login_time' => '最后登录时间',
            'cooperation_organize_id' => '协作者组织架构ID',
            'cooperation_organize_name' => '协作者组织架构名称',
            'slat'=>'密码加密',
            'fly_user_id'=>'飞书用户ID',
            'fly_open_id'=>'飞书openid',
        ];
    }

    /**
     * 根据uids 获取 飞书的 uids
     * @param $uids
     * @return array
     */
    public function getFlyUserIdByUids($uids){
        $userData = self::find()->where(['and',['in','id',$uids],'worked'=>1])->asArray()->all();
        $flyUserIds = array_column($userData,"fly_user_id");
        return array_filter($flyUserIds);
    }

    /**
     * 根据uids 获取 飞书的 uids
     * @param $uid
     * @return array
     */
    public function getFlyUserIdByUid($uid){
        $userData = self::find()->where(['and',['id'=>$uid],['worked'=>1]])->asArray()->one();
        if(empty($userData)){
            return [];
        }
        return $userData['fly_user_id'];
    }

    /**
     *  根据用户ID获取用户名字
     */
    public function getUserNameById($id)
    {
        $userData = self::find()->select("id,username")->where(['id' => $id])->asArray()->one();
        if (empty($userData)) {
            return false;
        }
        return $userData['username'];
    }
    /**
     *  根据用户ID获取用户详情
     */
    public function getUserInfoById($id, $worked=1){
        $userData = self::find()->select("id,username,sex,email,phone,password,fly_user_id,fly_open_id,role_id,role_name,organize_id,organize_name,parent_organize_id,parent_organize_name,worked,create_time,departure_time,update_time,slat")->where(['id'=>$id])->asArray()->one();
        if(empty($userData)){
              return [];
        }
        $userData['parentOrganize']=(new Organize())->getOrganizeNameById($userData['organize_id'],1);
        $userData['sonOrganize']=(new Organize())->getOrganizeNameById($userData['organize_id'],2);
        $userData['authUid']=$this->getAuthUserIdByUserInfo($userData, $worked);
        return $userData;
    }

    /**
     *  获取可查看的成员数据
     * @param $userData
     * @return array|mixed
     */
    public function getAuthUserIdByUserInfo($userData, $worked){
        if(!isset($userData['sonOrganize']['organizeIds'][0])){
            return $userData['id'];
        }
        $authIds = $userData['sonOrganize']['organizeIds'][0];
        $cnt = count($authIds);
        if($cnt>1){
             $where=['and'];
             $where[]=['in','organize_id',$authIds];
             $worked && $where[]=['worked'=>1];
             $uids = self::find()->select("id,organize_id")->where($where)->asArray()->all();
             return array_column($uids,'id');
        }else{
            return $userData['id'];
        }
    }
    /**
     *  获取组织架构
     */
    public function getOrganizeInfoByUserId($userId){
        $uData = self::find()->select('id,organize_id,username,phone')
            ->where(['id'=>$userId])
            ->asArray()
            ->one();
        if(empty($uData))
            return [];
        $oData = (new Organize())->getOrganizeInfoByOrganizeId($uData['organize_id']);
        $oData['username'] = $uData['username'];
        $oData['phone'] = $uData['phone'];
        $oData['id'] = $uData['id'];
        return $oData;
    }

    /**
     *  根据组织架构ID获取用户信息
     */
    public function getUserDataByOrganizeId($organizeId){
        $where = ['and'];
        $where[]=['organize_id'=>$organizeId];
        $where[]=['not like','role_name','助理'];
        $where[]=['worked'=>1];
        $uData = self::find()
            ->select('id,organize_id,username,phone')
            ->where($where)
            ->asArray()
            ->one();
        return $uData;
    }

    /**
     *  根据用户ID获取用户详情
     * @param $roleId 角色ID
     * @param $type 1 获取用户信息，2获取用户数量
     */
    public function getUserInfoByRoleId($roleId,$type=1){
        if($type==1){
            $userData = self::find()->select("id,username,phone,role_id,role_name,organize_id,organize_name,create_time,worked")->where(['role_id'=>$roleId])->asArray()->all();
            if(empty($userData)){
                return [];
            }
            return $userData;
        }else{
            $userCnt = self::find()->where(['role_id'=>$roleId])->count();
            return $userCnt;
        }
    }
    /**
     *  获取某个组织架构下面的用户
     * @param $organizeId 组织架构ID
     * @param $type 1 获取用户信息，2获取用户数量
     */
    public function getUserInfoByOrganizeId($organizeId,$type=1){
        if($type==1){
            $where = [
                'and',
                ['in','organize_id',$organizeId],
                ['worked'=>1]
            ];
            $userData = self::find()->select("id,username,phone,role_id,role_name,organize_id,organize_name,create_time,worked")->where($where)->asArray()->all();
            if(empty($userData)){
                return [];
            }
            return $userData;
        }else{
            $userCnt = self::find()->where(['organize_id'=>$organizeId,'worked'=>1])->count();
            return $userCnt;
        }
    }

    /**
     *  获取某个角色下的用户数量
     */
    public function getUserByRoleId($rid){
        return self::find()->where(['role_id'=>$rid])->count();
    }

    public function getUserIdByName($userName){
        $userName = trim($userName);
        $user = self::findOne(['username'=>$userName]);
        if($user){
            return $user->id;
        }
        return false;
    }

}
