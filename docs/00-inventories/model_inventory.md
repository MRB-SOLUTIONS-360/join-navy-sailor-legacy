# Model Inventory — `common/models/`, `backend/models/`, `frontend/models/`, `console/models/`

Scope: this is a Yii2 2.0 advanced-template app (`yii\db\ActiveRecord`, not Eloquent). All 28 files under `common/models/` (27 models + `common/models/payment/SSLPayment.php`), all 12 files under `backend/models/` (Search/Reference variants), all 7 files under `frontend/models/` (form models — one, `RefundForm.php`, is a genuinely empty 0-byte file), and `console/models/` (empty directory, no files). **47 files documented below.**

Method: every file was opened and read in full (via parallel research passes). `tableName()`, `rules()`, `attributeLabels()`, and relation methods are quoted/derived directly from source — no schema was guessed. "Migration" cross-reference was produced by matching each model's table name against `createTable()` calls in `console/migrations/*.php` (only 2 migration files exist in this repo, listed in full at the end of this document).

**Critical modernization risk, stated up front**: of the ~29 distinct tables referenced by these models, only **one** — `user` — has any migration history in this repo (`m130524_201442_init.php` creates it with 9 columns; `m190124_110200_add_verification_token_column_to_user_table.php` adds one more). Every other table (`sailors`, `de_sailors`, `sailor_batchs`, `sailor_batch_configuration`, `sailor_batch_configuration_exam_date`, `sailor_centers`, `sailor_cent_dist_mapping`, `districts`, `unions`, `upozilas`, `can_designation`, `can_eligibility_check_info`, `eligibility`, `subjects`, `send_sms`, `session`, etc.) exists **only in a live database dump not present in this repo**. Schema for those tables must be inferred entirely from each model's `rules()`/`attributeLabels()`/`@property` docblocks — there is no way to reconstruct exact column types, defaults, indexes, or foreign keys from this codebase alone. Even the one migrated table (`user`) has drifted: application code (`SignupForm`, `backend/models/UserSearch.php`) reads/writes columns (`birth_registration_no`, `phone_no`, `dob`, `user_group`, `user_type`, `os`, `login_zone`, `created_dt`, `updated_dt`) that neither migration file creates.

---

## Part 1 — `common/models/` (main model set)

## 1. `CanDesignation.php`

- **Class**: `common\models\CanDesignation extends \yii\db\ActiveRecord`
- **Table**: `{{%can_designation}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['candidate_type', 'name_en'], 'required'],
        [['name_en', 'name_bn'], 'filter', 'filter' => 'strip_tags'],
        ['description', 'filter', 'filter' => function ($value) {
            return HtmlPurifier::process($value);
        }],
        [['candidate_type', 'created_by', 'updated_by', 'status'], 'integer'],
        [['created_dt', 'updated_dt', 'description'], 'safe'],
        [['name_bn', 'name_en'], 'string', 'max' => 255],
    ];
}
```
- **attributeLabels()**: 10 boilerplate `Yii::t('app', ...)` entries (`id, candidate_type, name_bn, name_en, description, created_by, updated_by, created_dt, updated_dt, status`).
- **Relations**: `getCreatedBy()` → `hasOne(User::class, ['id' => 'created_by'])`; `getUpdatedBy()` → `hasOne(User::class, ['id' => 'updated_by'])`.
- **Other**: `beforeSave()` stamps `created_by`/`created_dt` (insert) or `updated_by`/`updated_dt` (update), fallback user id `1`/`2`. Static helpers: `getAllActiveDesignation()`, `getAllDesignation()`, `getAllDesignationForEligibilityPage()`, `getAllDesignationSession()` (session-cached id→name map). Doc comment: `candidate_type` 1=Sailor, 2=DeSailor, 3=Officer Cadet.
- **Migration**: none — `can_designation` is not created by either migration file.

---

## 2. `CanEligibilityCheckInfo.php`

- **Class**: `common\models\CanEligibilityCheckInfo extends \yii\db\ActiveRecord`
- **Table**: `{{%can_eligibility_check_info}}`
- **Scenarios**: `SCENARIO_PERSONAL_INFO = 'personal_info'`, `SCENARIO_ACADEMIC_INFO = 'academic_info'` (used as `on =>` targets inside `rules()`, no explicit `scenarios()` override).
- **Public non-column properties**: `$height_feet`, `$height_inch`.
- **rules()**:
```php
public function rules()
{
    return [
        [['candidate_type', 'dob', 'gender', 'district', 'height_feet', 'height_inch', 'marital_status'], 'required', 'on' => self::SCENARIO_PERSONAL_INFO],
        [['p_o_no', 'rank'], 'validatePoNoRank', 'skipOnError' => false, 'skipOnEmpty' => false],
        [['p_o_no', 'rank'], 'validateEnglishInput', 'skipOnError' => false, 'skipOnEmpty' => false],
        ['height_feet', 'integer', 'min' => 5, 'max' => 7],
        ['height_inch', 'integer', 'min' => 0, 'max' => 11],
        [['chest_normal', 'chest_expanded'], 'integer', 'min' => 25, 'max' => 38],
        [['jsc_result'], 'required', 'on' => self::SCENARIO_ACADEMIC_INFO],
        ['ssc_equv_result', 'number', 'min' => 2.5, 'max' => 5],
        ['ssc_equv_result', 'validateSscEquvResult', 'skipOnError' => false, 'skipOnEmpty' => false],
        ['ssc_equv_group', 'validateSscEquvGroup', 'skipOnError' => false, 'skipOnEmpty' => false],
        ['trade_course_subject', 'validateTradeCourseSubject', 'skipOnError' => false, 'skipOnEmpty' => false],
        ['have_trade_course_experience', 'validateHaveTradeCourseExperience', 'skipOnError' => false, 'skipOnEmpty' => false],
        ['ssc_equv_is_biology_include', 'validateBiologyIsInclude', 'skipOnError' => false, 'skipOnEmpty' => false],
        ['ssc_equv_is_trade_course_complete', 'validateTradeCourseComplete', 'skipOnError' => false, 'skipOnEmpty' => false],
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
```
- **attributeLabels()**: ~39 entries, notably `p_o_no` → "P No / O No", `eye_status` → "Eye Power".
- **Relations**: none.
- **Other**: 8 custom inline validators (`validatePoNoRank`, `validateEnglishInput`, `validateSscEquvResult`, `validateSscEquvGroup`, `validateBiologyIsInclude`, `validateTradeCourseComplete`, `validateTradeCourseSubject`, `validateHaveTradeCourseExperience`) enforcing the eligibility pre-check business rules (Bangladeshi-navy-specific: biology requirement when SSC group is Science/Vocational, trade-course completion/experience gating, etc.). `beforeSave()` forces `status = STATUS_ACTIVE` on insert.
- **Migration**: none.

---

## 3. `ChangePassword.php`

- **Class**: `common\models\ChangePassword extends \yii\base\Model` (form model, no table)
- **Properties**: `$newpassword`, `$repeatnepassword`
- **rules()**:
```php
public function rules()
{
   return [
      [['newpassword', 'repeatnepassword'], 'string', 'min' => 6],
      [['newpassword', 'repeatnepassword'], 'required'],
      ['repeatnepassword', 'compare', 'compareAttribute' => 'newpassword'],
   ];
}
```
- **attributeLabels()**: `newpassword` → "New Password", `repeatnepassword` → "Retype Password".
- **Relations**: none (plain form model).
- **Migration**: n/a (not a table-backed model).

---

## 4. `DeSailors.php`

