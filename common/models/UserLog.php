<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "tab_user_log".
 *
 * @property int $int
 * @property int|null $user_id
 * @property string $ip
 * @property string $date
 * @property string|null $create_time
 * @property string|null $update_time
 */
class UserLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'tab_user_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id'], 'integer'],
            [['create_time', 'update_time'], 'safe'],
            [['ip', 'date'], 'string', 'max' => 32],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'int' => 'Int',
            'user_id' => 'User ID',
            'ip' => 'Ip',
            'date' => 'Date',
            'create_time' => 'Create Time',
            'update_time' => 'Update Time',
        ];
    }
}
