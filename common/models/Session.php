<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%session}}".
 *
 * @property int $id
 * @property string|null $session_id
 * @property int|null $expire 1=>Expired
 * @property int $user_id
 */
class Session extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%session}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['expire', 'user_id'], 'integer'],
            [['user_id'], 'required'],
            [['session_id'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'session_id' => 'Session ID',
            'expire' => 'Expire',
            'user_id' => 'User ID',
        ];
    }
}
