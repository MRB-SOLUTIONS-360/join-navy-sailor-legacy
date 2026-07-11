<?php

namespace common\models;

use common\static\Constants;
use common\static\StaticMethod;
use Yii;

/**
 * This is the model class for table "{{%de_sailors}}".
 *
 * @property int $id
 * @property int $eligibility_info_id
 * @property string $app_unique_id Generate when apply
 * @property int|null $candidate_designation come from can designation
 * @property int $candidate_type 2=>Artificer 3=>Dockyard
 * @property int|null $center_id
 * @property int|null $batch_id
 * @property int|null $batch_config_id
 * @property int|null $exam_date_id
 * @property string|null $exam_date
 * @property int|null $exam_group Exam group from configuration table
 * @property string|null $serial_generate_date
 * @property string|null $serial_no
 * @property string|null $eligible_district
 * @property string|null $district
 * @property string|null $name
 * @property string|null $father_name
 * @property string|null $father_nid
 * @property string|null $father_occupation
 * @property float|null $father_income
 * @property string|null $mother_name
 * @property string|null $mother_occupation
 * @property string|null $current_village
 * @property string|null $current_word_no
 * @property string|null $current_union
 * @property string|null $current_post_office
 * @property string|null $current_thana
 * @property string|null $current_post_code
 * @property string|null $current_district
 * @property string|null $current_phone
 * @property string|null $permanent_village
 * @property string|null $permanent_union
 * @property string|null $permanent_word_no
 * @property string|null $permanent_post_office
 * @property string|null $permanent_thana
 * @property string|null $permanent_district
 * @property string|null $permanent_post_code
 * @property string|null $permanent_phone
 * @property string|null $guardian_name
 * @property string|null $guardian_relation
 * @property string|null $guardian_occupation
 * @property string|null $guardian_address
 * @property string|null $dob
 * @property string|null $age_according_to_circular
 * @property int|null $religion
 * @property int|null $gender
 * @property int|null $marital_status
 * @property string|null $nationality
 * @property string|null $photo
 * @property string|null $qr_photo
 * @property string|null $jsc_reg_no
 * @property string|null $jsc_institute_name
 * @property string|null $jsc_passing_year
 * @property string|null $jsc_gpa
 * @property int|null $ac_type_ssc 1=>মাধ্যমিক
 * @property string|null $ssc_institute
 * @property string|null $ssc_group
 * @property string|null $ssc_edu_board
 * @property string|null $ssc_reg_no
 * @property string|null $ssc_roll_no
 * @property string|null $ssc_passing_year
 * @property string|null $ssc_additional_subject
 * @property string|null $ssc_gpa
 * @property int|null $hsc_or_diploma
 * @property string|null $hsc_dip_institute
 * @property string|null $hsc_dip_group
 * @property string|null $hsc_dip_board
 * @property string|null $hsc_dip_reg_no
 * @property string|null $hsc_dip_roll_no
 * @property string|null $hsc_dip_passing_year
 * @property string|null $hsc_dip_additional_subject
 * @property string|null $hsc_dip_gpa
 * @property string|null $ssc_edu_data
 * @property string|null $hsc_edu_data
 * @property string|null $diploma_trade_institute
 * @property int|null $diploma_trade_course
 * @property string|null $diploma_trade_registration_roll
 * @property string|null $diploma_trade_gpa
 * @property string|null $ssc_teletalk_data
 * @property string|null $hsc_teletalk_data
 * @property string|null $experience_one_institute
 * @property string|null $experience_one_subject
 * @property string|null $experience_one_year
 * @property string|null $experience_one_cert_name
 * @property string|null $experience_two_institute
 * @property string|null $experience_two_subject
 * @property string|null $experience_two_year
 * @property string|null $experience_two_cert_name
 * @property string|null $experience_three_institute
 * @property string|null $experience_three_subject
 * @property string|null $experience_three_year
 * @property string|null $experience_three_cert_name
 * @property string|null $experience_four_institute
 * @property string|null $experience_four_subject
 * @property string|null $experience_four_year
 * @property string|null $experience_four_cert_name
 * @property int|null $is_freedom_fighter 1=>Yes ,2=>No
 * @property int|null $freedom_fighter_relation
 * @property int|null $is_child_of_naval_officer
 * @property string|null $naval_father_name
 * @property int $is_departmental_candidate
 * @property string|null $naval_office_no
 * @property string|null $naval_rank
 * @property string|null $navy_ship_etbd_retired
 * @property int|null $naval_uniform_civil
 * @property int|null $is_anser_vdp
 * @property string|null $anser_vdp_rank
 * @property string|null $anser_vdp_office_no
 * @property int|null $is_khudro_jati_gosti
 * @property int|null $phase
 * @property string|null $payment_type
 * @property int|null $is_manula_paid 1=>Yes,2=>No
 * @property string|null $ref_id
 * @property string|null $validation_id
 * @property string|null $order_id_original oder id by shurjo
 * @property float|null $amount
 * @property float|null $store_amount
 * @property string|null $card_type
 * @property string|null $card_no
 * @property string|null $trans_date
 * @property string|null $payment_api
 * @property int|null $payment_status 1=>Paid, 2=>Unpaid
 * @property string|null $referred_by
 * @property string|null $reference_details
 * @property int|null $have_reference
 * @property string|null $relationship
 * @property int|null $is_online_manual 1=>Online,2=>Manual
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int|null $application_status 1=>Active, 2=>Cancel
 * @property string|null $all_requested_tran_id
 * @property string|null $all_paid_tran_id
 * @property string|null $all_payment_response
 * @property string|null $all_payment_request
 * @property int|null $is_image_exist_check
 * @property int|null $trade_course_experience
 */
class DeSailors extends \yii\db\ActiveRecord
{

    // if true then skip teletalk validation
    // if false then teletalk validation required
    public $skipTeleTalkValidation = true;



    const PAYMENT = 'select_payment_type';
    const ACADEMIC_DE_SAILOR_ARTIFICER = 'artificer';
    const ACADEMIC_DE_SAILOR_DOCKYARD = 'dockyard';


    const ACADEMIC_INFO_JSC = 'academic_info_jsc';
    const ACADEMIC_INFO_JSC_SSC = 'academic_info_jsc_ssc';

    const PERSONAL_INFO = 'personal_information';
    const PERSONAL_INFO_WITH_IMAGE = 'personal_information_with_image';


    public $agree_payment_terms;
    public $exam_center_name;
    public $academic_info_already_used_in_ssc;
    public $academic_info_already_used_in_jsc;

    
    public $encryption_fields_for_personal_info = ['father_nid', 'current_phone', 'permanent_phone' , 'father_phone', 'mother_phone', 'guardian_phone'];


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
            [['eligibility_info_id', 'app_unique_id', 'candidate_type'], 'required'],
            [['payment_type', 'agree_payment_terms'], 'required', 'on' => self::PAYMENT],
            [['birth_registration_no'], 'birthRegistrationNoValidation', 'on' => self::PAYMENT,'skipOnError' => false, 'skipOnEmpty' => false],

