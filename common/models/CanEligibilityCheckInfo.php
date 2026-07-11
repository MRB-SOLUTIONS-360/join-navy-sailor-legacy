<?php

namespace common\models;

use common\static\Constants;
use Yii;

/**
 * This is the model class for table "{{%can_eligibility_check_info}}".
 *
 * @property int $id
 * @property int $candidate_type general, posso kota, departmental candidate
 * @property string|null $eligible_dept Candidate Eligible Department
 * @property string|null $apply_department Apply department
 * @property int $apply_department_type Sailor/artificer/dockyard
 * @property string|null $p_o_no
 * @property string|null $rank
 * @property string $dob
 * @property int $gender 1=>Male, 2=>Female
 * @property string|null $nationality
 * @property int|null $marital_status 1=>Married, 2=>Unmarried
 * @property string|null $height
 * @property string|null $chest_normal
 * @property string|null $chest_expanded
 * @property int|null $eye_status 1=>6/6, 2=>6/12
 * @property string $district
 * @property int|null $jsc_result 1=>Pass, 2=>Fail
 * @property int|null $ssc_equv_academic_type 1=>SSC,3=>O level
 * @property string|null $ssc_equv_result ssc / equivalent result
 * @property string|null $ssc_equv_group science/business/human/ vocational
 * @property int|null $ssc_equv_is_biology_include 1=>Yes, 2=>No
 * @property int|null $ssc_equv_is_trade_course_complete 1=>Yes, 2=>No #Trade Course Completed(Minimum 6 Months)?
 * @property string|null $trade_course_subject trade course subject id
 * @property string|null $have_trade_course_experience have_trade_course_experience
 * @property int|null $hsc_equv_academic_type 1=>HSC, 2=>Diploma,3=> A level
 * @property string|null $hsc_equv_result ssc / equivalent/ diploma result
 * @property string|null $hsc_equv_group if hsc then ac group , if diploma  then academic department
 * @property int|null $hsc_equv_is_biology_include 1=>Yes, 2=>No
 * @property int|null $is_honours_appeared 1=>Yes, 2=>No
 * @property string|null $honours_result
 * @property string|null $honours_subject
 * @property string|null $honours_college college / university name
 * @property int|null $is_masters_appeared 1=>Yes, 2=>No
 * @property string|null $masters_result
 * @property string|null $masters_subject
 * @property string|null $masters_college college / university name
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int $status 1=>Active, 2=>Inactive
 * @property int $height_feet
 * @property int $height_inch
 */
class CanEligibilityCheckInfo extends \yii\db\ActiveRecord
{

    const SCENARIO_PERSONAL_INFO = 'personal_info';
    const SCENARIO_ACADEMIC_INFO = 'academic_info';

