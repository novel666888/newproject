<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%action_to_interface}}".
 *
 * @property int $id
 * @property int $aid 操作ID
 * @property int $iid 接口权限ID
 * @property string|null $create_time 创建时间
 * @property string|null $update_time 修改时间
 */
class ActionToInterface extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%action_to_interface}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['aid', 'iid'], 'required'],
            [['aid', 'iid'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'aid' => '操作ID',
            'iid' => '接口权限ID',
            'create_time' => '创建时间',
            'update_time' => '修改时间',
        ];
    }
}
