<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "{{%sailors}}".
 *
 * @property int $id
 * @property int $eligibility_info_id  
 */
class DeSailorsReference extends \yii\db\ActiveRecord
{
    const ADD_REFERENCE  = 'add_reference';
    const UPDATE_REFERENCE  = 'update_reference';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%de_sailors}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // Add reference
            [['serial_no', 'referred_by', 'relationship'], 'required', 'on' => self::ADD_REFERENCE],
            ['serial_no', 'isSerialNoExist', 'on' => self::ADD_REFERENCE, 'skipOnError' => false, 'skipOnEmpty' => false],
            ['reference_details', 'safe'],

        ];
    }

    public function isSerialNoExist($attribute, $params, $validator)
    {
        if (!empty($this->$attribute)) {
            $is_exist = self::find()->where(['serial_no' => $this->$attribute])->count();
            if ($is_exist == 0)
                $this->addError($attribute, $this->getAttributeLabel('serial_no') . ' is not available in system.');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'serial_no' => Yii::t('app', 'Serial No'),
            'referred_by' => Yii::t('app', 'Referred By'),
            'reference_details' => Yii::t('app', 'Reference Details'),
            'have_reference' => Yii::t('app', 'Have Reference'),
            'relationship' => Yii::t('app', 'Relationship'),
        ];
    }
}