- **Class**: `common\models\DeSailors extends \yii\db\ActiveRecord`
- **Table**: `{{%de_sailors}}`
- **Scenarios** (constants, referenced via `on =>` in `rules()`, no `scenarios()` override): `PAYMENT = 'select_payment_type'`, `ACADEMIC_DE_SAILOR_ARTIFICER = 'artificer'`, `ACADEMIC_DE_SAILOR_DOCKYARD = 'dockyard'`, `ACADEMIC_INFO_JSC = 'academic_info_jsc'`, `ACADEMIC_INFO_JSC_SSC = 'academic_info_jsc_ssc'`, `PERSONAL_INFO = 'personal_information'`, `PERSONAL_INFO_WITH_IMAGE = 'personal_information_with_image'`
- **Public non-column properties**: `$skipTeleTalkValidation = true`, `$agree_payment_terms`, `$exam_center_name`, `$academic_info_already_used_in_ssc`, `$academic_info_already_used_in_jsc`, `$encryption_fields_for_personal_info = ['father_nid', 'current_phone', 'permanent_phone', 'father_phone', 'mother_phone', 'guardian_phone']`.
- **rules()** — full scenario-gated ruleset (personal info, payment, academic-track validation, DE-specific trade fields), quoted verbatim:
```php
public function rules()
{
    return [
        [['eligibility_info_id', 'app_unique_id', 'candidate_type'], 'required'],
        [['payment_type', 'agree_payment_terms'], 'required', 'on' => self::PAYMENT],
        [['birth_registration_no'], 'birthRegistrationNoValidation', 'on' => self::PAYMENT,'skipOnError' => false, 'skipOnEmpty' => false],

        [['jsc_institute_name','jsc_reg_no','jsc_passing_year','jsc_gpa','ssc_edu_board','ssc_roll_no','ssc_reg_no','ssc_passing_year','diploma_trade_institute','diploma_trade_course','diploma_trade_registration_roll','diploma_trade_gpa'], 'sailorArtificerValidation', 'on' => self::ACADEMIC_DE_SAILOR_ARTIFICER, 'skipOnError' => false, 'skipOnEmpty' => false],
        [['jsc_institute_name','jsc_reg_no','jsc_passing_year','jsc_gpa','ssc_edu_board','ssc_roll_no','ssc_reg_no','ssc_passing_year','diploma_trade_institute','diploma_trade_course','diploma_trade_registration_roll','diploma_trade_gpa'], 'sailorArtificerValidation', 'on' => self::ACADEMIC_DE_SAILOR_DOCKYARD, 'skipOnError' => false, 'skipOnEmpty' => false],

        // Personal Information Validation (identical field list applied twice, once per PERSONAL_INFO / PERSONAL_INFO_WITH_IMAGE scenario)
        [['exam_center_name','name','father_name','father_occupation','mother_name','mother_occupation','current_village','current_union','current_post_office','current_thana','current_district','current_post_code','father_phone','mother_phone','permanent_village','permanent_union','permanent_post_office','permanent_thana','permanent_district','permanent_post_code','permanent_phone','dob','age_according_to_circular','religion','marital_status','nationality','is_freedom_fighter','is_child_of_naval_officer','is_anser_vdp','is_khudro_jati_gosti','ssc_institute','diploma_trade_institute','diploma_trade_course','diploma_trade_registration_roll','diploma_trade_gpa','name_bangla','father_name_bangla','mother_name_bangla'], 'personalInformationValidate', 'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
        [['exam_center_name','name','father_name','father_occupation','mother_name','mother_occupation','current_village','current_union','current_post_office','current_thana','current_district','current_post_code','father_phone','mother_phone','permanent_village','permanent_union','permanent_post_office','permanent_thana','permanent_district','permanent_post_code','permanent_phone','dob','age_according_to_circular','religion','marital_status','nationality','is_freedom_fighter','is_child_of_naval_officer','is_anser_vdp','is_khudro_jati_gosti','ssc_institute','diploma_trade_institute','diploma_trade_course','diploma_trade_registration_roll','diploma_trade_gpa','name_bangla','father_name_bangla','mother_name_bangla'], 'personalInformationValidate', 'on' => self::PERSONAL_INFO_WITH_IMAGE, 'skipOnError' => false, 'skipOnEmpty' => false],

        // English-only, numeric-only, decimal-only, Bangla-only, phone, duplicate-check, gender/marital/age/naval-child validators (mirrors Sailors.php pattern) — see below
        [['jsc_institute_name','diploma_trade_institute','diploma_trade_gpa','name','father_name','mother_name','father_occupation','mother_occupation','current_village','current_word_no','current_union','current_post_office','current_thana','permanent_village','permanent_union','permanent_word_no','permanent_post_office','permanent_thana','guardian_name','guardian_relation','guardian_occupation','guardian_address','experience_one_institute','experience_one_subject','experience_one_cert_name','experience_two_institute','experience_two_subject','experience_two_cert_name','freedom_fighter_relation','naval_father_name','anser_vdp_rank','anser_vdp_office_no','ssc_edu_board','ssc_group','ssc_institute','ssc_additional_subject','hsc_dip_institute','hsc_dip_group','hsc_dip_board','hsc_dip_additional_subject','jsc_gpa'], 'onlyInputEnglishCharacterValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
        [['jsc_passing_year','ssc_roll_no','ssc_reg_no','ssc_passing_year','hsc_dip_roll_no','hsc_dip_reg_no','hsc_dip_passing_year','diploma_trade_course','father_nid','permanent_phone','current_phone','current_post_code','permanent_post_code','experience_one_year','experience_two_year','hsc_dip_reg_no','hsc_dip_roll_no','hsc_dip_passing_year'], 'onlyNumberInputValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
        [['jsc_reg_no','diploma_trade_registration_roll'], 'onlyNumberInputValidationWithBackslash', 'skipOnError' => false, 'skipOnEmpty' => false],
        [['jsc_institute_name','jsc_institute_name','jsc_passing_year','jsc_gpa','ssc_edu_board','ssc_roll_no','ssc_reg_no','ssc_passing_year','hsc_dip_board','hsc_dip_roll_no','hsc_dip_reg_no','hsc_dip_passing_year'], 'checkDuplicationApplicationBySameAcInformation', 'skipOnError' => false, 'skipOnEmpty' => false],
        [['father_name_bangla','mother_name_bangla','name_bangla'], 'banglaInputCharacterValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
        [['current_phone', 'permanent_phone','father_phone','mother_phone','guardian_phone' ], 'phoneNoValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
        [['permanent_phone'], 'permanentPhoneUniqueCheck', 'skipOnError' => false, 'skipOnEmpty' => false],
        ['gender', 'genderValidation', 'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
        ['gender', 'genderValidation', 'on' => self::PERSONAL_INFO_WITH_IMAGE, 'skipOnError' => false, 'skipOnEmpty' => false],
        ['marital_status', 'maritalStatusValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
        ['dob', 'ageValidation', 'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
        ['dob', 'ageValidation', 'on' => self::PERSONAL_INFO_WITH_IMAGE, 'skipOnError' => false, 'skipOnEmpty' => false],
        [['naval_father_name', 'naval_uniform_civil', 'naval_office_no', 'naval_rank'], 'isNavalChildValidation',  'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
        [['naval_father_name', 'naval_uniform_civil', 'naval_office_no', 'naval_rank'], 'isNavalChildValidation',  'on' => self::PERSONAL_INFO_WITH_IMAGE, 'skipOnError' => false, 'skipOnEmpty' => false],
        [['ssc_gpa','hsc_dip_gpa'], 'onlyDecimalNumberInputValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
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
        ['photo', 'image', 'skipOnError' => false, 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxSize' => 1024 * 500, 'minWidth' => 300, 'maxWidth' => 300, 'minHeight' => 300, 'maxHeight' => 300, 'on' => self::PERSONAL_INFO],
        [['photo'], 'image', 'skipOnError' => false, 'skipOnEmpty' => true, 'extensions' => 'png, jpg', 'maxSize' => 1024 * 500, 'minWidth' => 300, 'maxWidth' => 300, 'minHeight' => 300, 'maxHeight' => 300, 'on' => self::PERSONAL_INFO_WITH_IMAGE],
    ];
}
```
- **attributeLabels()**: ~110-entry English label array plus a parallel `attributeLabelBangla()` static method (~90 Bangla entries used for validator error messages).
- **Relations**: **none** — no `getXxx()` hasOne/hasMany methods; related lookups (`SailorBatchConfiguration`, `Eligibility`, `SailorBatchs`) happen via direct static `::find()` calls inside custom validators, not declared AR relations.
- **Other**: `find()` overridden to return `SailorsQuery` (same shared query class `Sailors` uses: `return new SailorsQuery(get_called_class());`). `beforeSave()` stamps audit fields. Static helpers mirror `Sailors.php` exactly by name/signature: `numberOfApplication()`, `nextRollByBatchId()`, `getLastRollExamDateByDesignationCenter()`, `sailorBatchAndGroupWiseCount()`, `generateLog()`, `getTotalCandidateByExamDate()`.
- **Migration**: none — `de_sailors` is not created by either migration file.

**→ See "Sailors vs. DeSailors" comparison below.**

---

## 5. `DeSailorsSearch.php`