    public $height_feet;
    public $height_inch;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%can_eligibility_check_info}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['candidate_type', 'dob', 'gender', 'district', 'height_feet', 'height_inch', 'marital_status'], 'required', 'on' => self::SCENARIO_PERSONAL_INFO],
            [['p_o_no', 'rank'], 'validatePoNoRank', 'skipOnError' => false, 'skipOnEmpty' => false],
            [['p_o_no', 'rank'], 'validateEnglishInput', 'skipOnError' => false, 'skipOnEmpty' => false],
            ['height_feet', 'integer', 'min' => 5, 'max' => 7],
            ['height_inch', 'integer', 'min' => 0, 'max' => 11],
            [['chest_normal', 'chest_expanded'], 'integer', 'min' => 25, 'max' => 38],
            // ACAdemic info validation
            [['jsc_result'], 'required', 'on' => self::SCENARIO_ACADEMIC_INFO],
            // [['chest_normal'], 'number','numberPattern' => '/^\d+(.\d{1,2})?$/'],

            ['ssc_equv_result', 'number', 'min' => 2.5, 'max' => 5],

            ['ssc_equv_result', 'validateSscEquvResult', 'skipOnError' => false, 'skipOnEmpty' => false],
            ['ssc_equv_group', 'validateSscEquvGroup', 'skipOnError' => false, 'skipOnEmpty' => false],
            ['trade_course_subject', 'validateTradeCourseSubject', 'skipOnError' => false, 'skipOnEmpty' => false],
            ['have_trade_course_experience', 'validateHaveTradeCourseExperience', 'skipOnError' => false, 'skipOnEmpty' => false],
            ['ssc_equv_is_biology_include', 'validateBiologyIsInclude', 'skipOnError' => false, 'skipOnEmpty' => false],
            ['ssc_equv_is_trade_course_complete', 'validateTradeCourseComplete', 'skipOnError' => false, 'skipOnEmpty' => false],
            ///'ssc_equv_is_trade_course_complete'
            [['candidate_type','apply_department_type', 'gender', 'marital_status', 'eye_status', 'jsc_result', 'ssc_equv_academic_type', 'ssc_equv_is_biology_include', 'ssc_equv_is_trade_course_complete', 'hsc_equv_academic_type', 'hsc_equv_is_biology_include', 'is_honours_appeared', 'is_masters_appeared', 'created_by', 'updated_by', 'status'], 'integer'],
            [['dob', 'created_dt', 'updated_dt', 'height_feet', 'height_inch','have_trade_course_experience'], 'safe'],
            [['p_o_no', 'rank'], 'string', 'max' => 200],
            [['nationality'], 'string', 'max' => 100],
            ['height', 'string', 'max' => 100],
            [['eligible_dept', 'apply_department', 'trade_course_subject', 'chest_normal', 'chest_expanded', 'ssc_equv_result', 'ssc_equv_group', 'hsc_equv_result', 'hsc_equv_group', 'honours_result', 'honours_subject', 'masters_result', 'masters_subject'], 'string', 'max' => 50],
            [['district'], 'string', 'max' => 150],
            [['honours_college', 'masters_college'], 'string', 'max' => 255],
        ];
    }

    public function validatePoNoRank($attribute, $params, $validator)
    {
        if (empty($this->$attribute) && in_array($this->candidate_type, [Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA, Constants::ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL])) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
        }
        return true;
    }
    public function validateEnglishInput($attribute, $params, $validator)
    {
        if (!empty($this->$attribute) && !preg_match("#^[a-zA-Z0-9 .@()\&\-\,\/]+$#", $this->$attribute)) {
            $this->addError($attribute, 'ইনপুট অবশ্যই ইংরেজিতে হতে হবে।'); //সাংকেতিক চিহ্ন গ্রহণযোগ্য নয়।
        }
        return true;
    }

    public function validateSscEquvResult($attribute, $params, $validator)
    {
        if ($this->ssc_equv_group && empty($this->ssc_equv_result)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
        }
        return true;
    }

    public function validateSscEquvGroup($attribute, $params, $validator)
    {
        if ($this->ssc_equv_result && empty($this->ssc_equv_group)) {
            $this->addError($attribute,  $this->getAttributeLabel($attribute) . ' cannot be blank.');
        }
        return true;
    }

    public function validateBiologyIsInclude($attribute, $params, $validator)
    {
        if (($this->ssc_equv_result || $this->ssc_equv_group) && in_array($this->ssc_equv_group, [Constants::AC_GROUP_SCIENCE, Constants::AC_GROUP_VOCATIONAL])  && empty($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
        }
        return true;
    }

    public function validateTradeCourseComplete($attribute, $params, $validator)
    {
        if (($this->ssc_equv_result || $this->ssc_equv_group) &&  empty($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
        }
        return true;
    }

    public function validateTradeCourseSubject($attribute, $params, $validator)
    {
        if ($this->ssc_equv_is_trade_course_complete && $this->ssc_equv_is_trade_course_complete == Constants::YES   && empty($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
        }
        return true;
    }
    public function validateHaveTradeCourseExperience($attribute, $params, $validator)
    {
        if ($this->ssc_equv_is_trade_course_complete && $this->ssc_equv_is_trade_course_complete == Constants::YES  && empty($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' cannot be blank.');
        }
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'candidate_type' => Yii::t('app', 'Candidate Type'),
            'eligible_dept' => Yii::t('app', 'Eligible Dept'),
            'apply_department' => Yii::t('app', 'Apply Department'),
            'apply_department_type' => Yii::t('app', 'Apply Department Type'),
            'p_o_no' => Yii::t('app', 'P No / O No'),
            'rank' => Yii::t('app', 'Rank'),
            'dob' => Yii::t('app', 'Date of Birth'),
            'gender' => Yii::t('app', 'Gender'),
            'nationality' => Yii::t('app', 'Nationality'),
            'marital_status' => Yii::t('app', 'Marital Status'),
            'height' => Yii::t('app', 'Height'),
            'height_feet' => Yii::t('app', 'Height Feet'),
            'height_inch' => Yii::t('app', 'Height Inch'),
            'chest_normal' => Yii::t('app', 'Chest Normal'),
            'chest_expanded' => Yii::t('app', 'Chest Expanded'),
            'eye_status' => Yii::t('app', 'Eye Power'),
            'district' => Yii::t('app', 'District'),
            'jsc_result' => Yii::t('app', 'JSC Result'),
            'ssc_equv_academic_type' => Yii::t('app', 'SSC Academic Type'),
            'ssc_equv_result' => Yii::t('app', 'SSC Result'),
            'ssc_equv_group' => Yii::t('app', 'SSC Group'),
            'ssc_equv_is_biology_include' => Yii::t('app', 'SSC Equv Is Biology Include'),
            'ssc_equv_is_trade_course_complete' => Yii::t('app', 'SSC Equv Is Trade Course Complete'),
            'trade_course_subject' => Yii::t('app', 'Trade Course'),
            'hsc_equv_academic_type' => Yii::t('app', 'HSC Academic Type'),
            'hsc_equv_result' => Yii::t('app', 'HSC Result'),
            'hsc_equv_group' => Yii::t('app', 'HSC Group'),
            'hsc_equv_is_biology_include' => Yii::t('app', 'HSC Equv Is Biology Include'),
            'is_honours_appeared' => Yii::t('app', 'Is Honours Appeared'),
            'honours_result' => Yii::t('app', 'Honours Result'),
            'honours_subject' => Yii::t('app', 'Honours Subject'),
            'honours_college' => Yii::t('app', 'Honours College'),
            'is_masters_appeared' => Yii::t('app', 'Is Masters Appeared'),
            'masters_result' => Yii::t('app', 'Masters Result'),
            'masters_subject' => Yii::t('app', 'Masters Subject'),
            'masters_college' => Yii::t('app', 'Masters College'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Dt'),
            'updated_dt' => Yii::t('app', 'Updated Dt'),
            'status' => Yii::t('app', 'Status'),
            'have_trade_course_experience' => Yii::t('app', 'Do You have trade course work experience'),
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->status = Constants::STATUS_ACTIVE;
                $this->created_by = (isset(Yii::$app->user->identity->id)) ? Yii::$app->user->identity->id : 1;
                $this->created_dt = date('Y-m-d H:i:s');
            } else {
                $this->updated_by = (isset(Yii::$app->user->identity->id)) ? Yii::$app->user->identity->id : 1;
                $this->updated_dt = date('Y-m-d H:i:s');
            }
            return true;
        } else return false;
    }
}
