<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%eligibility}}".
 *
 * @property int $id
 * @property int $candidate_type 1=>Sailor,2 =>DeSailor, 3=>Officer Cadet
 * @property int|null $candidate_designation Sailor(EDU Seaman), topas, dockyard radio , BNVRX etc
 * @property string $min_age 17.06 # 17 year 6 month,
 * @property string $max_age 20.00 # 20 year 0 month
 * @property string $dept_can_max_age departmental  candidate maax age
 * @property string|null $marital_status 1=>married,2=>unmarried
 * @property string|null $gender 1=>Male, 2=>Female
 * @property string|null $height_male
 * @property string|null $weight_male
 * @property string|null $height_female
 * @property string|null $weight_female
 * @property string|null $chest_normal_male
 * @property string|null $chest_extended_male
 * @property string|null $chest_normal_female
 * @property string|null $chest_extended_female
 * @property int|null $jsc_result 1=>Pass
 * @property string|null $ssc_result SSC GPA
 * @property string|null $ssc_ac_group SSC Academic Group
 * @property string|null $hsc_result HSC GPA
 * @property string|null $hsc_ac_group HSC Academic Group
 * @property int|null $is_required_biology 1=>Yes, 2=>No
 * @property int|null $is_allow_trade_course 1=>Yes, 2=>No
 * @property int|null $is_allow_diploma 1=>Yes, 2=>No
 * @property string|null $diploma_result Diploma Result
 * @property int|null $is_allow_hons_appeared 1=>Yes, 2=>No
 * @property string|null $hons_result Hons
 * @property int|null $is_allow_masters_appeared 1=>Yes, 2=>No
 * @property string|null $masters_result Masters Result
 * @property string|null $masters_subject
 * @property string|null $trade_course_subject trade course subject
 * @property string|null $hons_diploma_subject Hons and diploma subjects
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int $status
 * @property int $is_required_trade_course_experience
 */
class Eligibility extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%eligibility}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['candidate_type'],'unique'],
            ['candidate_designation', 'unique', 'targetAttribute' => ['candidate_designation', 'candidate_type']],
            [['candidate_type', 'candidate_designation', 'min_age', 'max_age', 'dept_can_max_age', 'marital_status', 'gender', 'jsc_result', 'is_required_biology','is_required_trade_course_experience'], 'required'],
            ///  [['min_age'],'number'],
            [['height_male', 'weight_male', 'height_female', 'weight_female', 'chest_normal_male', 'chest_extended_male', 'chest_normal_female', 'chest_extended_female', 'diploma_result'], 'number'],
            [['ssc_result', 'hsc_result', 'diploma_result'], 'number', 'max' => 5],
            [['candidate_type', 'candidate_designation', 'jsc_result', 'is_required_biology', 'is_allow_trade_course', 'is_allow_diploma', 'is_allow_hons_appeared', 'is_allow_masters_appeared', 'created_by', 'updated_by', 'status'], 'integer'],
            [['ssc_ac_group', 'hsc_ac_group', 'created_dt', 'updated_dt', 'hons_diploma_subject', 'trade_course_subject'], 'safe'],
            [['min_age', 'max_age', 'dept_can_max_age', 'height_male', 'weight_male', 'height_female', 'weight_female', 'chest_normal_male', 'chest_extended_male', 'chest_normal_female', 'chest_extended_female', 'ssc_result', 'hsc_result', 'diploma_result', 'hons_result', 'masters_result', 'masters_subject'], 'string', 'max' => 50],
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
            'candidate_designation' => Yii::t('app', 'Candidate Designation'),
            'min_age' => Yii::t('app', 'Min Age'),
            'max_age' => Yii::t('app', 'Max Age'),
            'dept_can_max_age' => Yii::t('app', 'Dept. candidate max age'),
            'marital_status' => Yii::t('app', 'Marital Status'),
            'gender' => Yii::t('app', 'Gender'),
            'height_male' => Yii::t('app', 'Height Male'),
            'weight_male' => Yii::t('app', 'Weight Male'),
            'height_female' => Yii::t('app', 'Height Female'),
            'weight_female' => Yii::t('app', 'Weight Female'),
            'chest_normal_male' => Yii::t('app', 'Chest Normal Male'),
            'chest_extended_male' => Yii::t('app', 'Chest Extended Male'),
            'chest_normal_female' => Yii::t('app', 'Chest Normal Female'),
            'chest_extended_female' => Yii::t('app', 'Chest Extended Female'),
            'jsc_result' => Yii::t('app', 'JSC Result'),
            'ssc_result' => Yii::t('app', 'SSC GPA'),
            'ssc_ac_group' => Yii::t('app', 'SSC Academic Group'),
            'hsc_result' => Yii::t('app', 'HSC GPA'),
            'hsc_ac_group' => Yii::t('app', 'HSC Academic Group'),
            'is_required_biology' => Yii::t('app', 'Is Biology Required'),
            'is_allow_trade_course' => Yii::t('app', 'Is Required Trade Course'),
            'is_allow_diploma' => Yii::t('app', 'Is Required Diploma/HSC'),
            'diploma_result' => Yii::t('app', 'HSC/Diploma Result'),
            'is_allow_hons_appeared' => Yii::t('app', '1=>Yes, 2=>No'),
            'hons_result' => Yii::t('app', 'Hons'),
            'is_allow_masters_appeared' => Yii::t('app', '1=>Yes, 2=>No'),
            'masters_result' => Yii::t('app', 'Masters Result'),
            'masters_subject' => Yii::t('app', 'Masters Subject'),
            'trade_course_subject' => Yii::t('app', 'Assign Trade Course'),
            'hons_diploma_subject' => Yii::t('app', 'Assign Diploma Subjects'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Date'),
            'updated_dt' => Yii::t('app', 'Updated Date'),
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
     * Gets query for [[Candidate designation]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCanDesignation()
    {
        return $this->hasOne(CanDesignation::class, ['id' => 'candidate_designation']);
    }


    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {

        // implode array for store 
        foreach (self::implodeFieldList() as $key => $field) {
            if (array_key_exists($field, $this->attributes) && is_array($this->attributes[$field])) {
                $implode  = implode(',', $this->attributes[$field]);
                $this->$field = $implode;
            }
        }

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
     * some fields need to implode for db insert    
     */
    public static function implodeFieldList()
    {
        return ['marital_status', 'gender', 'ssc_ac_group', 'hsc_ac_group', 'hons_diploma_subject', 'trade_course_subject'];
    }

    /**
     * Eligibility info by designation  
     */

    public static function eligibilityBySession(int $id)
    {
        // $session = Yii::$app->session;
        // if ($session->has('eligibilitySession')) {
        //     $eligibility = $session->get('eligibilitySession');
        //     return $eligibility[$id];
        // }
        $model = self::find()
            ->select([
                'id', 'candidate_type', 'candidate_designation', 'min_age', 'max_age', 'dept_can_max_age',
                'height_male', 'weight_male', 'ssc_result', 'ssc_ac_group','trade_course_subject','hons_diploma_subject'
            ])
            ->where(['candidate_designation' => $id])
            ->asArray()
            ->one();
        /// $session->set('eligibilitySession', [$id => $model]);
        return $model;
    }
}
