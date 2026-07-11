<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%send_sms}}".
 *
 * @property int $id
 * @property string $application_type
 * @property string|null $serial_no
 * @property string|null $phone_no
 * @property string|null $sms_body
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 */
class SendSms extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%send_sms}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['application_type'], 'required'],
            [['application_type'], 'string'],
            [['created_by', 'updated_by'], 'integer'],
            [['created_dt', 'updated_dt'], 'safe'],
            [['serial_no'], 'string', 'max' => 150],
            [['phone_no'], 'string', 'max' => 200],
            [['sms_body'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'application_type' => Yii::t('app', 'Application Type'),
            'serial_no' => Yii::t('app', 'Serial No'),
            'phone_no' => Yii::t('app', 'Phone No'),
            'sms_body' => Yii::t('app', 'Sms Body'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Dt'),
            'updated_dt' => Yii::t('app', 'Updated Dt'),
        ];
    }

     /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_by = (isset(Yii::$app->user->identity->id)) ? Yii::$app->user->identity->id : 1;
                $this->created_dt = date('Y-m-d H:i:s');
            } else {
                $this->updated_by = (isset(Yii::$app->user->identity->id)) ? Yii::$app->user->identity->id : 2;
                $this->updated_dt = date('Y-m-d H:i:s');
            }
            return true;
        } else return false;
    }
}