- **Class**: `common\models\DeSailorsSearch extends DeSailors` (Search variant, Gii pattern: subclasses the AR model itself)
- **rules()**: loosens all scenario-gated `required`/custom-validator rules to `integer`/`safe`/`number`:
```php
public function rules()
{
    return [
        [['id', 'eligibility_info_id', 'candidate_designation', 'candidate_type', 'center_id', 'batch_id', 'batch_config_id', 'exam_group', 'religion', 'gender', 'marital_status', 'ac_type_ssc', 'hsc_or_diploma', 'is_freedom_fighter', 'freedom_fighter_relation', 'is_child_of_naval_officer', 'is_departmental_candidate', 'naval_uniform_civil', 'is_anser_vdp', 'is_khudro_jati_gosti', 'phase', 'is_manula_paid', 'payment_status', 'have_reference', 'is_online_manual', 'created_by', 'updated_by', 'application_status','diploma_trade_course'], 'integer'],
        [['app_unique_id', 'exam_date', 'serial_generate_date', 'serial_no', 'eligible_district', 'district', 'name', 'father_name', /* ...all remaining text/date fields... */ 'created_dt', 'updated_dt'], 'safe'],
        [['father_income', 'amount', 'store_amount'], 'number'],
    ];
}
```
- **scenarios()**: bypassed → `return Model::scenarios();` (default single scenario, all safe attributes usable regardless of `DeSailors`'s scenario constants).
- **Search/filter fields** (`search($params)`): exact-match on `id, eligibility_info_id, candidate_designation, candidate_type, center_id, batch_id, batch_config_id, exam_date, exam_group, serial_generate_date, father_income, dob, religion, gender, marital_status, ac_type_ssc, hsc_or_diploma, is_freedom_fighter, freedom_fighter_relation, is_child_of_naval_officer, is_departmental_candidate, naval_uniform_civil, is_anser_vdp, is_khudro_jati_gosti, phase, is_manula_paid, amount, store_amount, trans_date, diploma_trade_course, have_reference, is_online_manual, created_by, updated_by, created_dt, updated_dt, application_status, payment_status` (defaults to `Constants::PAYMENT_PAID` when unset); `like`-match on ~70 text fields (`app_unique_id, serial_no, eligible_district, district, name, father_name, ...` full academic/experience/reference field set).
- **Migration**: n/a (search wrapper, same table as `DeSailors`).

---

## 6. `Districts.php`

- **Class**: `common\models\Districts extends \yii\db\ActiveRecord`
- **Table**: `{{%districts}}`
- **behaviors()**: `SlugBehavior` (`skeeks\yii2\slug\SlugBehavior`) — auto-generates `slug` from `name_en`, 5–20 chars, lowercase, `-` separator, `ensureUnique`.
- **rules()**:
```php
public function rules()
{
    return [
        [['name_en', 'division'], 'required'],
        [['slug', 'name_en'], 'unique'],
        [['name_en', 'name_bn'], 'filter', 'filter' => 'strip_tags'],
        [['created_by', 'updated_by', 'status'], 'integer'],
        [['created_dt', 'updated_dt'], 'safe'],
        [['slug'], 'string', 'max' => 200],
        [['name_en', 'name_bn'], 'string', 'max' => 255],
    ];
}
```
- **attributeLabels()**: 10 entries (`id, division, slug, name_en, name_bn, created_by, updated_by, created_dt, updated_dt, status`).
- **Relations**: **none** — despite `Unions`/`Upozilas` referencing `district_id`, `Districts` declares no `getUnions()`/`getUpozilas()` relation; all cross-lookups go through static helpers instead.
- **Other**: `beforeSave()` audit stamping. Static helpers: `getAllActiveDistrict()`, `getDistrictsList()` (bug: `$isActive` param declared but never applied to the query), `getAllActiveDistrictBySlug()`, `getAllDistrict()`, `getDistrictBySlug()` (CSV-slug → comma-joined names), `districtSessionSlug()` (session-cached), `findOneBySlug()`, `getIdBySlug()`. Doc comment: `status` 1=Active, 2=Inactive.
- **Migration**: none.

---

## 7. `DownloadDocuments.php`

- **Class**: `common\models\DownloadDocuments extends \yii\base\Model` (form model)
- **Properties**: `$download_by`, `$application_id`, `$batch`, `$serial_no`, `$dob`
- **rules()**:
```php
public function rules()
{
    return [
        [['download_by'], 'required'],
        [['application_id','batch','serial_no','dob'],'safe'],
        ['application_id','applicationIdValidation','skipOnError' => false, 'skipOnEmpty' => false],
        [['batch','serial_no','dob'],'batchSerialValidation','skipOnError' => false, 'skipOnEmpty' => false],
    ];
}
```
- **attributeLabels()**: `download_by` → "Document Download By", `application_id` → "Application ID", `batch` → "Batch", `serial_no` → "Serial No", `dob` → "Date of Birth".
- **Relations**: none. Two inline validators (`applicationIdValidation`, `batchSerialValidation`) branch on `download_by` (1 = lookup by application id, 2 = lookup by batch/serial/dob).
- **Migration**: n/a.

---

## 8. `Eligibility.php`

- **Class**: `common\models\Eligibility extends \yii\db\ActiveRecord`
- **Table**: `{{%eligibility}}`
- **rules()**:
```php
public function rules()
{
    return [
        ['candidate_designation', 'unique', 'targetAttribute' => ['candidate_designation', 'candidate_type']],
        [['candidate_type', 'candidate_designation', 'min_age', 'max_age', 'dept_can_max_age', 'marital_status', 'gender', 'jsc_result', 'is_required_biology','is_required_trade_course_experience'], 'required'],
        [['height_male', 'weight_male', 'height_female', 'weight_female', 'chest_normal_male', 'chest_extended_male', 'chest_normal_female', 'chest_extended_female', 'diploma_result'], 'number'],
        [['ssc_result', 'hsc_result', 'diploma_result'], 'number', 'max' => 5],
        [['candidate_type', 'candidate_designation', 'jsc_result', 'is_required_biology', 'is_allow_trade_course', 'is_allow_diploma', 'is_allow_hons_appeared', 'is_allow_masters_appeared', 'created_by', 'updated_by', 'status'], 'integer'],
        [['ssc_ac_group', 'hsc_ac_group', 'created_dt', 'updated_dt', 'hons_diploma_subject', 'trade_course_subject'], 'safe'],
        [['min_age', 'max_age', 'dept_can_max_age', 'height_male', 'weight_male', 'height_female', 'weight_female', 'chest_normal_male', 'chest_extended_male', 'chest_normal_female', 'chest_extended_female', 'ssc_result', 'hsc_result', 'diploma_result', 'hons_result', 'masters_result', 'masters_subject'], 'string', 'max' => 50],
    ];
}
```
- **attributeLabels()**: 30 entries, notable: `dept_can_max_age` → "Dept. candidate max age", `ssc_result`/`hsc_result` → "SSC/HSC GPA", `is_allow_hons_appeared`/`is_allow_masters_appeared` labels literally read `'1=>Yes, 2=>No'` (Gii-boilerplate artifact, not fixed up).
- **Relations**: `getCreatedBy()`, `getUpdatedBy()` → `hasOne(User::class, ...)`; `getCanDesignation()` → `hasOne(CanDesignation::class, ['id' => 'candidate_designation'])` — the only cross-entity relation declared among the eligibility/designation/subject models.
- **Other**: `beforeSave()` implodes array fields (`marital_status, gender, ssc_ac_group, hsc_ac_group, hons_diploma_subject, trade_course_subject`) to CSV, stamps audit fields. `eligibilityBySession()` static (misleadingly named — session-caching code is commented out, so it hits the DB every call despite the name).
- **Migration**: none.

---

## 9. `LoginForm.php`

- **Class**: `common\models\LoginForm extends \yii\base\Model` (form model)
- **Properties**: `$username`, `$password`, `$rememberMe = true`, `$captcha`, `$user_type`, private `$_user`
- **rules()**:
```php
public function rules()
{
    return [
        ['username', 'trim'],
        [['username', 'password', 'captcha'], 'required'],
        ['rememberMe', 'boolean'],
        ['password', 'validatePassword'],
    ];
}
```
- **attributeLabels()**: `username` → "Username", `captcha` → "Answer".
- **Other**: `validatePassword()` also checks `user->user_type != $this->user_type`. `login()` sets `login_zone`/`os` on the `User` record before calling `Yii::$app->user->login()`. `getLoginAddress($ip)` calls the third-party `http://ipinfo.io/{ip}` geolocation service via plain-HTTP `file_get_contents()` on every login — flagged below as a hardening concern (outbound call to an unauthenticated third party over HTTP, no timeout/error handling shown).
- **Migration**: n/a.

---

## 10. `ResetPassword.php`

- **Class**: `common\models\ResetPassword extends \yii\base\Model`
- **Properties**: `$username`, `$dob`
- **rules()**:
```php
public function rules()
{
   return [
      [['username', 'dob'], 'required'],
   ];
}
```
- **attributeLabels()**: `username` → "Username", `dob` → "Date of Birth".
- **Relations**: none — no auth logic in the model itself (presumably handled in a controller).
- **Migration**: n/a.

---

## 11. `SailorBatchConfiguration.php` (+ `SailorBatchConfigurationQuery.php`)

- **Class**: `common\models\SailorBatchConfiguration extends \yii\db\ActiveRecord`
- **Table**: `{{%sailor_batch_configuration}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['batch_id', 'center_id', 'candidate_type', 'gender', 'marital_status', 'exam_group', 'group_no_of_candidate','team', 'check_max_candidate'], 'required'],
        [['batch_id', 'center_id', 'candidate_type', 'roll_swap_in_group', 'created_by', 'updated_by', 'status','check_max_candidate'], 'integer'],
        [['du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'], 'integer'],
        [['exam_date', 'created_dt', 'updated_dt', 'district_slug', 'candidate_designation', 'gender', 'marital_status', 'exam_group'], 'safe'],
        [['group_start_roll', 'group_end_roll', 'group_no_of_candidate', 'du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'], 'string', 'max' => 50],
        [['group_start_roll', 'group_end_roll'], 'validateOnRollSwap', 'skipOnError' => false, 'skipOnEmpty' => false],
    ];
}
```
- **attributeLabels()**: 20 entries; notable: `du_uc_can_total` → "DEUC (Seaman / Communication / Technical)", `pertol_store_can_total` → "Petrolman / Writer / Store", `modc_can_total` → "MODC (N)" — these are Bangladesh Navy sailor trade-branch quota totals per batch/center/group.
- **Relations**: `getCreatedBy()`, `getUpdatedBy()` → `User`; `getBatch()` → `hasOne(SailorBatchs::class, ['id' => 'batch_id'])`; `getCenter()` → `hasOne(SailorCenters::class, ['id' => 'center_id'])`; `getExamDates()` → `hasMany(SailorBatchConfigurationExamDate::class, ['batch_configuration_id' => 'id'])`.
- **Other**: `find()` overridden to return `SailorBatchConfigurationQuery`. `beforeSave()` implodes `gender, marital_status, candidate_designation, district_slug` to CSV, stamps audit fields. Static helpers: `batchIdAndCenterIdByApplyDistrictAndDepartment()`, `configurationByBatchCenterGenderCanDesigDistrictSlug()`, `configurationByBatchCenterGenderCanDesigDistrictSlugAll()`.
- **`SailorBatchConfigurationQuery.php`**: extends `\yii\db\ActiveQuery`; adds `isActive()` scope (`andFilterWhere(['status' => Constants::STATUS_ACTIVE])`).
- **Migration**: none — `sailor_batch_configuration` is not created by either migration file.

---

## 12. `SailorBatchConfigurationExamDate.php`

- **Class**: `common\models\SailorBatchConfigurationExamDate extends \yii\db\ActiveRecord`
- **Table**: `{{%sailor_batch_configuration_exam_date}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['exam_date', 'max_candidate_this_date', 'created_by', 'updated_by', 'created_dt', 'updated_dt'], 'default', 'value' => null],
        [['status'], 'default', 'value' => 1],
        [['batch_configuration_id'], 'required'],
        [['batch_configuration_id', 'status', 'created_by', 'updated_by'], 'integer'],
        [['exam_date', 'created_dt', 'updated_dt'], 'safe'],
        [['max_candidate_this_date'], 'number'],
    ];
}
```
- **attributeLabels()**: 8 entries, plain strings (not `Yii::t()`), e.g. `exam_date` → "Exam Date - ##".
- **Relations**: none declared on this side (inverse of `SailorBatchConfiguration::getExamDates()`).
- **Other**: `beforeSave()` audit stamping. `getListByConfigurationId()` — active exam dates for a configuration, `indexBy('id')`. `getNextAvailableExamDate()` — assigns candidates to the next exam date under `max_candidate_this_date`, wraps circularly, bulk-flips over-limit dates to `status=2`. Dead code: a `validateExamDate()` method (with a stray `echo 'ds';` debug statement) is defined but never wired into `rules()`.
- **Migration**: none.

---

## 13. `SailorBatchs.php`

- **Class**: `common\models\SailorBatchs extends \yii\db\ActiveRecord`
- **Table**: `{{%sailor_batchs}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['candidate_type', 'allow_refund', 'name_en', 'circular_date', 'circular_close_date', 'circular_start_date', 'payment_amount', 'start_roll', 'secrate_key'], 'required'],
        [['candidate_type', 'is_active_batch', 'allow_application_after_close', 'is_batch_live_mode', 'payment_mode', 'created_by', 'updated_by', 'status'], 'integer'],
        [['circular_date', 'circular_close_date', 'created_dt', 'updated_dt'], 'safe'],
        [['roll_from'], 'string'],
        [['name_en', 'name_bn', 'description'], 'string', 'max' => 255],
        [['secrate_key'], 'string', 'max' => 50],
        [['start_roll', 'payment_amount', 'next_start_roll', 'next_start_roll_after'], 'number'],
        ['payment_amount', 'in', 'range' => StaticMethod::paymentAmount()],
    ];
}
```
- **attributeLabels()**: 20+ entries (`candidate_type, name_en, name_bn, description, circular_date, circular_start_date, circular_close_date, circular_media, media_for_api, roll_from, start_roll, next_start_roll, next_start_roll_after, is_active_batch, allow_application_after_close, is_batch_live_mode, allow_refund, secrate_key, payment_mode, payment_amount, ...`). Note: `circular_media`/`media_for_api` have labels but no corresponding rule/`@property` — likely leftover virtual attributes.
- **Relations**: `getCreatedBy()`, `getUpdatedBy()` → `User`.
- **Other**: `find()` overridden to return a scope-query class `common\models\scopeQuery\SailorBatchs` (aliased `ScopeQuerySailorBatchs` in this file), which provides chainable scopes (`isCircularCloseDateGraterCurrentDate()`, `isActive()`, `isActiveBatch()`) used by `isBatchActiveAndRunning()`. `beforeSave()` reformats circular dates to `Y-m-d H:i`. Static helpers: `getAllActiveBatch()`, `getAllBatch()`, `isBatchActiveAndRunning()`, `batchById()`, `isCandidateContinueApplication()`, `getAllBatchSession()`.
- **Migration**: none.

---

## 14. `SailorCentDistMapping.php`

- **Class**: `common\models\SailorCentDistMapping extends \yii\db\ActiveRecord`
- **Table**: `{{%sailor_cent_dist_mapping}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['center_id', 'district_slug','candidate_type'], 'required'],
        [['center_id', 'created_by', 'updated_by', 'status','candidate_type'], 'integer'],
        [['created_dt', 'updated_dt','district_slug'], 'safe'],
        [['center_id'], 'exist', 'skipOnError' => true, 'targetClass' => SailorCenters::class, 'targetAttribute' => ['center_id' => 'id']],
        [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
        [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
    ];
}
```
- **attributeLabels()**: 9 entries (`center_id` → "Center", `candidate_type` → "Sailor Type", `district_slug` → "District List").
- **Relations**: `getCenter()` → `hasOne(SailorCenters::class, ['id' => 'center_id'])`; `getCreatedBy()`, `getUpdatedBy()` → `User`.
- **Other**: this model is the mapping table linking exam centers (`sailor_centers`) to a CSV list of district slugs (`district_slug`), not a real FK junction row per district. `GetAllAssignedDistrictByCenter()` decodes the CSV and joins against `Districts` by `slug`.
- **Migration**: none.

---

## 15. `SailorCenters.php`

- **Class**: `common\models\SailorCenters extends \yii\db\ActiveRecord`
- **Table**: `{{%sailor_centers}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['candidate_type', 'name_en'], 'required'],
        [['name_en', 'name_bn'], 'filter', 'filter' => 'strip_tags'],
        [['candidate_type', 'created_by', 'updated_by', 'status'], 'integer'],
        [['created_dt', 'updated_dt'], 'safe'],
        [['name_en', 'name_bn'], 'string', 'max' => 255],
    ];
}
```
- **attributeLabels()**: 9 entries.
- **Relations**: `getCreatedBy()`, `getUpdatedBy()` → `User` (no relation back to `SailorCentDistMapping` — FK direction only declared on the mapping side).
- **Other**: doc comment: `candidate_type` 1=Sailor, 2=De Sailor. Static helpers `getAllActiveCenter()`, `getAllCenter()`, `getAllCenterSession()`.
- **Migration**: none.

---

## 16. `Sailors.php` (+ `SailorsQuery.php`) — the central/largest entity

- **Class**: `common\models\Sailors extends \yii\db\ActiveRecord` (1315 lines — the largest model in the app)
- **Table**: `{{%sailors}}`
- **Scenario constants** (no `scenarios()` override; used via `on =>` in `rules()`): `PAYMENT = 'select_payment_type'`, `ACADEMIC_INFO_JSC = 'academic_info_jsc'`, `ACADEMIC_INFO_JSC_SSC = 'academic_info_jsc_ssc'`, `PERSONAL_INFO = 'personal_information'`, `PERSONAL_INFO_WITH_IMAGE = 'personal_information_with_image'`.
- **Public non-column properties**: `$encryption_fields_for_personal_info = ['father_nid', 'current_phone', 'permanent_phone', 'father_phone', 'mother_phone', 'guardian_phone']`, `$agree_payment_terms`, `$exam_center_name`, `$academic_info_already_used_in_ssc`, `$academic_info_already_used_in_jsc`, `$list_custom_filter`.
- **rules()** — full scenario-gated ruleset (structurally the near-twin of `DeSailors::rules()` minus the DE trade-course fields; personal info, JSC/SSC academic validation, payment, ~15 custom inline validators: `paymentTypeValidate, birthRegistrationNoValidation, jscAcademicInfoValidate, jscSscAcademicInfoValidate, personalInformationValidate, onlyInputEnglishCharacterValidation, banglaInputCharacterValidation, onlyNumberInputValidation, onlyNumberInputValidationWithBackslash, onlyDecimalNumberInputValidation, phoneNoValidation, permanentPhoneUniqueCheck, checkDuplicationApplicationBySameAcInformation, genderValidation, maritalStatusValidation, ageValidation, isNavalChildValidation, isAnserVdpValidation, freedomFighterRelationValidation` — the last is a dormant no-op, its body is commented out and it always returns `true`). Key excerpts:
```php
[['eligibility_info_id', 'app_unique_id', 'candidate_type'], 'required'],
[['payment_type', 'agree_payment_terms'], 'required', 'on' => self::PAYMENT],
[['birth_registration_no'], 'birthRegistrationNoValidation', 'on' => self::PAYMENT, 'skipOnError' => false, 'skipOnEmpty' => false],
['payment_type', 'paymentTypeValidate', 'on' => self::PAYMENT, 'skipOnError' => false, 'skipOnEmpty' => false],
[['jsc_institute_name', 'jsc_reg_no', 'jsc_passing_year', 'jsc_gpa'], 'jscAcademicInfoValidate', 'on' => self::ACADEMIC_INFO_JSC, 'skipOnError' => false, 'skipOnEmpty' => false],
[['jsc_institute_name', 'jsc_reg_no', 'jsc_passing_year', 'jsc_gpa', 'ssc_edu_board', 'ssc_roll_no', 'ssc_reg_no', 'ssc_passing_year'], 'jscSscAcademicInfoValidate', 'on' => self::ACADEMIC_INFO_JSC_SSC, 'skipOnError' => false, 'skipOnEmpty' => false],
// [30+ field] personal-info validation block, applied identically under PERSONAL_INFO and PERSONAL_INFO_WITH_IMAGE
// English-only / number-only / decimal-only / Bangla-only character-class validators over ~50 fields
[['current_phone', 'permanent_phone','father_phone','mother_phone','guardian_phone'], 'phoneNoValidation', 'skipOnError' => false, 'skipOnEmpty' => false],
[['permanent_phone'], 'permanentPhoneUniqueCheck', 'skipOnError' => false, 'skipOnEmpty' => false],
['dob', 'ageValidation', 'on' => self::PERSONAL_INFO, 'skipOnError' => false, 'skipOnEmpty' => false],
[['serial_no'], 'unique'],
[['request_for_cancel'], 'default', 'value' => 0],
[['cancel_application_view'], 'default', 'value' => 2],
[['photo'], 'required', 'on' => self::PERSONAL_INFO],
['photo', 'image', 'extensions' => 'png, jpg', 'maxSize' => 1024 * 500, 'minWidth' => 300, 'maxWidth' => 300, 'minHeight' => 300, 'maxHeight' => 300, 'on' => self::PERSONAL_INFO],
```
(Full field-by-field ruleset spans ~290 lines; string-length caps are set per-field with Bangla `tooLong` messages sourced from a parallel `attributeLabelBangla()` array, e.g. `father_occupation` max 30, `father_nid` max 20, `current_village` max 25.)
- **attributeLabels()**: ~140 entries, mostly boilerplate but with a few Bangla overrides: `payment_type` → "পেমেন্টের ধরন", `birth_registration_no` → "জন্ম নিবন্ধন নম্বর", `serial_no` → "Roll No", `payment_status` → "Is Paid Application?", `application_status` → "Is Cancel Application?". A separate static `attributeLabelBangla()` (~148 entries) provides parallel Bangla labels used both for display and `tooLong` validation messages.
- **Relations**: **none** — `Sailors.php` defines zero `hasOne`/`hasMany` relation methods (confirmed by grep). It does override `find()`:
```php
public static function find()
{
    return new SailorsQuery(get_called_class());
}
```
- **Other**: `beforeSave()` manually stamps `created_by`/`created_dt`/`updated_by`/`updated_dt` (no `TimestampBehavior`). Static helpers: `numberOfApplication()`, `nextRollByBatchId()`, `getLastRollExamDateByDesignationCenter()`, `sailorBatchAndGroupWiseCount()`, `generateLog()` (dumps a fixed field list to a per-batch JSON debug log under `@rootDirFilUpload/dummy/{batch_id}.txt`), `getTotalCandidateByExamDate()`.
- **`SailorsQuery.php`**: extends `\yii\db\ActiveQuery`; adds `activeApplication()` (`andFilterWhere(['application_status' => Constants::STATUS_ACTIVE])`) and `cancelApplication()` (`Constants::STATUS_INACTIVE`) scopes. Shared by both `Sailors` and `DeSailors` via their respective `find()` overrides.
- **PII / file-upload / payment fields**: PII intended for encryption per `$encryption_fields_for_personal_info`: `father_nid, current_phone, permanent_phone, father_phone, mother_phone, guardian_phone` (plus a separate `permanent_phone_de` column holding an already-encrypted value, confirmed used in `SailorsSearch`). File upload: `photo` (required, image validator: png/jpg ≤500KB, exactly 300×300px), `qr_photo`. Payment fields: `payment_type, agree_payment_terms, ref_id, validation_id, order_id_original, amount, store_amount, card_type, card_no, trans_date, payment_api, payment_status, all_requested_tran_id, all_paid_tran_id, all_payment_response, all_payment_request, is_manula_paid, refund_phone`. Roll number: `serial_no` (unique). Exam fields: `exam_date_id, exam_date, exam_group, exam_center_name, serial_generate_date, batch_id, batch_config_id, center_id, team`.
- **Migration**: none — `sailors` is not created by either migration file.

---

## 17. `SailorsSearch.php`

- **Class**: `common\models\SailorsSearch extends Sailors` (no separate `tableName()` — inherits `{{%sailors}}`)
- **rules()**:
```php
public function rules()
{
    return [
        [['id', 'eligibility_info_id', 'is_departmental_candidate', 'candidate_designation', 'exam_group', 'center_id', 'batch_id', 'batch_config_id', 'religion', 'gender', 'marital_status', 'ac_type_ssc', 'ssc_edu_board', 'hsc_or_diploma', 'hsc_dip_board', 'is_freedom_fighter', 'freedom_fighter_relation', 'is_child_of_naval_officer', 'naval_uniform_civil', 'is_anser_vdp', 'is_khudro_jati_gosti', 'phase', 'is_manula_paid', 'payment_status', 'application_status', 'have_reference', 'is_online_manual', 'created_by', 'updated_by', 'list_custom_filter'], 'integer'],
        [['app_unique_id', 'exam_date', 'serial_no', /* ~75 more text/date fields covering address, academic, experience, reference, payment */ 'created_dt', 'updated_dt'], 'safe'],
        [['father_income', 'amount', 'store_amount'], 'number'],
    ];
}
```
- **scenarios()**: bypassed → `return Model::scenarios();`.
- **Search/filter fields** (`search($params)`, `pageSize => 300`): exact-match `andFilterWhere` on `id, eligibility_info_id, candidate_designation, center_id, batch_id, batch_config_id, exam_date, exam_group, father_income, dob, religion, gender, marital_status, ac_type_ssc, ssc_edu_board, hsc_or_diploma, hsc_dip_board, is_freedom_fighter, freedom_fighter_relation, is_child_of_naval_officer, naval_uniform_civil, is_anser_vdp, is_khudro_jati_gosti, phase, is_manula_paid, amount, store_amount, trans_date, payment_status` (defaults to `Constants::PAYMENT_PAID`), `application_status, have_reference, is_online_manual, is_departmental_candidate, created_by, updated_by, created_dt, updated_dt`. **Encrypted-phone filter**: if `permanent_phone_de` is set, the search value is AES-encrypted (`AES256CTR::dataEncrypt`, truncated `-3` chars) and matched via `like` against the `permanent_phone_de` column — confirms phone numbers are stored encrypted in that column. `like`-match on ~70 further text fields (name/address/academic/experience/reference/payment). Custom `list_custom_filter`: `1` = paid & roll assigned, `2` = paid & roll not yet assigned.
- **Migration**: n/a (search wrapper, same table as `Sailors`).

---

## Sailors vs. DeSailors — what distinguishes them

`Sailors` and `DeSailors` are **two fully separate `ActiveRecord` models backed by two separate tables** (`{{%sailors}}` vs `{{%de_sailors}}`) — not a subtype/single-table-inheritance relationship. They are structural twins (same field families: personal info, address, academic history, payment, reference, audit) but represent **different recruitment tracks**:

- **`Sailors`** is the general/standard sailor recruitment application, covering the broad `can_designation` set (per its doc comment, `candidate_type` 1=Sailor).
- **`DeSailors`** is the **"DE" (Direct Entry) track** — trade-entry recruitment for experienced/skilled candidates. Evidence:
  - `DeSailors`'s doc comment on `candidate_type` reads `2=>Artificer 3=>Dockyard` — DE candidates are typed specifically as **Artificer** or **Dockyard** tradesmen, not general sailor designations.
  - `DeSailors` has DE-only scenario constants `ACADEMIC_DE_SAILOR_ARTIFICER = 'artificer'` / `ACADEMIC_DE_SAILOR_DOCKYARD = 'dockyard'` and DE-only academic fields absent from `Sailors`: `diploma_trade_institute, diploma_trade_course, diploma_trade_registration_roll, diploma_trade_gpa`, plus four "experience" blocks (`experience_one_..four_..`) capturing prior trade work/certifications — consistent with Direct Entry hiring being for already-trained tradespeople rather than fresh SSC/HSC-track recruits.
  - `DeSailors` carries `is_departmental_candidate`, which its `ageValidation()` uses to apply a different max-age ceiling (`dept_can_max_age` vs `max_age`) — a DE-specific "already serving" candidate category.
  - Both models share the exact same supporting infrastructure: both override `find()` to return the same `SailorsQuery` class, both have identically-named/signature static helpers (`numberOfApplication`, `nextRollByBatchId`, `generateLog`, etc.), and both are paired with a lightweight backend `*Reference` AR class (`SailorsReference` / `DeSailorsReference`) pointing at the same respective table, used only for the backend "add reference" form.
  - The only naming nuance between the two backend reference forms: `SailorsReference` labels `serial_no` as **"Roll No"**; `DeSailorsReference` labels the identical field **"Serial No"** — a small terminology difference consistent with `DeSailors` foregrounding `serial_no`/`serial_generate_date` as its primary post-scheduling identifier.

Neither `sailors` nor `de_sailors` has any migration in this repo — both schemas exist only in the live database.

---

## 18. `SendSms.php`

- **Class**: `common\models\SendSms extends \yii\db\ActiveRecord`
- **Table**: `{{%send_sms}}`
- **rules()**:
```php
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
```
- **attributeLabels()**: `id, application_type, serial_no, phone_no, sms_body, created_by, updated_by, created_dt, updated_dt`.
- **Relations**: none. This is purely the `send_sms` audit-log table model — no gateway credentials in this file (the actual SMS-sending integration lives in `common/static/SendSms.php`, a non-AR helper class, see Cross-cutting Observations below).
- **Migration**: none.

---

## 19. `Session.php`

- **Class**: `common\models\Session extends \yii\db\ActiveRecord`
- **Table**: `{{%session}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['expire', 'user_id'], 'integer'],
        [['user_id'], 'required'],
        [['session_id'], 'string', 'max' => 200],
    ];
}
```
- **attributeLabels()**: `id, session_id, expire, user_id`. Doc comment: `expire` 1=Expired.
- **Relations**: none. No custom methods.
- **Migration**: none.

---

## 20. `Subjects.php`

- **Class**: `common\models\Subjects extends \yii\db\ActiveRecord`
- **Table**: `{{%subjects}}`
- **rules()**:
```php
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
```
- **attributeLabels()**: 9 entries.
- **Relations**: `getCreatedBy()`, `getUpdatedBy()` → `User`.
- **Other**: doc comment `candidate_type` 1=Sailor/2=DeSailor/3=Officer Cadet, `subject_type` 1=Diploma/2=Trade. Static helpers: `getAllActiveSubject()`, `getAllSubject()`, `getAllActiveSubjectBySubjectType()`, `getAllActiveSubjectByIdIn()`, `modelToDropdown()`, `subjectFindById()`.
- **Migration**: none.

---

## 21. `Unions.php` (+ `UnionsSearch.php`)

- **Class**: `common\models\Unions extends \yii\db\ActiveRecord`
- **Table**: `{{%unions}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['name', 'district_id', 'upozila_id'], 'required'],
        [['upozila_id', 'district_id', 'name', 'bn_name', 'slug', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status'], 'default', 'value' => null],
        [['upozila_id', 'district_id', 'created_by', 'updated_by', 'status'], 'integer'],
        [['created_at', 'updated_at'], 'safe'],
        [['name', 'bn_name', 'slug'], 'string', 'max' => 250],
    ];
}
```
- **attributeLabels()**: 11 entries, plain strings.
- **Relations**: none declared (no `getDistrict()`/`getUpozila()` despite FK-shaped columns).
- **Other**: uses `created_at`/`updated_at` (not the `_dt` suffix convention seen elsewhere) and has **no `beforeSave()`** — audit timestamps are never auto-populated, unlike `SailorCenters`/`Districts`/`SailorCentDistMapping`. Static helpers: `getUnionsListForCandidate()`, `unionNameById()`.
- **`UnionsSearch.php`** (`common\models\UnionsSearch extends Unions`): `rules()` → `[['id','upozila_id','district_id','created_by','updated_by','status'],'integer']`, `[['name','bn_name','slug','created_at','updated_at'],'safe']`. `scenarios()` bypassed. Search fields: exact-match `id, upozila_id, district_id, created_at, updated_at, created_by, updated_by, status`; `like` on `name, bn_name, slug`.
- **Migration**: none.

---

## 22. `Upozilas.php` (+ `UpozilasSearch.php`)

- **Class**: `common\models\Upozilas extends \yii\db\ActiveRecord`
- **Table**: `{{%upozilas}}`
- **rules()**:
```php
public function rules()
{
    return [
        [['name', 'district_id'], 'required'],
        [['district_id', 'name', 'bn_name', 'slug', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status'], 'default', 'value' => null],
        [['district_id', 'created_by', 'updated_by', 'status'], 'integer'],
        [['created_at', 'updated_at'], 'safe'],
        [['name', 'bn_name', 'slug'], 'string', 'max' => 250],
    ];
}
```
- **attributeLabels()**: 10 entries, plain strings.
- **Relations**: none declared (no `getDistrict()` despite `district_id`).
- **Other**: no `beforeSave()` (same pattern as `Unions`). Static helpers: `getUpazilaListAdmin()` (unfiltered by status), `getUpazilaListCandidate()` (status=1 only), `upazilaNameById()`.
- **`UpozilasSearch.php`** (`common\models\UpozilasSearch extends Upozilas`): `rules()` → `[['id','district_id','created_by','updated_by','status'],'integer']`, `[['name','bn_name','slug','created_at','updated_at'],'safe']`. `scenarios()` bypassed. Search fields: exact-match `id, district_id, created_at, updated_at, created_by, updated_by, status`; `like` on `name, bn_name, slug`.
- **Migration**: none.

**Note**: `Districts` → `Upozilas` → `Unions` form the standard Bangladesh administrative hierarchy (District → Upazila → Union), but — unusually — **none of the three models declare AR relations to each other**; every cross-level lookup goes through static helper methods keyed on raw `district_id`/`upozila_id`/slug columns instead of `hasOne`/`hasMany`.

---

## 23. `User.php`

- **Class**: `common\models\User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface`
- **Table**: `{{%user}}` — the **one table with real migration history** in this repo.
- **behaviors()**: `[TimestampBehavior::class]`
- **Status constants**: `STATUS_DELETED = 0`, `STATUS_INACTIVE = 2`, `STATUS_ACTIVE = 1`
- **rules()**:
```php
public function rules()
{
    return [
        ['status', 'default', 'value' => self::STATUS_INACTIVE],
        ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        [['user_group', 'user_type'], 'string'],
        [['username', 'email', 'auth_key', 'password_hash', 'created_at', 'updated_at'], 'required'],
        [['last_logout', 'created_dt', 'updated_dt', 'dob', 'phone_no'], 'safe'],
        [['created_at', 'updated_at', 'status'], 'integer'],
        [['username', 'email', 'password_hash', 'password_reset_token', 'verification_token', 'login_zone'], 'string', 'max' => 255],
        [['os'], 'string', 'max' => 150],
        [['auth_key'], 'string', 'max' => 32],
        [['last_login_ip'], 'string', 'max' => 100],
        [['username'], 'unique'],
        // [['email'], 'unique'],   <-- commented out: duplicate emails allowed
        [['password_reset_token'], 'unique'],
    ];
}
```
- **attributeLabels()**: 20 entries (`id, user_group, user_type, username, email, phone_no, dob, auth_key, password_hash, password_reset_token, verification_token, last_login_ip, last_logout, login_zone, os, created_at, updated_at, created_dt, updated_dt, status`).
- **Relations**: none declared directly on `User` (it's the target of `getCreatedBy()`/`getUpdatedBy()` on ~10 other models).
- **IdentityInterface methods**: `findIdentity()`, `findIdentityByAccessToken()` (throws `NotSupportedException`), `findByUsername()`, `findByPasswordResetToken()`, `findByVerificationToken()`, `isPasswordResetTokenValid()`, `getId()`, `getAuthKey()`, `validateAuthKey()`, `validatePassword()`, `setPassword()`, `generateAuthKey()`, `generatePasswordResetToken()`, `generateEmailVerificationToken()`, `removePasswordResetToken()`.
- **Other**: `beforeSave()` sets `password_reset_token = uniqid('', true)`, `last_login_ip = $_SERVER['REMOTE_ADDR']`, `os = PHP_OS` on every save (insert and update). **Schema drift**: `birth_registration_no` is read/written by `frontend\models\SignupForm` and filtered by `backend\models\UserSearch`, but is declared in neither `User::rules()` nor its `@property` docblock — an undocumented column.
- **Migration**: `console/migrations/m130524_201442_init.php` creates the base 9-column `user` table (`id, username, auth_key, password_hash, password_reset_token, email, status, created_at, updated_at`); `m190124_110200_add_verification_token_column_to_user_table.php` adds `verification_token`. **Not covered by any migration**: `user_group, user_type, phone_no, dob, last_login_ip, last_logout, login_zone, os, created_dt, updated_dt, birth_registration_no` — 11 of the ~20 columns the model actually uses.

---

## 24. `payment/SSLPayment.php`

- **Class**: `common\models\payment\SSLPayment` — plain PHP class (no base class), a static-method service/utility, not a Yii `Model`/`ActiveRecord`.
- **Purpose**: SSLCommerz payment-gateway integration for the joinnavysailor.org site.
- **Notable constants**:
```php
const PAYMENT_VALID = 'VALID';
const PAYMENT_VALIDATED = 'VALIDATED';
const STORE_AMOUNT = 275;
const SANDBOX_STORE_ID = 'unloc67b319d54a54e';
const SANDBOX_PASSWORD = 'unloc67b319d54a54e@ssl';
const SANDBOX_URL = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
const LIVE_STORE_ID = 'joinnavynavymilbd0live';
const LIVE_PASSWORD = '67D29D06BA5D147902';
const LIVE_URL = 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
const HOST = 'https://www.joinnavysailor.org';
```
- **Methods**: `requestInit($dataArray)` — builds the SSLCommerz payment session payload and POSTs via cURL (`CURLOPT_SSL_VERIFYPEER => false`, `CURLOPT_SSL_VERIFYHOST => false`); on `opt_a == 'sailor'`, looks up the `Sailors` record from `opt_b` and appends to its `all_requested_tran_id`/`all_payment_request` JSON columns, saving with `save(false)` (validation skipped). `allRequestListByTranIds()` — reconciles transaction IDs against SSLCommerz's validation API (same disabled-TLS-verification pattern).
- **Security flags** (analogous to a hardcoded-SMS-credentials finding, but materially worse — these are **live payment-gateway merchant credentials**):
  1. Both sandbox and **live** SSLCommerz `store_id`/`store_passwd` are hardcoded plaintext class constants committed to source control (`LIVE_STORE_ID`, `LIVE_PASSWORD`).
  2. TLS certificate/host verification is explicitly disabled (`CURLOPT_SSL_VERIFYPEER`/`VERIFYHOST => false`) on all outbound calls to the payment gateway, in both `requestInit()` and `allRequestListByTranIds()`.
  3. Environment (sandbox vs. live `HOST`) appears to have been switched historically by manually commenting/uncommenting `const HOST` lines rather than via environment config — three alternate `HOST` values are left commented out in source.
- **Migration**: n/a (not a table-backed model; it reads/writes `Sailors`).

---

## Part 2 — `backend/models/` (Search & Reference variants)

All Search classes follow the same Gii-generated pattern: extend the base AR class, loosen `required`/custom-validator rules to `integer`/`safe`, override `scenarios()` to `return Model::scenarios();` (bypassing the base model's scenario gating), and implement `search($params)` with `andFilterWhere` for exact-match fields plus chained `andFilterWhere(['like', ...])` for text fields.

| Search/Reference model | Wraps | Filterable fields (exact-match) | Filterable fields (like) |
|---|---|---|---|
| `CanDesignationSearch` | `CanDesignation` | `id, candidate_type, created_by, updated_by, created_dt, updated_dt, status` | `name_bn, name_en` |
| `DistrictsSearch`* | `Districts` | `id, division, created_by, updated_by, created_dt, updated_dt, status` | `slug, name_en, name_bn` |
| `EligibilitySearch` | `Eligibility` | `id, candidate_type, candidate_designation, min_age, max_age, dept_can_max_age, jsc_result, is_required_biology, is_allow_trade_course, is_allow_diploma, is_allow_hons_appeared, is_allow_masters_appeared, created_by, updated_by, created_dt, updated_dt, status` | `marital_status, gender, height_male, weight_male, height_female, weight_female, chest_normal_male, chest_extended_male, chest_normal_female, chest_extended_female, ssc_result, ssc_ac_group, hsc_result, hsc_ac_group, diploma_result, hons_result, masters_result, masters_subject, hons_diploma_subject` |
| `SailorBatchConfigurationSearch` | `SailorBatchConfiguration` | `id, batch_id, center_id, candidate_type, exam_date, exam_group, team, roll_swap_in_group, created_by, updated_by, created_dt, updated_dt, status` | `gender, candidate_designation, district_slug, group_start_roll, group_end_roll, group_no_of_candidate` |
| `SailorBatchsSearch` | `SailorBatchs` | `id, candidate_type, circular_date, circular_close_date, is_active_batch, allow_application_after_close, is_batch_live_mode, created_by, updated_by, created_dt, updated_dt, status` | `name_en, name_bn, description, roll_from, start_roll, secrate_key` |
| `SailorCentDistMappingSearch` | `SailorCentDistMapping` | `id, center_id, candidate_type, created_by, updated_by, created_dt, updated_dt, status` | `district_slug` |
| `SailorCentersSearch` | `SailorCenters` | `id, candidate_type, created_by, updated_by, created_dt, updated_dt, status` | `name_en, name_bn` |
| `SubjectsSearch` | `Subjects` | `id, candidate_type, subject_type, created_by, updated_by, created_dt, updated_dt, status` | `name_en, name_bn` |
| `UserSearch` | `User` | `id, last_logout, created_at, updated_at, created_dt, updated_dt, status` | `user_group, user_type, birth_registration_no, username, email, phone_no, auth_key, password_hash, password_reset_token, verification_token, last_login_ip, login_zone, os` |
| `SailorsReference` | `Sailors` (same `{{%sailors}}` table) | n/a — standalone AR, not a grid-search class | n/a |
| `DeSailorsReference` | `DeSailors` (same `{{%de_sailors}}` table) | n/a — standalone AR, not a grid-search class | n/a |
| `Report` | n/a — form model, not a Search class | n/a | n/a |

\* `DistrictsSearch.php` declares `namespace app\models;` while physically living in `backend/models/` — inconsistent with every other `backend/models/*Search.php` file, which correctly declares `namespace backend\models;`. This is a genuine anomaly in the source, not a transcription error in this doc; worth verifying whether Composer autoloading/PSR-4 config actually resolves this class as intended.

**`UserSearch` security note**: its `search()` exposes `like`-filtering on `auth_key`, `password_hash`, and `password_reset_token` — sensitive columns — through the admin grid form. Whether this constitutes real exposure depends on whether the paired grid view actually renders those columns, but the filter surface itself is a smell worth a security-review follow-up.

### `SailorsReference.php` / `DeSailorsReference.php` detail

Both are standalone `\yii\db\ActiveRecord` classes (not subclasses of `Sailors`/`DeSailors`) built specifically for the backend "add reference" workflow — they point at the same table as their full model but expose only the reference columns:
```php
const ADD_REFERENCE = 'add_reference';
const UPDATE_REFERENCE = 'update_reference';

public function rules()
{
    return [
        [['serial_no', 'referred_by', 'relationship'], 'required', 'on' => self::ADD_REFERENCE],
        ['serial_no', 'isSerialNoExist', 'on' => self::ADD_REFERENCE, 'skipOnError' => false, 'skipOnEmpty' => false],
        ['reference_details', 'safe'],
    ];
}
```
Custom validator `isSerialNoExist()` checks the row exists via `self::find()->where(['serial_no' => ...])->count()`. Label difference: `SailorsReference` labels `serial_no` "Roll No"; `DeSailorsReference` labels it "Serial No" (see Sailors-vs-DeSailors section above).

### `Report.php` detail

- **Class**: `backend\models\Report extends \yii\base\Model` (form model backing the admin reporting screens — payment reports, candidate filters, monitoring, district/center breakdowns). No `tableName()`.
- **Scenario constants**: `PAYMENT_REPORT, CANDIDATE_FILTER, CANDIDATE_MONITORING_BY, CANDIDATE_DISTRICT_WISE, CANDIDATE_CENTER_WISE, CANDIDATE_FILTER_WITH_SERIAL_NUMBER, CANDIDATE_CENTER_DATE_WISE`.
- **rules()**: each scenario requires a different subset of `batch, payment_type, is_paid, district, center, designation, serial_no, monitor_by, create_date, exam_date` — with the remaining fields marked `safe` (one `safe` block is duplicated/dead).
- **Other**: static `getSscResult($teletalkData)` parses a Teletalk SSC-result JSON blob into subject→grade pairs, with separate subject-code maps for madrasha vs. non-madrasha boards — a report-generation helper, not persistence logic.
- **Migration**: n/a.

---

## Part 3 — `frontend/models/` (form models, no backing table)

| Model | Extends | Purpose |
|---|---|---|
| `ContactForm.php` | `yii\base\Model` | Contact page: `name, email, subject, body, verifyCode`; `sendEmail()` sends via `Yii::$app->mailer`. No `User` relation. |
| `RefundForm.php` | — | **Empty file (0 bytes)** — no `<?php` tag, no namespace, no class body at all. Dead/placeholder file. |
| `PasswordResetRequestForm.php` | `yii\base\Model` | `$email`; `'exist'` rule against `common\models\User` filtered to `STATUS_ACTIVE`; `sendEmail()` generates/validates `User::generatePasswordResetToken()` and mails a reset link. |
| `ResendVerificationEmailForm.php` | `yii\base\Model` | `$email`; `'exist'` rule against `User` filtered to `STATUS_INACTIVE`; `sendEmail()` mails the `emailVerify-html`/`-text` views. |
| `SignupForm.php` | `yii\base\Model` | `$username, $birth_registration_no, $email, $password, $confirm_password, $phone_no, $dob, $captcha`. `username` and `birth_registration_no` both validated `unique` against `User`. Custom `usernameValidation()` (6–15 chars, ≥1 letter, ≥1 digit, no whitespace). `signup()` constructs a `User` directly: encrypts `phone_no` via `common\static\DataEncryption::dataEncrypt()`, sets `user_group='register'`, `user_type='candidate'`, **`status = STATUS_ACTIVE` immediately** (not pending-verification), and saves with `save(false)`. Note: `sendEmail($user)` exists as a method but its call inside `signup()` is commented out, so verification email is not actually sent on signup despite the method's presence. |
| `ResetPasswordForm.php` | `yii\base\Model` | `$password`; constructor resolves `User::findByPasswordResetToken($token)` or throws; `resetPassword()` sets password, clears reset token, regenerates auth key. |
| `VerifyEmailForm.php` | `yii\base\Model` | `$token`; **no `rules()` method at all** (only form model in the whole inventory with zero validation rules); constructor resolves `User::findByVerificationToken($token)` or throws; `verifyEmail()` sets `status = STATUS_ACTIVE`. |

---

## Part 4 — `console/models/`

Directory exists but is **empty** — no model files present.

---

## Cross-cutting observations

1. **No migration history for ~28 of ~29 tables.** Only `user` traces to migrations in this repo (partially — see §2). Every sailor/de-sailor/batch/center/district/union/upozila/designation/eligibility/subject/sms/session table exists solely in a live DB dump outside this repository. Any modernization effort must reconstruct schema (column types, defaults, indexes, FKs, charset) from `rules()`/`@property` docblocks alone, which do not capture DB-level constraints (e.g., `unique` in Yii `rules()` is app-level, and doesn't guarantee a DB unique index exists, or vice versa).
2. **`User` itself has drifted from its own migrations.** 11 of ~20 columns the model/app actually use (`user_group, user_type, phone_no, dob, last_login_ip, last_logout, login_zone, os, created_dt, updated_dt, birth_registration_no`) are absent from both migration files.
3. **Sailors/DeSailors have no declared AR relations at all** — despite dozens of FK-shaped columns (`batch_id, center_id, batch_config_id, exam_date_id, candidate_designation`), all cross-entity data access goes through raw `::find()`/`where()` calls inside custom validators and static helpers, not `hasOne`/`hasMany`. This is a significant departure from the `SailorBatchConfiguration`/`Eligibility`/`CanDesignation` family, which do declare relations.
4. **Administrative-geography hierarchy (District → Upazila → Union) has no relations either** — three separate models, zero cross-references via AR, only static helper lookups keyed on raw ids/slugs.
5. **Two independent security-hardening findings, more severe than typical**: (a) `common/models/payment/SSLPayment.php` hardcodes **live SSLCommerz merchant credentials** in source and disables TLS verification on outbound gateway calls; (b) `backend/models/UserSearch.php` exposes `like`-filtering on `auth_key`/`password_hash`/`password_reset_token` through the admin search form. A third, milder finding: `common/models/LoginForm.php::getLoginAddress()` calls a third-party IP-geolocation API over plain HTTP with no auth on every login.
6. **`SignupForm::signup()` activates accounts immediately** (`status = STATUS_ACTIVE`) rather than leaving them `STATUS_INACTIVE` pending email verification, even though the surrounding email-verification machinery (`VerifyEmailForm`, `ResendVerificationEmailForm`, `sendEmail()`) exists and is wired for the inactive-until-verified flow — the actual `sendEmail()` call in `signup()` is commented out, so the verification step appears dormant/bypassed in the current code path.
7. **Inconsistent audit-field automation.** `SailorCenters`, `Districts`, `SailorCentDistMapping`, `Sailors`, `DeSailors`, `SailorBatchs`, `SailorBatchConfiguration`, `SailorBatchConfigurationExamDate`, `CanDesignation`, `Eligibility`, `Subjects` all auto-stamp `created_by/updated_by/created_dt/updated_dt` via `beforeSave()` (with a recurring `1`/`2` fallback-user-id pattern when no identity is set). `Unions` and `Upozilas` have no such logic at all — their audit columns are declared in `rules()` but never actually populated by the model.
8. **`DistrictsSearch.php` namespace anomaly**: declares `namespace app\models;` while living in `backend/models/`, unlike its sibling Search classes which correctly use `namespace backend\models;`.
9. **`RefundForm.php` is a dead, fully empty file** (0 bytes) — not merely unused, but has no PHP content whatsoever.
10. **`VerifyEmailForm.php` is the only form model with no `rules()` at all** — validation is implicit (constructor throws if the token doesn't resolve to a user).
11. **Dead/no-op code left in place**: `Sailors::freedomFighterRelationValidation()` (body commented out, always `true`), `SailorBatchConfigurationExamDate::validateExamDate()` (contains a stray `echo 'ds';`, never wired into `rules()`), `SailorBatchs::startRollValidation()` (commented out), `Eligibility::eligibilityBySession()` (misnamed — session-caching code is commented out, hits DB every call), `Districts::getDistrictsList($isActive)` (parameter accepted but never applied to the query).

---

## Migration file reference (both files under `console/migrations/`)

```
m130524_201442_init.php
  up():   createTable('{{%user}}', [id, username, auth_key, password_hash,
          password_reset_token, email, status (default 10), created_at, updated_at])
  down(): dropTable('{{%user}}')

m190124_110200_add_verification_token_column_to_user_table.php
  up():   addColumn('{{%user}}', 'verification_token', string()->defaultValue(null))
  down(): dropColumn('{{%user}}', 'verification_token')
```

`user` is the only table created by any migration in this repo. No migration exists for: `sailors, de_sailors, sailor_batchs, sailor_batch_configuration, sailor_batch_configuration_exam_date, sailor_centers, sailor_cent_dist_mapping, districts, unions, upozilas, can_designation, can_eligibility_check_info, eligibility, subjects, send_sms, session`.
