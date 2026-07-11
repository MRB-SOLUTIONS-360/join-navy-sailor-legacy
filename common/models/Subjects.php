<?php

namespace common\models;

use common\static\Constants;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%subjects}}".
 *
 * @property int $id
 * @property int $candidate_type 1=>Sailor,2 =>DeSailor, 3=>Officer Cadet
 * @property int $subject_type 1=>Diploma , 2 =>Trade
 * @property string $name_en
 * @property string|null $name_bn
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int $status 1=>Active, 2=>Inactive
 */
class Subjects extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%subjects}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['candidate_type', 'subject_type', 'name_en'], 'required'],
            [['name_en', 'name_bn'], 'filter', 'filter' => 'strip_tags'],
            [['candidate_type', 'subject_type', 'created_by', 'updated_by', 'status'], 'integer'],
            [['created_dt', 'updated_dt'], 'safe'],
            [['name_en', 'name_bn'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'candidate_type' => Yii::t('app', 'Candidate Type'),
            'subject_type' => Yii::t('app', 'Subject Type'),
            'name_en' => Yii::t('app', 'Name English'),
            'name_bn' => Yii::t('app', 'Name Bangla'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Dt'),
            'updated_dt' => Yii::t('app', 'Updated Dt'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
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

    /**
     * All active subject.All active designation for dropdown
     * @param type array      
     */
    public static function getAllActiveSubject(int $isActive = Constants::STATUS_ACTIVE)
    {
        $model = self::find()
            ->where('status =:status', [':status' => $isActive])
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'name_en');
    }


    /**
     * All subject in system for dropdown filter 
     * @param type array      
     */
    public static function getAllSubject()
    {
        $model = self::find()
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'name_en');
    }
    /**
     * All subject in system for dropdown filter 
     * @param type array      
     */
    public static function getAllActiveSubjectBySubjectType(int $candidate_type = null, int $subject_type = null)
    {
        return Subjects::find()
            ->select(['id', 'name_en'])
            ->where(['status' => Constants::STATUS_ACTIVE])
            ->andFilterWhere(['candidate_type' => $candidate_type])
            ->andFilterWhere(['subject_type' => $subject_type])
            ->orderBy('name_en ASC')
            ->asArray()
            ->all();
    }


    /**
     * All subject in system for dropdown filter 
     * @param type array      
     */
    public static function getAllActiveSubjectByIdIn(array $ids = array())
    {
        return Subjects::find()
            ->select(['id', 'name_en'])
            ->where(['status' => Constants::STATUS_ACTIVE])
            ->andFilterWhere(['in', 'id', $ids])
            ->orderBy('name_en ASC')
            ->asArray()
            ->all();
    }


    public static function modelToDropdown(array $model = array(), $value = 'id', $name = 'name_en')
    {
        return  ArrayHelper::map($model, $value, $name);
    }


    /**
     * All subject in system for dropdown filter 
     * @param type array      
     */
    public static function subjectFindById($id)
    {
        return self::find()
            ->where(['id' => $id])
            ->asArray()
            ->one();
    }
}
