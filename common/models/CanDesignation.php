<?php

namespace common\models;

use common\static\Constants;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\HtmlPurifier;


/**
 * This is the model class for table "{{%can_designation}}".
 *
 * @property int $id
 * @property int $candidate_type 1=>Sailor,2 =>DeSailor, 3=>Officer Cadet
 * @property string $name_bn
 * @property string|null $name_en
 * @property string|null $description
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int $status 1=>Active, 2=>Inactive
 */
class CanDesignation extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%can_designation}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['candidate_type', 'name_en'], 'required'],
            [['name_en', 'name_bn'], 'filter', 'filter' => 'strip_tags'],
            ['description', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value);
            }],
            // ['description', 'filter', 'filter' => 'htmlspecialchars'],
            [['candidate_type', 'created_by', 'updated_by', 'status'], 'integer'],
            [['created_dt', 'updated_dt', 'description'], 'safe'],
            [['name_bn', 'name_en'], 'string', 'max' => 255],
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
            'name_bn' => Yii::t('app', 'Name Bangla'),
            'name_en' => Yii::t('app', 'Name English'),
            'description' => Yii::t('app', 'Description'),
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
     * @param type get All active designation for dropdown     
     */
    public static function getAllActiveDesignation(int $isActive = Constants::STATUS_ACTIVE, int $type = null)
    {
        $model = self::find()
            ->where('status =:status', [':status' => $isActive])
            ->andFilterWhere(['candidate_type' => $type])
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'name_en');
    }


    /**
     * All designation in system for dropdown filter 
     * @param type array      
     */
    public static function getAllDesignation(int $type = null)
    {
        $model = self::find()
            ->andFilterWhere(['candidate_type' => $type])

            ->orderBy('name_en ASC')
            ->all();

        return   ArrayHelper::map($model, 'id', 'name_en');
    }

    /**
     * All designation in system for dropdown filter 
     * @param type array      
     */
    public static function getAllDesignationForEligibilityPage(int $type = null)
    {
        $model = self::find()
            ->select(['id', 'name_en', 'description'])
            ->andFilterWhere(['candidate_type' => $type])
            ->orderBy('name_en ASC')
            ->asArray()
            ->all();

        $return_data = [];
        foreach ($model as $k => $val)
            $return_data[$val['id']] = $val;
        return $return_data;
    }


    /**
     * All designation set session
     * @param type array      
     */
    public static function getAllDesignationSession(int $id)
    {
        $session = Yii::$app->session;
        if ($session->has('designationSession')) {
            $designation = $session->get('designationSession');
            if (isset($designation[$id])) {
                return $designation[$id];
            }
            
        }
        $model = self::find()
            ->orderBy('name_en ASC')
            ->all();
        $designation = ArrayHelper::map($model, 'id', 'name_en');
        $session->set('designationSession', $designation);

        return  $designation[$id];
    }
}