            [[
                'jsc_institute_name',
                'jsc_reg_no',
                'jsc_passing_year',
                'jsc_gpa',
                'ssc_edu_board',
                'ssc_roll_no',
                'ssc_reg_no',
                'ssc_passing_year',
                'diploma_trade_institute',
                'diploma_trade_course',
                'diploma_trade_registration_roll',
                'diploma_trade_gpa',
            ], 'sailorArtificerValidation', 'on' => self::ACADEMIC_DE_SAILOR_ARTIFICER, 'skipOnError' => false, 'skipOnEmpty' => false],
            [[
                'jsc_institute_name',
                'jsc_reg_no',
                'jsc_passing_year',
                'jsc_gpa',
                'ssc_edu_board',
                'ssc_roll_no',
                'ssc_reg_no',
                'ssc_passing_year',
                'diploma_trade_institute',
                'diploma_trade_course',
                'diploma_trade_registration_roll',
                'diploma_trade_gpa',
            ], 'sailorArtificerValidation', 'on' => self::ACADEMIC_DE_SAILOR_DOCKYARD, 'skipOnError' => false, 'skipOnEmpty' => false],



            // Personal Information Validation
            [[
                'exam_center_name',
                'name',
                'father_name',
                'father_occupation',
                'mother_name',
                'mother_occupation',
                'current_village',
                'current_union',
                'current_post_office',
                'current_thana',
                'current_district',
                'current_post_code',
                // 'current_phone',
                 'father_phone',
                'mother_phone',
                'permanent_village',
                'permanent_union',
                'permanent_post_office',
                'permanent_thana',
                'permanent_district',
                'permanent_post_code',
                'permanent_phone',
                'dob',
                'age_according_to_circular',
                'religion',
                'marital_status',
                'nationality',
                'is_freedom_fighter',
                'is_child_of_naval_officer',
                'is_anser_vdp',
                'is_khudro_jati_gosti',
                'ssc_institute',
                'diploma_trade_institute',
                'diploma_trade_course',
                'diploma_trade_registration_roll',
                'diploma_trade_gpa',
                 'name_bangla',
                'father_name_bangla',
                'mother_name_bangla'
            ], 'personalInformationValidate', 'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
            [[
                'exam_center_name',
                'name',
                'father_name',
                'father_occupation',
                'mother_name',
                'mother_occupation',
                'current_village',
                'current_union',
                'current_post_office',
                'current_thana',
                'current_district',
                'current_post_code',
                // 'current_phone',
                 'father_phone',
                'mother_phone',
                'permanent_village',
                'permanent_union',
                'permanent_post_office',
                'permanent_thana',
                'permanent_district',
                'permanent_post_code',
                'permanent_phone',
                'dob',
                'age_according_to_circular',
                'religion',
                'marital_status',
                'nationality',
                'is_freedom_fighter',
                'is_child_of_naval_officer',
                'is_anser_vdp',
                'is_khudro_jati_gosti',
                'ssc_institute',
                'diploma_trade_institute',
                'diploma_trade_course',
                'diploma_trade_registration_roll',
                'diploma_trade_gpa',
                 'name_bangla',
                'father_name_bangla',
                'mother_name_bangla'
            ], 'personalInformationValidate', 'on' => self::PERSONAL_INFO_WITH_IMAGE, 'skipOnError' => false, 'skipOnEmpty' => false],




            // Only English Allow   /// 'naval_office_no', 'naval_rank',
            [[
                'jsc_institute_name',
                'diploma_trade_institute',
                'diploma_trade_gpa',
                'name',
                'father_name',
                'mother_name',
                'father_occupation',
                'mother_occupation',
                'current_village',
                'current_word_no',
                'current_union',
                'current_post_office',
                'current_thana',
                'permanent_village',
                'permanent_union',
                'permanent_word_no',
                'permanent_post_office',
                'permanent_thana',
                'guardian_name',
                'guardian_relation',
                'guardian_occupation',
                'guardian_address',
                'experience_one_institute',
                'experience_one_subject',
                'experience_one_cert_name',
                'experience_two_institute',
                'experience_two_subject',
                'experience_two_cert_name',
                'freedom_fighter_relation',
                'naval_father_name',
                'anser_vdp_rank',
                'anser_vdp_office_no',
                'ssc_edu_board',
                'ssc_group',
                'ssc_institute',
                'ssc_additional_subject',
                'hsc_dip_institute',
                'hsc_dip_group',
                'hsc_dip_board',
                'hsc_dip_additional_subject',
                'jsc_gpa'
            ], 'onlyInputEnglishCharacterValidation', 'skipOnError' => false, 'skipOnEmpty' => false],

            // Only Number Allow 
            [[
                'jsc_passing_year',
                'ssc_roll_no',
                'ssc_reg_no',
                'ssc_passing_year',
                'hsc_dip_roll_no',
                'hsc_dip_reg_no',
                'hsc_dip_passing_year',
                'diploma_trade_course',
                'father_nid',
                'permanent_phone',
                'current_phone',
                'current_post_code',
                'permanent_post_code',
                'experience_one_year',
                'experience_two_year',
                'hsc_dip_reg_no',
                'hsc_dip_roll_no',
                'hsc_dip_passing_year'
            ], 'onlyNumberInputValidation', 'skipOnError' => false, 'skipOnEmpty' => false],

            // Only Decimal  Number Allow 
            [[
                'jsc_reg_no',
                'diploma_trade_registration_roll'
            ], 'onlyNumberInputValidationWithBackslash', 'skipOnError' => false, 'skipOnEmpty' => false],


            /// check duplicate application by same academic information 
            [[
                'jsc_institute_name',
                'jsc_institute_name',
                'jsc_passing_year',
                'jsc_gpa',
                'ssc_edu_board',
                'ssc_roll_no',
                'ssc_reg_no',
                'ssc_passing_year',
                'hsc_dip_board',
                'hsc_dip_roll_no',
                'hsc_dip_reg_no',
                'hsc_dip_passing_year'
            ], 'checkDuplicationApplicationBySameAcInformation', 'skipOnError' => false, 'skipOnEmpty' => false],




              [[
                'father_name_bangla',
                'mother_name_bangla',
                'name_bangla'
            ], 'banglaInputCharacterValidation', 'skipOnError' => false, 'skipOnEmpty' => false],

            // phone no validation 
            [['current_phone', 'permanent_phone','father_phone','mother_phone','guardian_phone' ], 'phoneNoValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
            [['permanent_phone'], 'permanentPhoneUniqueCheck', 'skipOnError' => false, 'skipOnEmpty' => false],

             // freedom_fighter_relation validation 
            // ['freedom_fighter_relation', 'freedomFighterRelationValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
            // Gender Validation 
            ['gender', 'genderValidation', 'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
            ['gender', 'genderValidation', 'on' => self::PERSONAL_INFO_WITH_IMAGE, 'skipOnError' => false, 'skipOnEmpty' => false],
            // marital_status Validation 
            ['marital_status', 'maritalStatusValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
            /// 
            ['dob', 'ageValidation', 'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
            ['dob', 'ageValidation', 'on' => self::PERSONAL_INFO_WITH_IMAGE, 'skipOnError' => false, 'skipOnEmpty' => false],

              // is_child_of_naval_officer
            [['naval_father_name', 'naval_uniform_civil', 'naval_office_no', 'naval_rank'], 'isNavalChildValidation',  'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
            [['naval_father_name', 'naval_uniform_civil', 'naval_office_no', 'naval_rank'], 'isNavalChildValidation',  'on' => self::PERSONAL_INFO_WITH_IMAGE, 'skipOnError' => false, 'skipOnEmpty' => false],

        // Only Decimal  Number Allow 
            [[
                'ssc_gpa',
                'hsc_dip_gpa'
            ], 'onlyDecimalNumberInputValidation', 'skipOnError' => false, 'skipOnEmpty' => false],


             // marital_status Validation 
            [['ssc_gpa','ssc_group'], 'sscGpaGroupValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
            




            [['all_requested_tran_id', 'all_paid_tran_id', 'all_payment_response', 'all_payment_request','is_image_exist_check','trade_course_experience','birth_registration_no', 'name_bangla', 'father_name_bangla', 'mother_name_bangla','reason'], 'safe'],

            [['eligibility_info_id', 'candidate_designation', 'candidate_type', 'center_id', 'batch_id', 'batch_config_id', 'exam_group', 'religion', 'gender', 'marital_status', 'ac_type_ssc', 'diploma_trade_course', 'hsc_or_diploma', 'is_freedom_fighter', 'freedom_fighter_relation', 'is_child_of_naval_officer', 'is_departmental_candidate', 'naval_uniform_civil', 'is_anser_vdp', 'is_khudro_jati_gosti', 'phase', 'is_manula_paid', 'payment_status', 'have_reference', 'is_online_manual', 'created_by', 'updated_by', 'application_status','exam_date_id'], 'integer'],
            [['exam_date', 'serial_generate_date', 'dob', 'trans_date', 'created_dt', 'updated_dt'], 'safe'],
            [['father_income', 'amount', 'store_amount'], 'number'],
            [['ssc_edu_data', 'hsc_edu_data', 'ssc_teletalk_data', 'hsc_teletalk_data', 'payment_type', 'payment_api', 'reference_details', 'relationship'], 'string'],
            [['app_unique_id', 'jsc_passing_year'], 'string', 'max' => 100],
            [['birth_registration_no','serial_no', 'jsc_reg_no', 'jsc_gpa', 'ssc_group', 'ssc_edu_board', 'ssc_reg_no', 'ssc_passing_year', 'ssc_gpa', 'hsc_dip_group', 'hsc_dip_board', 'hsc_dip_reg_no', 'hsc_dip_roll_no', 'hsc_dip_passing_year', 'hsc_dip_gpa', 'experience_one_year', 'experience_two_year', 'experience_three_year', 'experience_four_year', 'diploma_trade_registration_roll', 'diploma_trade_gpa'], 'string', 'max' => 50],
            [['eligible_district', 'district', 'father_nid', 'father_occupation', 'mother_occupation', 'current_village', 'current_word_no', 'current_union', 'current_post_office', 'current_thana', 'current_post_code', 'current_district', 'current_phone', 'permanent_village', 'permanent_union', 'permanent_word_no', 'permanent_post_office', 'permanent_thana', 'permanent_district', 'permanent_post_code', 'permanent_phone', 'guardian_relation', 'guardian_occupation', 'age_according_to_circular', 'nationality', 'photo', 'qr_photo', 'experience_one_subject', 'experience_one_cert_name', 'experience_two_subject', 'experience_two_cert_name', 'experience_three_subject', 'experience_three_cert_name', 'experience_four_subject', 'experience_four_cert_name', 'naval_father_name', 'naval_office_no', 'naval_rank', 'navy_ship_etbd_retired', 'anser_vdp_rank', 'anser_vdp_office_no', 'ref_id', 'card_type', 'card_no'], 'string', 'max' => 150],
            [['name', 'father_name', 'mother_name', 'guardian_name', 'guardian_address', 'jsc_institute_name', 'ssc_institute', 'ssc_additional_subject', 'hsc_dip_institute', 'diploma_trade_institute', 'hsc_dip_additional_subject', 'experience_one_institute', 'experience_two_institute', 'experience_three_institute', 'experience_four_institute', 'validation_id', 'order_id_original'], 'string', 'max' => 250],
            [['ssc_roll_no'], 'string', 'max' => 20],
            [['referred_by'], 'string', 'max' => 255],
            [['app_unique_id'], 'unique'],
            [['serial_no'], 'unique'],

            [['photo'], 'required', 'on' => self::PERSONAL_INFO],
            [
                'photo',
                'image',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg',
                'maxSize' => 1024 * 500,
                'minWidth' => 300,
                'maxWidth' => 300,
                'minHeight' => 300,
                'maxHeight' => 300,
                // 'minWidth' => 100, 'maxWidth' => 250,
                // 'minHeight' => 100, 'maxHeight' => 250,
                'on' => self::PERSONAL_INFO,
            ],
            [
                ['photo'],
                'image',
                'skipOnError' => false,
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg',
                'maxSize' => 1024 * 500,
                'minWidth' => 300,
                'maxWidth' => 300,
                'minHeight' => 300,
                'maxHeight' => 300,
                // 'minWidth' => 100, 'maxWidth' => 250,
                // 'minHeight' => 100, 'maxHeight' => 250,
                'on' => self::PERSONAL_INFO_WITH_IMAGE,
            ],

        ];
    }



    public function phoneNoValidation($attribute, $params, $validator)
    {
        if (!empty($this->$attribute) && !preg_match('/^(?:\+88|88)?(01[3-9]\d{8})$/', $this->$attribute)) // '/[^a-z\d]/i' should also work.
        {
            $this->addError($attribute, 'ফোন নম্বর সঠিক নয়।');
        }
        return true;
    }

     public function banglaInputCharacterValidation($attribute, $params, $validator)
    {
        if ( !empty($this->$attribute) &&  !preg_match('/^[\x{0980}-\x{09FF}\s]+$/u', trim($this->$attribute)) ) {
            $this->addError($attribute, 'ইনপুট অবশ্যই বাংলায় হতে হবে।');
        }
        return true;
    }

     public function permanentPhoneUniqueCheck($attribute, $params, $validator)
    {
        if (!empty($this->$attribute) && !empty($this->current_phone) && $this->$attribute == $this->current_phone) {
            $this->addError($attribute, 'স্থায়ী ফোন নম্বর ও বর্তমান ফোন নম্বর একই হতে পারে না।'); #স্থায়ী ফোন নম্বর ও বর্তমান ফোন নম্বর  আলাদা হতে হবে। 
        }
        return true;
    }

 
    public function sscGpaGroupValidation($attribute, $params, $validator){

        if($this->skipTeleTalkValidation && empty($this->$attribute) && ($this->scenario == self::ACADEMIC_DE_SAILOR_ARTIFICER || $this->scenario == self::ACADEMIC_DE_SAILOR_DOCKYARD)){
            $this->addError($attribute, self::attributeLabelBangla()[$attribute] . ' খালি রাখা যাবে না।');
           
        }
    }

    public function birthRegistrationNoValidation($attribute, $params, $validator)
    {
        if (empty($this->$attribute)) {
            $this->addError($attribute, self::attributeLabelBangla()[$attribute] . ' খালি রাখা যাবে না।');
        }
        else if (!empty($this->$attribute) && !preg_match("#^[0-9]+$#", $this->$attribute)) {
            $this->addError($attribute, ' ইনপুট অবশ্যই ইংরেজি সংখ্যা হতে হবে।');
        }
        else if (!empty($this->$attribute) && preg_match("#^[0-9]+$#", $this->$attribute)) {
            $check_data = self::find()
                ->select(['id'])
                ->where(['batch_id' => $this->batch_id])
                ->andWhere(['birth_registration_no' => $this->birth_registration_no])                 
                ->andWhere(['<>', 'id', $this->id])
                ->activeApplication()
                ->asArray()->count();
                
            if($check_data > 0){
                $this->addError($attribute, $this->birth_registration_no .' ব্যবহার করে ইতিমধ্যে আবেদন করা হয়েছে।');
            }
        }
        return true;
    }

    public function genderValidation($attribute, $params, $validator)
    {

        if (!empty($this->$attribute))  /// check gender is allowed or not 
        {
            $configuration = SailorBatchConfiguration::find()
                ->where(['batch_id' => $this->batch_id])
                ->andFilterWhere(['center_id' => $this->center_id])
                ->andFilterWhere(['REGEXP', 'gender', "(^|,)" . $this->gender . "(,|$)"])
                ->andFilterWhere(['REGEXP', 'candidate_designation', "(^|,)" . $this->candidate_designation . "(,|$)"])
                ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $this->eligible_district . "(,|$)"]);
            ///->andFilterWhere(['status' => Constants::STATUS_ACTIVE]);
            $configuration =  $configuration->asArray()->count();
            //  echo  $sql_r = $configuration->createCommand()->getRawSql();  die();
            // echo $this->$attribute.$this->batch_id.$this->center_id.$this->eligible_district.$this->candidate_designation;
            $gender_bangla = ($this->gender == Constants::GENDER_MALE) ? 'পুরুষ ' : 'মহিলা ';
            if ($configuration === 0)
                $this->addError($attribute, 'আপনি জেন্ডার সিলেকশন ভুল করেছেন।');
            // $this->addError($attribute, $gender_bangla . ' এই ব্যাচ এ আবেদন করতে পারবে না।');
        }
        return true;
    }

     public function maritalStatusValidation($attribute, $params, $validator)
    {

        if (!empty($this->$attribute))  /// check gender is allowed or not 
        {

            $configuration = SailorBatchConfiguration::find()
                ->where(['batch_id' => $this->batch_id])
                ->andFilterWhere(['center_id' => $this->center_id])
                ->andFilterWhere(['REGEXP', 'marital_status', "(^|,)" . $this->marital_status . "(,|$)"])
                ->andFilterWhere(['REGEXP', 'candidate_designation', "(^|,)" . $this->candidate_designation . "(,|$)"])
                ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $this->eligible_district . "(,|$)"]);
            //->andFilterWhere(['status' => Constants::STATUS_ACTIVE]);
            $configuration =  $configuration->asArray()->count();
            ///echo  $sql_r = $configuration->createCommand()->getRawSql();
            // echo $this->$attribute.$this->batch_id.$this->center_id.$this->eligible_district.$this->candidate_designation;
            $marital_status = ($this->marital_status == Constants::YES) ? 'বিবাহিত ' : 'অবিবাহিত ';
            if ($configuration === 0)
                $this->addError($attribute, $marital_status . ' এই ব্যাচ এ আবেদন করতে পারবে না।');
        }
        return true;
    }


    public function ageValidation($attribute, $params, $validator)
    {

        if (!empty($this->$attribute))  /// check gender is allowed or not 
        {
            //// Eligibility information by id 
            $eligibility_model = Eligibility::eligibilityBySession($this->candidate_designation);
            // batch data for age check 
            $batch_data = SailorBatchs::find()
                ->select(['id', 'circular_date'])
                ->where(['id' => $this->batch_id])->asArray()->one();

            if ($eligibility_model &&  $batch_data) {
                $age_year_month = StaticMethod::getDifferenceBetweenTwoDateYearMonth(maxDate: $batch_data['circular_date'], minDate: $this->dob);

                // check for departmental candidate 
                $max_age = ($this->is_departmental_candidate == Constants::YES) ? $eligibility_model['dept_can_max_age'] : $eligibility_model['max_age'];

                if (strval($eligibility_model['min_age']) <= strval($age_year_month) && strval($max_age) >= strval($age_year_month)) {
                    return true;
                } else {
                    $this->addError($attribute, ' আপনার বয়স ' . $age_year_month . ' যা গ্রহণযোগ্য নয়।');
                }
            }
        }
        return true;
    }

     public function isNavalChildValidation($attribute, $params, $validator)
    {
        if ($this->is_child_of_naval_officer == Constants::YES && empty($this->$attribute)) {
            $this->addError($attribute, self::attributeLabelBangla()[$attribute] . ' খালি রাখা যাবে না।');
        }
        return true;
    }


    public function sailorArtificerValidation($attribute, $params, $validator)
    {
        if (empty($this->$attribute)) {
            $this->addError($attribute, self::attributeLabelBangla()[$attribute] . ' খালি রাখা যাবে না।');
        }
        return true;
    }

    public function personalInformationValidate($attribute, $params, $validator)
    {

        if (empty($this->$attribute)) {
            $this->addError($attribute, self::attributeLabelBangla()[$attribute] . ' খালি রাখা যাবে না।');
        }
        return true;
    }


    public function onlyInputEnglishCharacterValidation($attribute, $params, $validator)
    {

        if (!empty($this->$attribute) && !preg_match("#^[a-zA-Z0-9 .@()\&\-\,\/\:\']+$#", trim(preg_replace('/[\x{200B}-\x{200D}]/u', '', $this->$attribute)))) {
            $this->addError($attribute, 'ইনপুট অবশ্যই ইংরেজিতে হতে হবে।'); //সাংকেতিক চিহ্ন গ্রহণযোগ্য নয়।
        }
        return true;
    }
    public function onlyNumberInputValidation($attribute, $params, $validator)
    {
        if (!empty($this->$attribute) && !preg_match("#^[0-9]+$#", $this->$attribute)) {
            $this->addError($attribute, ' ইনপুট অবশ্যই ইংরেজি সংখ্যা হতে হবে।');
        }
        return true;
    }

    public function onlyNumberInputValidationWithBackslash($attribute, $params, $validator)
    {
        if (!empty($this->$attribute) && !preg_match("#^[0-9\/]+$#", $this->$attribute)) {
            $this->addError($attribute, ' ইনপুট অবশ্যই ইংরেজি সংখ্যা হতে হবে।');
        }
        return true;
    }

    public function onlyDecimalNumberInputValidation($attribute, $params, $validator)
    {
        if (!empty($this->$attribute) && !preg_match("#^[0-9.]+$#", $this->$attribute)) {
            $this->addError($attribute, ' ইনপুট অবশ্যই ইংরেজি সংখ্যা হতে হবে।');
        }
        return true;
    }



    public function checkDuplicationApplicationBySameAcInformation($attribute, $params, $validator)
    {

        if ($this->scenario == self::ACADEMIC_INFO_JSC) {
            // echo 'here--';
            if ($this->jsc_institute_name && $this->jsc_reg_no && $this->jsc_passing_year && $this->jsc_gpa) {
                $check_data = self::find()
                    ->select(['id'])
                    ->where(['batch_id' => $this->batch_id])
                    ->andWhere(['jsc_institute_name' => $this->jsc_institute_name])
                    ->andWhere(['jsc_reg_no' => $this->jsc_reg_no])
                    ->andWhere(['jsc_passing_year' => $this->jsc_passing_year])
                    ->andWhere(['jsc_gpa' => $this->jsc_gpa])
                    ->andWhere(['dob' => $this->dob])
                    ->andWhere(['<>', 'id', $this->id])
                    ->activeApplication()
                    ->asArray()->count();
                /// echo $sql_r = $check_data->createCommand()->getRawSql();die();
                if ($check_data > 0) {
                    $this->addError('academic_info_already_used_in_jsc', 'প্রদত্ত ৮ম শ্রেণী তথ্য ও জন্ম তারিখ ব্যবহার করে অন্য একটি আবেদন করা হয়েছে।');
                } else
                    return true;
            }
            return true;
        }  else if ($this->scenario == self::ACADEMIC_DE_SAILOR_ARTIFICER || $this->scenario == self::ACADEMIC_DE_SAILOR_DOCKYARD) {
            if ($this->ssc_edu_board && $this->ssc_roll_no && $this->ssc_reg_no && $this->ssc_passing_year) {
                $check_data = self::find()
                    ->select(['id'])
                    ->where(['batch_id' => $this->batch_id])
                    ->andWhere(['ssc_edu_board' => $this->ssc_edu_board])
                    ->andWhere(['ssc_reg_no' => $this->ssc_reg_no])
                    ->andWhere(['ssc_roll_no' => $this->ssc_roll_no])
                    ->andWhere(['ssc_passing_year' => $this->ssc_passing_year])
                    ->andWhere(['<>', 'id', $this->id])
                    ->activeApplication()
                    ->asArray()->count();
                if ($check_data > 0) {
                    $this->addError('academic_info_already_used_in_ssc', 'প্রদত্ত এসএসসি / সমমান তথ্য ব্যবহার করে অন্য একটি আবেদন করা হয়েছে।');
                } else
                    return true;
            }
        }
      

        return true;
    }


    /**
     * {@inheritdoc}
     */
    public static function attributeLabelBangla()
    {
        return [
            'id' => Yii::t('app', 'ID'),
               'exam_date_id' => Yii::t('app', 'Exam Date ID'),
            'eligibility_info_id' => Yii::t('app', 'Eligibility Info ID'),
            'app_unique_id' => Yii::t('app', 'App ID'),
            'candidate_designation' => Yii::t('app', 'App Type'),
            'center_id' => Yii::t('app', 'কেন্দ্রের নাম'),
            'exam_center_name' => Yii::t('app', 'কেন্দ্রের নাম'),
            'batch_id' => Yii::t('app', 'শাখা'),
            'batch_config_id' => Yii::t('app', 'Batch Config'),
            'exam_date' => Yii::t('app', 'Exam Date'),
            'exam_group' => Yii::t('app', 'Exam Group'),
            'serial_no' => Yii::t('app', 'Serial No'),
            'eligible_district' => Yii::t('app', 'Eligible District'),
            'district' => Yii::t('app', 'জেলা/কোটা'),
            'name' => Yii::t('app', 'প্রার্থীর পূর্ণ নাম'),
            'father_name' => Yii::t('app', 'পিতার নাম'),
            'father_nid' => Yii::t('app', 'পিতার এন আই ডি'),
            'father_occupation' => Yii::t('app', 'পিতার পেশা'),
            'father_income' => Yii::t('app', 'Father Income'),
            'mother_name' => Yii::t('app', 'মাতার  নাম'),
            'mother_occupation' => Yii::t('app', 'মাতার  পেশা'),
            'current_village' => Yii::t('app', 'গ্রাম/বাসা'),
            'current_word_no' => Yii::t('app', 'ওয়ার্ড  নং'),
            'current_union' => Yii::t('app', 'ইউনিয়ন/রোড নং'),
            'current_post_office' => Yii::t('app', 'পোস্ট অফিস'),
            'current_thana' => Yii::t('app', 'থানা/উপজেলা'),
            'current_post_code' => Yii::t('app', 'পোস্ট কোড'),
            'current_district' => Yii::t('app', 'জেলা'),
            'current_phone' => Yii::t('app', 'ফোন/মোবাইল'),
            'permanent_village' => Yii::t('app', 'গ্রাম/বাসা'),
            'permanent_union' => Yii::t('app', 'ইউনিয়ন/রোড নং'),
            'permanent_word_no' => Yii::t('app', 'ওয়ার্ড  নং'),
            'permanent_post_office' => Yii::t('app', 'পোস্ট অফিস'),
            'permanent_thana' => Yii::t('app', 'থানা/উপজেলা'),
            'permanent_district' => Yii::t('app', 'জেলা'),
            'permanent_post_code' => Yii::t('app', 'পোস্ট কোড'),
            'permanent_phone' => Yii::t('app', 'ফোন/মোবাইল'),

            'father_phone' => Yii::t('app', 'পিতার ফোন/মোবাইল'),
            'mother_phone' => Yii::t('app', 'মাতার ফোন/মোবাইল'),
            'guardian_phone' => Yii::t('app', 'অভিভাবকের ফোন/মোবাইল'),

            'guardian_name' => Yii::t('app', 'অভিভাবকের নাম (পিতা জীবিত না থাকলে)'),
            'guardian_relation' => Yii::t('app', 'সম্পর্ক'),
            'guardian_occupation' => Yii::t('app', 'পেশা'),
            'guardian_address' => Yii::t('app', 'ঠিকানা'),
            'dob' => Yii::t('app', 'জন্ম তারিখ (মাধ্যমিক সনদপত্র/ নবম শ্রেণীর রেজিস্ট্রেশন কার্ড / টোপাস প্রার্থীদের ক্ষেত্রে জন্ম নিবন্ধন বা ভোটার আইডি কার্ড অনুযায়ী)'),
            'age_according_to_circular' => Yii::t('app', 'বিজ্ঞাপনে বর্ণিত তারিখে বয়সঃ (বছর - মাস - দিন)'),
            'religion' => Yii::t('app', 'ধর্ম'),
            'gender' => Yii::t('app', 'লিঙ্গ'),
            'marital_status' => Yii::t('app', 'বৈবাহিক অবস্থা'),
            'nationality' => Yii::t('app', 'জাতীয়তা (জন্ম সূত্রে)'),
            'photo' => Yii::t('app', 'ছবি'),
            'qr_photo' => Yii::t('app', 'Qr Photo'),
            'jsc_reg_no' => Yii::t('app', 'রেজিস্ট্রেশন নং'),
            'jsc_institute_name' => Yii::t('app', 'শিক্ষা প্রতিষ্ঠানের নাম'),
            'jsc_passing_year' => Yii::t('app', 'পাশের সন'),
            'jsc_gpa' => Yii::t('app', 'প্রাপ্ত নম্বর/জিপিএ'),
            'ac_type_ssc' => Yii::t('app', 'Ac Type Ssc'),
            'ssc_institute' => Yii::t('app', 'শিক্ষা প্রতিষ্ঠানের নাম'),
            'ssc_group' => Yii::t('app', 'শিক্ষা বিভাগ'),
            'ssc_edu_board' => Yii::t('app', 'শিক্ষা বোর্ড'),
            'ssc_reg_no' => Yii::t('app', 'রেজিষ্ট্রেশন নং'),
            'ssc_roll_no' => Yii::t('app', 'রোল নং'),
            'ssc_passing_year' => Yii::t('app', 'পাশের সন'),
            'ssc_additional_subject' => Yii::t('app', 'Ssc Additional Subject'),
            'ssc_gpa' => Yii::t('app', 'প্রাপ্ত জিপিএ'),
            'hsc_or_diploma' => Yii::t('app', 'Hsc Or Diploma'),
            'hsc_dip_institute' => Yii::t('app', 'Hsc Dip Institute'),
            'hsc_dip_group' => Yii::t('app', 'Hsc Dip Group'),
            'hsc_dip_board' => Yii::t('app', 'Hsc Dip Board'),
            'hsc_dip_reg_no' => Yii::t('app', 'Hsc Dip Reg No'),
            'hsc_dip_roll_no' => Yii::t('app', 'Hsc Dip Roll No'),
            'hsc_dip_passing_year' => Yii::t('app', 'Hsc Dip Passing Year'),
            'hsc_dip_additional_subject' => Yii::t('app', 'Hsc Dip Additional Subject'),

            'diploma_trade_institute' => Yii::t('app', 'শিক্ষা প্রতিষ্ঠানের নাম'),
            'diploma_trade_course' => Yii::t('app', 'কোর্স'),
            'diploma_trade_registration_roll' => Yii::t('app', 'রেজিস্ট্রেশন নং / রোল নং'),
            'diploma_trade_gpa' => Yii::t('app', 'সিজিপিএ/জিপিএ'),


            'hsc_dip_gpa' => Yii::t('app', 'Hsc Dip Gpa'),
            'ssc_edu_data' => Yii::t('app', 'Ssc Edu Data'),
            'hsc_edu_data' => Yii::t('app', 'Hsc Edu Data'),
            'ssc_teletalk_data' => Yii::t('app', 'Ssc Teletalk Data'),
            'hsc_teletalk_data' => Yii::t('app', 'Hsc Teletalk Data'),
            'experience_one_institute' => Yii::t('app', 'প্রতিষ্ঠানের নাম'),
            'experience_one_subject' => Yii::t('app', 'অংশগ্রহনকৃত বিষয়ের নাম'),
            'experience_one_year' => Yii::t('app', 'সন'),
            'experience_one_cert_name' => Yii::t('app', 'প্রাপ্ত স্থান/প্রশংসাপত্র/পদকের নাম'),
            'experience_two_institute' => Yii::t('app', 'Experience Two Institute'),
            'experience_two_subject' => Yii::t('app', 'Experience Two Subject'),
            'experience_two_year' => Yii::t('app', 'Experience Two Year'),
            'experience_two_cert_name' => Yii::t('app', 'Experience Two Cert Name'),
            'experience_three_institute' => Yii::t('app', 'Experience Three Institute'),
            'experience_three_subject' => Yii::t('app', 'Experience Three Subject'),
            'experience_three_year' => Yii::t('app', 'Experience Three Year'),
            'experience_three_cert_name' => Yii::t('app', 'Experience Three Cert Name'),
            'experience_four_institute' => Yii::t('app', 'Experience Four Institute'),
            'experience_four_subject' => Yii::t('app', 'Experience Four Subject'),
            'experience_four_year' => Yii::t('app', 'Experience Four Year'),
            'experience_four_cert_name' => Yii::t('app', 'Experience Four Cert Name'),
            'is_freedom_fighter' => Yii::t('app', 'মুক্তিযোদ্ধার সন্তান'),
            'freedom_fighter_relation' => Yii::t('app', 'মুক্তিযোদ্ধার সাথে সম্পর্ক'),
            'is_child_of_naval_officer' => Yii::t('app', 'নৌবাহিনীর সদস্যের সন্তান'),
            'naval_father_name' => Yii::t('app', 'পিতার নাম (নৌবাহিনীর সদস্য)'),
            'is_departmental_candidate' => Yii::t('app', 'is departmental candidate'),
            'naval_office_no' => Yii::t('app', 'নৌবাহিনীর অফিস নম্বর'),
            'naval_rank' => Yii::t('app', 'নৌবাহিনীর পদবী'),
            'navy_ship_etbd_retired' => Yii::t('app', 'Navy Ship Etbd Retired'),
            'naval_uniform_civil' => Yii::t('app', 'সামরিক / বেসামরিক'),
            'is_anser_vdp' => Yii::t('app', ' প্রার্থী আনসার / ভিডিপি\'র'),
            'anser_vdp_rank' => Yii::t('app', 'আনসার ও গ্রামপ্রতিরক্ষা বাহিনীর পদবী'),
            'anser_vdp_office_no' => Yii::t('app', 'আনসার ও গ্রামপ্রতিরক্ষা বাহিনীর অফিস নম্বর'),
            'is_khudro_jati_gosti' => Yii::t('app', 'ক্ষুদ্রজাতি গোষ্ঠী'),
            'phase' => Yii::t('app', 'Phase'),
            'payment_type' => Yii::t('app', 'Payment Type'),
            'is_manula_paid' => Yii::t('app', 'Is Manula Paid'),
            'ref_id' => Yii::t('app', 'Ref ID'),
            'validation_id' => Yii::t('app', 'Validation ID'),
            'order_id_original' => Yii::t('app', 'Order Id Original'),
            'amount' => Yii::t('app', 'Amount'),
            'store_amount' => Yii::t('app', 'Store Amount'),
            'card_type' => Yii::t('app', 'Card Type'),
            'card_no' => Yii::t('app', 'Card No'),
            'trans_date' => Yii::t('app', 'Trans Date'),
            'payment_api' => Yii::t('app', 'Payment Api'),
            'payment_status' => Yii::t('app', 'Payment Status'),
            'application_status' => Yii::t('app', 'Is Cancel Application ? '),
            'referred_by' => Yii::t('app', 'Referred By'),
            'reference_details' => Yii::t('app', 'Reference Details'),
            'have_reference' => Yii::t('app', 'Have Reference'),
            'relationship' => Yii::t('app', 'Relationship'),
            'is_online_manual' => Yii::t('app', 'Is Online Manual'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Dt'),
            'updated_dt' => Yii::t('app', 'Updated Dt'),
            'agree_payment_terms' => Yii::t('app', 'Agree payment conditions'),
            'birth_registration_no' => Yii::t('app','জন্ম নিবন্ধন নম্বর '),
            'name_bangla' => Yii::t('app', 'প্রার্থীর পূর্ণ নাম (বাংলা)'),
            'father_name_bangla' => Yii::t('app', 'পিতার নাম (বাংলা)'),
            'mother_name_bangla' =>  Yii::t('app', 'মাতার  নাম (বাংলা)'),
        ];
    }



    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'eligibility_info_id' => Yii::t('app', 'Eligibility Info ID'),
            'app_unique_id' => Yii::t('app', 'App Unique ID'),
            'candidate_designation' => Yii::t('app', 'Candidate Designation'),
            'candidate_type' => Yii::t('app', 'Candidate Type'),
            'center_id' => Yii::t('app', 'Center'),
            'batch_id' => Yii::t('app', 'Batch'),
            'batch_config_id' => Yii::t('app', 'Batch Config'),
            'exam_date' => Yii::t('app', 'Exam Date'),
            'exam_group' => Yii::t('app', 'Exam Group'),
            'serial_generate_date' => Yii::t('app', 'Serial Generate Date'),
            'serial_no' => Yii::t('app', 'Serial No'),
            'eligible_district' => Yii::t('app', 'Eligible District'),
            'district' => Yii::t('app', 'District'),
            'name' => Yii::t('app', 'Name'),
            'father_name' => Yii::t('app', 'Father Name'),
            'father_nid' => Yii::t('app', 'Father Nid'),
            'father_occupation' => Yii::t('app', 'Father Occupation'),
            'father_income' => Yii::t('app', 'Father Income'),
            'mother_name' => Yii::t('app', 'Mother Name'),
            'mother_occupation' => Yii::t('app', 'Mother Occupation'),
            'current_village' => Yii::t('app', 'Current Village'),
            'current_word_no' => Yii::t('app', 'Current Word No'),
            'current_union' => Yii::t('app', 'Current Union'),
            'current_post_office' => Yii::t('app', 'Current Post Office'),
            'current_thana' => Yii::t('app', 'Current Thana'),
            'current_post_code' => Yii::t('app', 'Current Post Code'),
            'current_district' => Yii::t('app', 'Current District'),
            'current_phone' => Yii::t('app', 'Current Phone'),
            'permanent_village' => Yii::t('app', 'Permanent Village'),
            'permanent_union' => Yii::t('app', 'Permanent Union'),
            'permanent_word_no' => Yii::t('app', 'Permanent Word No'),
            'permanent_post_office' => Yii::t('app', 'Permanent Post Office'),
            'permanent_thana' => Yii::t('app', 'Permanent Thana'),
            'permanent_district' => Yii::t('app', 'Permanent District'),
            'permanent_post_code' => Yii::t('app', 'Permanent Post Code'),
            'permanent_phone' => Yii::t('app', 'Permanent Phone'),
            'guardian_name' => Yii::t('app', 'Guardian Name'),
            'guardian_relation' => Yii::t('app', 'Guardian Relation'),
            'guardian_occupation' => Yii::t('app', 'Guardian Occupation'),
            'guardian_address' => Yii::t('app', 'Guardian Address'),
            'dob' => Yii::t('app', 'Dob'),
            'age_according_to_circular' => Yii::t('app', 'Age According To Circular'),
            'religion' => Yii::t('app', 'Religion'),
            'gender' => Yii::t('app', 'Gender'),
            'marital_status' => Yii::t('app', 'Marital Status'),
            'nationality' => Yii::t('app', 'Nationality'),
            'photo' => Yii::t('app', 'Photo'),
            'qr_photo' => Yii::t('app', 'Qr Photo'),
            'jsc_reg_no' => Yii::t('app', 'Jsc Reg No'),
            'jsc_institute_name' => Yii::t('app', 'Jsc Institute Name'),
            'jsc_passing_year' => Yii::t('app', 'Jsc Passing Year'),
            'jsc_gpa' => Yii::t('app', 'Jsc Gpa'),
            'ac_type_ssc' => Yii::t('app', 'Ac Type Ssc'),
            'ssc_institute' => Yii::t('app', 'Ssc Institute'),
            'ssc_group' => Yii::t('app', 'Ssc Group'),
            'ssc_edu_board' => Yii::t('app', 'Ssc Edu Board'),
            'ssc_reg_no' => Yii::t('app', 'Ssc Reg No'),
            'ssc_roll_no' => Yii::t('app', 'Ssc Roll No'),
            'ssc_passing_year' => Yii::t('app', 'Ssc Passing Year'),
            'ssc_additional_subject' => Yii::t('app', 'Ssc Additional Subject'),
            'ssc_gpa' => Yii::t('app', 'Ssc Gpa'),
            'hsc_or_diploma' => Yii::t('app', 'Hsc Or Diploma'),
            'hsc_dip_institute' => Yii::t('app', 'Hsc Dip Institute'),
            'hsc_dip_group' => Yii::t('app', 'Hsc Dip Group'),
            'hsc_dip_board' => Yii::t('app', 'Hsc Dip Board'),
            'hsc_dip_reg_no' => Yii::t('app', 'Hsc Dip Reg No'),
            'hsc_dip_roll_no' => Yii::t('app', 'Hsc Dip Roll No'),
            'hsc_dip_passing_year' => Yii::t('app', 'Hsc Dip Passing Year'),
            'hsc_dip_additional_subject' => Yii::t('app', 'Hsc Dip Additional Subject'),
            'hsc_dip_gpa' => Yii::t('app', 'Hsc Dip Gpa'),
            'ssc_edu_data' => Yii::t('app', 'Ssc Edu Data'),
            'hsc_edu_data' => Yii::t('app', 'Hsc Edu Data'),
            'diploma_trade_institute' => Yii::t('app', 'Diploma Trade Institute'),
            'diploma_trade_course' => Yii::t('app', 'Diploma Trade Course'),
            'diploma_trade_registration_roll' => Yii::t('app', 'Diploma Trade Registration Roll'),
            'diploma_trade_gpa' => Yii::t('app', 'Diploma Trade Gpa'),
            'ssc_teletalk_data' => Yii::t('app', 'Ssc Teletalk Data'),
            'hsc_teletalk_data' => Yii::t('app', 'Hsc Teletalk Data'),
            'experience_one_institute' => Yii::t('app', 'Experience One Institute'),
            'experience_one_subject' => Yii::t('app', 'Experience One Subject'),
            'experience_one_year' => Yii::t('app', 'Experience One Year'),
            'experience_one_cert_name' => Yii::t('app', 'Experience One Cert Name'),
            'experience_two_institute' => Yii::t('app', 'Experience Two Institute'),
            'experience_two_subject' => Yii::t('app', 'Experience Two Subject'),
            'experience_two_year' => Yii::t('app', 'Experience Two Year'),
            'experience_two_cert_name' => Yii::t('app', 'Experience Two Cert Name'),
            'experience_three_institute' => Yii::t('app', 'Experience Three Institute'),
            'experience_three_subject' => Yii::t('app', 'Experience Three Subject'),
            'experience_three_year' => Yii::t('app', 'Experience Three Year'),
            'experience_three_cert_name' => Yii::t('app', 'Experience Three Cert Name'),
            'experience_four_institute' => Yii::t('app', 'Experience Four Institute'),
            'experience_four_subject' => Yii::t('app', 'Experience Four Subject'),
            'experience_four_year' => Yii::t('app', 'Experience Four Year'),
            'experience_four_cert_name' => Yii::t('app', 'Experience Four Cert Name'),
            'is_freedom_fighter' => Yii::t('app', 'Is Freedom Fighter'),
            'freedom_fighter_relation' => Yii::t('app', 'Freedom Fighter Relation'),
            'is_child_of_naval_officer' => Yii::t('app', 'Is Child Of Naval Officer'),
            'naval_father_name' => Yii::t('app', 'Naval Father Name'),
            'is_departmental_candidate' => Yii::t('app', 'Is Departmental Candidate'),
            'naval_office_no' => Yii::t('app', 'Naval Office No'),
            'naval_rank' => Yii::t('app', 'Naval Rank'),
            'navy_ship_etbd_retired' => Yii::t('app', 'Navy Ship Etbd Retired'),
            'naval_uniform_civil' => Yii::t('app', 'Naval Uniform Civil'),
            'is_anser_vdp' => Yii::t('app', 'Is Anser Vdp'),
            'anser_vdp_rank' => Yii::t('app', 'Anser Vdp Rank'),
            'anser_vdp_office_no' => Yii::t('app', 'Anser Vdp Office No'),
            'is_khudro_jati_gosti' => Yii::t('app', 'Is Khudro Jati Gosti'),
            'phase' => Yii::t('app', 'Phase'),
            'payment_type' => Yii::t('app', 'Payment Type'),
            'is_manula_paid' => Yii::t('app', 'Is Manula Paid'),
            'ref_id' => Yii::t('app', 'Ref ID'),
            'validation_id' => Yii::t('app', 'Validation ID'),
            'order_id_original' => Yii::t('app', 'Order Id Original'),
            'amount' => Yii::t('app', 'Amount'),
            'store_amount' => Yii::t('app', 'Store Amount'),
            'card_type' => Yii::t('app', 'Card Type'),
            'card_no' => Yii::t('app', 'Card No'),
            'trans_date' => Yii::t('app', 'Trans Date'),
            'payment_api' => Yii::t('app', 'Payment Api'),
            'payment_status' => Yii::t('app', 'Is Paid Application ?'),
            'referred_by' => Yii::t('app', 'Referred By'),
            'reference_details' => Yii::t('app', 'Reference Details'),
            'have_reference' => Yii::t('app', 'Have Reference'),
            'relationship' => Yii::t('app', 'Relationship'),
            'is_online_manual' => Yii::t('app', 'Is Online Manual'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Dt'),
            'updated_dt' => Yii::t('app', 'Updated Dt'),
            'application_status' => Yii::t('app', 'Is Cancel Application ?'),
             'agree_payment_terms' => Yii::t('app', 'Agree payment conditions'),
            'birth_registration_no' => Yii::t('app','জন্ম নিবন্ধন নম্বর '),
        ];
    }



    /**
     * {@inheritdoc}
     * @return SailorsQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SailorsQuery(get_called_class());
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
     * number of application in a batch  
     * if allow multipe need return 0 allways 
     */
    public static function numberOfApplication(int $batch_id)
    {
        return  self::find()
            ->where(['batch_id' => $batch_id])
            ->andWhere(['created_by' => Yii::$app->user->identity->id])
            ->activeApplication()->count();
    }

    /**
     * Next roll by batch 
     */
    public static function nextRollByBatchId(int $batchId, string $batch_setting_info_roll_no)
    {
        $candidate_exist = self::find()
            ->select(['MAX(serial_no) as sl_no'])
            ->where(['batch_id' => $batchId])
            ->andWhere(['not', ['exam_date' => null]])
            ->andWhere(['not', ['exam_group' => null]])
            ->andWhere(['not', ['serial_no' => null]]);
        $candidate_exist_data = $candidate_exist->asArray()->one();
        return $roll_no = ($candidate_exist_data['sl_no'] > 1) ? ($candidate_exist_data['sl_no'] + 1) : $batch_setting_info_roll_no;
    }

    /**
     * sailor Batch And Group Wise Count
     * for multiple group 
     */
    public static function sailorBatchAndGroupWiseCount(int $batch_id, int $center_id, int $gender, int $candidate_designation, string $eligible_district, array $batch_config_keys)
    {
        $sailor_batch_wise_count  = self::find()
            ->select(['count(*) as total_candidate', 'batch_config_id'])
            ->where(['batch_id' => $batch_id])
            ->andWhere(['not', ['exam_date' => null]])
            ->andWhere(['not', ['exam_group' => null]])
            ->andWhere(['not', ['serial_no' => null]])
            ->andFilterWhere(['center_id' => $center_id])
            ->andFilterWhere(['gender' => $gender])
            ->andFilterWhere(['in', 'batch_config_id', $batch_config_keys])
            ->andFilterWhere(['application_status' => Constants::STATUS_ACTIVE])->groupBy('batch_config_id')->asArray()->all();
        return  $sailor_batch_wise_count;
        //echo  $sql_r = $sailor_batch_wise_count->createCommand()->getRawSql();  
    }


    public static function getLastRollExamDateByDesignationCenter(int $batchId, $canInfo = array()){
        $examDateByDesig = self::find()   
            ->select(['id','serial_no', 'serial_no','exam_date_id'])        
            ->where(['batch_id' => $batchId])
            ->andFilterWhere(['center_id' => $canInfo['center_id']])
            ->andFilterWhere(['gender' => $canInfo['gender']])
            // ->andFilterWhere(['candidate_designation' => $canInfo['candidate_designation']])
            ->andFilterWhere(['in', 'candidate_designation', explode(',',$canInfo['candidate_designation'])])
            ->andWhere(['not', ['exam_date' => null]])
            ->andWhere(['not', ['exam_group' => null]])
            ->andWhere(['not', ['serial_no' => null]])
            ->orderBy('serial_no DESC')->limit(1);        
        $exam_date_by_desig = $examDateByDesig->asArray()->one();
        return $exam_date_by_desig;
      
    }


    /**
     * Generate log file 
     */
    public static function generateLog($id = null)
    {

        $application_form_fields = [
            'id',
            'eligibility_info_id',
            'app_unique_id',
            'candidate_designation',
            'candidate_type',
            'center_id',
            'batch_id',
            'batch_config_id',
            'exam_date',
            'serial_no',
            'qr_photo',
            'photo',
            'name',
            'father_name',
            'father_occupation',
            'father_nid',
            'mother_name',
            'mother_occupation',
            'current_village',
            'current_word_no',
            'current_union',
            'current_post_office',
            'current_thana',
            'current_district',
            'current_post_code',
            'current_phone',
            'permanent_village',
            'permanent_word_no',
            'permanent_union',
            'permanent_post_office',
            'permanent_thana',
            'permanent_district',
            'permanent_post_code',
            'permanent_phone',
            'guardian_name',
            'guardian_relation',
            'guardian_occupation',
            'guardian_address',
            'dob',
            'age_according_to_circular',
            'gender',
            'religion',
            'marital_status',
            'nationality',
            'jsc_institute_name',
            'jsc_reg_no',
            'jsc_passing_year',
            'jsc_gpa',
            'ssc_institute',
            'ssc_group',
            'ssc_edu_board',
            'ssc_reg_no',
            'ssc_roll_no',
            'ssc_passing_year',
            'ssc_additional_subject',
            'ssc_gpa',
            'hsc_dip_institute',
            'hsc_dip_group',
            'hsc_dip_board',
            'hsc_dip_reg_no',
            'hsc_dip_roll_no',
            'hsc_dip_passing_year',
            'hsc_dip_additional_subject',
            'hsc_dip_gpa',
            'experience_one_institute',
            'experience_one_subject',
            'experience_one_year',
            'experience_one_cert_name',
            'experience_two_institute',
            'experience_two_subject',
            'experience_two_year',
            'experience_two_cert_name',
            'is_freedom_fighter',
            'freedom_fighter_relation',
            'is_child_of_naval_officer',
            'naval_father_name',
            'naval_office_no',
            'naval_rank',
            'navy_ship_etbd_retired',
            'is_anser_vdp',
            'anser_vdp_rank',
            'anser_vdp_office_no',
            'is_khudro_jati_gosti',
        ];
        $data = self::find()->select($application_form_fields)->where(['id' => $id])->asArray()->one();
        if ($data) {
            $base_path = Yii::getAlias('@rootDirFilUpload') . '/dummy/' . $data['batch_id'] . '.txt';
            if (!file_exists($base_path)) {
                if (!file_exists(Yii::getAlias('@rootDirFilUpload') . '/dummy/'))
                    mkdir(Yii::getAlias('@rootDirFilUpload') . '/dummy/', 0777, true);
                $handle = fopen($base_path, "w");
                fclose($handle);
            }

            $file = file_get_contents($base_path, true);
            // old data 
            $old_data = json_decode($file, true);
            if ($old_data) {
                $old_data[$data['serial_no']] = $data;
                $data_arr =   $old_data;
            } else
                $data_arr[$data['serial_no']] = $data;

            file_put_contents($base_path, json_encode($data_arr, true));
            //  $file = file_get_contents( $base_path, true); 

        }

        return true;
    }


     // for check image exist or not
    public static function getTotalCandidateByExamDate($batch_id, $exam_date_id, $exam_date = null)
    {
        $query = self::find()
            ->where([
                'batch_id' => $batch_id,
                'exam_date_id' => $exam_date_id,
                'application_status' => Constants::STATUS_ACTIVE
            ])
            ->andWhere(['not', ['serial_no' => null]]);
        if ($exam_date !== null) {
            $query->andWhere(['exam_date' => $exam_date]);
        }
        return (int) $query->count();
    }

}
