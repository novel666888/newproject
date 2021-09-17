<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%user_to_role}}".
 *
 * @property int $id
 * @property int $uid 关联用户id
 * @property int $rid 关联权限ID
 */
class UserToRole extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user_to_role}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['uid', 'rid'], 'required'],
            [['uid', 'rid'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'rid' => 'Rid',
        ];
    }
}
