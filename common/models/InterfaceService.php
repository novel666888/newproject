<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%interface_service}}".
 *
 * @property int $id
 * @property string $interface_name 接口名字
 * @property string $interface_path 接口路径
 * @property int $is_verification 1公共接口跳过验证 0需要验证
 * @property string|null $create_time 创建时间
 * @property string|null $update_time 修改时间
 */
class InterfaceService extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%interface_service}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['interface_name','interface_path', 'is_verification'], 'required'],
            [['is_verification'], 'integer'],
            [['interface_name'], 'unique'],
            [['create_time', 'update_time'], 'safe'],
            [['interface_name'], 'string', 'max' => 50],
            [['interface_path'], 'string', 'max' => 255],
        ];
    }

    /**
     * @param $pathInfo
     * 验证菜单数据
     */
    public function validateAuth($pathInfo){
        $where = ['interface_path'=>$pathInfo];
        $data = self::find()->where($where)->asArray()->one();
        if(empty($data))
            return false;
        if($data['is_verification']==1)
            return true;
        return $data['id'];
    }
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'interface_name' => '接口名字',
            'interface_path' => '接口路径',
            'is_verification' => '是否需要验证',
            'create_time' => '创建时间',
            'update_time' => '修改时间',
        ];
    }
}
