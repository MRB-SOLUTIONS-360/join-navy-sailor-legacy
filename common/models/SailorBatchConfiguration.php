<?php

namespace common\models;

use common\static\Constants;
use Yii;

/**
 * This is the model class for table "{{%sailor_batch_configuration}}".
 *
 * @property int $id
 * @property int|null $batch_id
 * @property int|null $center_id
 * @property int|null $candidate_type Sailor/DE Sailor
 * @property int|null $team Center as Team 
 * @property string|null $gender 1=>Male, 2=>Female
 * @property string|null $marital_status 1=>Married, 2=>Unmarried
 * @property string|null $candidate_designation DUEC/Sailor-Patrolman/ DE Sailor for Dockyard (Shipwright)  etc
 * @property string|null $district_slug
 * @property string|null $exam_date
 * @property string|null $exam_group 1=>Group A, 2=>Group B, 3=>Group C
 * @property int|null $roll_swap_in_group 1=>Yes, 2=>No #if yes swap roll in group A->B->C else A complete then B, after B complete C
 * @property int|null $check_max_candidate 1=>Yes, 2=>No #if yes then check max candidate in this group
 * @property string|null $group_start_roll
 * @property string|null $group_end_roll
 * @property string|null $group_no_of_candidate No of candidate this group
 * @property string|null $du_uc_can_total
 * @property string|null $medical_can_total
 * @property string|null $pertol_store_can_total
 * @property string|null $cook_steward_can_total
 * @property string|null $modc_can_total
 * @property string|null $topass_can_total
 * @property int $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int $status 1=>Active, 2=>Inactive 
 */
class SailorBatchConfiguration extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */ 
    public static function tableName()
    {
        return '{{%sailor_batch_configuration}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['batch_id', 'center_id', 'candidate_type', 'gender', 'marital_status', 'exam_date', 'exam_group', 'group_no_of_candidate'], 'required'],
            [['batch_id', 'center_id', 'candidate_type', 'gender', 'marital_status', 'exam_group', 'group_no_of_candidate','team', 'check_max_candidate'], 'required'],
            [['batch_id', 'center_id', 'candidate_type', 'roll_swap_in_group', 'created_by', 'updated_by', 'status','check_max_candidate'], 'integer'],
            [['du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'], 'integer'],
            [['exam_date', 'created_dt', 'updated_dt', 'district_slug', 'candidate_designation', 'gender', 'marital_status', 'exam_group'], 'safe'],
            [['group_start_roll', 'group_end_roll', 'group_no_of_candidate', 'du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'], 'string', 'max' => 50],
            [['group_start_roll', 'group_end_roll'], 'validateOnRollSwap',  'skipOnError' => false, 'skipOnEmpty' => false],

            // ['exam_date','validationExamDate']


            // [['candidate_designation'], 'string', 'max' => 200],
            // [['district_slug'], 'string', 'max' => 255],
        ];
    }  

    // public function validationExamDate($attribute, $params)
    // {
    //     // if (isset($this->$attribute) && empty($this->$attribute)) {
    //     //     // Check if the first element is empty and apply required validation // }

    //     $this->addError($attribute, 'The first exam date is required.');
    // }
    


    public function validateOnRollSwap($attribute, $params, $validator)
    {
        if ($this->roll_swap_in_group == Constants::YES && empty($this->$attribute)) {
            $this->addError($attribute, $this->getAttributeLabel($attribute) . ' can not be blank.');
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
            'batch_id' => Yii::t('app', 'Batch'),
            'center_id' => Yii::t('app', 'Center'),
            'team' => Yii::t('app', 'Team'),
            'candidate_type' => Yii::t('app', 'Candidate Type'),
            'gender' => Yii::t('app', 'Gender'),
            'marital_status' => Yii::t('app', 'Marital Status'),
            'candidate_designation' => Yii::t('app', 'Candidate Designation'), /// DUEC/Sailor-Patrolman/ DE Sailor for Dockyard (Shipwright)  etc
            'district_slug' => Yii::t('app', 'District'),
            'exam_date' => Yii::t('app', 'Exam Date'),            
            'exam_group' => Yii::t('app', 'Candidate Group'),
            'roll_swap_in_group' => Yii::t('app', 'Swap Roll'),
            'group_start_roll' => Yii::t('app', 'Start Roll'),
            'group_end_roll' => Yii::t('app', 'End Roll'),
            'group_no_of_candidate' => Yii::t('app', 'No of Candidate this group'),
            'du_uc_can_total' => Yii::t('app', 'DEUC (Seaman / Communication / Technical)'),
            'medical_can_total' => Yii::t('app', 'Medical'),
            'pertol_store_can_total' => Yii::t('app', 'Petrolman / Writer / Store'),
            'cook_steward_can_total' => Yii::t('app', 'Cook / Steward'),
            'modc_can_total' => Yii::t('app', 'MODC (N)'),
            'topass_can_total' => Yii::t('app', 'Topass'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Dt'),
            'updated_dt' => Yii::t('app', 'Updated Dt'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return SailorBatchConfigurationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SailorBatchConfigurationQuery(get_called_class());
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
     * Gets query for [[batch]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBatch()
    {
        return $this->hasOne(SailorBatchs::class, ['id' => 'batch_id']);
    }

    /**
     * Gets query for [[center]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCenter()
    {
        return $this->hasOne(SailorCenters::class, ['id' => 'center_id']);
    }

    /**
     * Gets query for [[center]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExamDates()
    {
        return $this->hasMany(SailorBatchConfigurationExamDate::class, ['batch_configuration_id' => 'id']);
    }


    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        foreach (self::inplodeFields() as $key => $field) {
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
    public static function inplodeFields()
    {
        return ['gender', 'marital_status', 'candidate_designation', 'district_slug'];
    }


    /**
     *  candidate batch and center by eligible district and department 
     *  @return  array 
     */
    public static function batchIdAndCenterIdByApplyDistrictAndDepartment(int $applyDept = null,int $gender, string $elgilbe_district = null)
    {

        $sailor_active_batch = SailorBatchs::find()
            ->select(['id', 'candidate_type', 'name_en', 'circular_date', 'circular_close_date'])
            ->isActive()->isActiveBatch()->isCircularCloseDateGraterCurrentDate()->asArray()
            ->all();
        $batch_ids = [];

        foreach ($sailor_active_batch as $key => $val)
            $batch_ids[] = $val['id'];




        // echo $applyDept.'--'.$elgilbe_district;
        return $model = self::find()
            ->select(['id', 'batch_id', 'center_id', 'candidate_type'])
            ->isActive()
            ->andFilterWhere(['in', 'batch_id', $batch_ids])
            ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $elgilbe_district . "(,|$)"])
            ->andFilterWhere(['REGEXP', 'gender', "(^|,)" . $gender . "(,|$)"])
            ->andFilterWhere(['REGEXP', 'candidate_designation', "(^|,)" . $applyDept . "(,|$)"])->asArray()->one();

        // echo     $sql_r = $model->createCommand()->getRawSql();
        // die();
    }


    /**
     *  candidate batch and center by eligible district and department 
     *  @return  array 
     */
    public static function configurationByBatchCenterGenderCanDesigDistrictSlug(int $batch_id, int $center_id, int $gender, int $candidate_designation, string $eligible_district)
    {
        $configuration = SailorBatchConfiguration::find()
            ->select(['id', 'exam_date', 'exam_group', 'roll_swap_in_group', 'group_no_of_candidate'])
            ->where(['batch_id' => $batch_id])
            ->andFilterWhere(['center_id' => $center_id])
            ->andFilterWhere(['gender' => $gender])
            ->andFilterWhere(['REGEXP', 'candidate_designation', "(^|,)" . $candidate_designation . "(,|$)"])
            ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $eligible_district . "(,|$)"])
            ->andFilterWhere(['status' => Constants::STATUS_ACTIVE]);
        return  $configuration->asArray()->one();
    }


    /**
     * all configuration  by candidate batch and center by eligible district and department 
     */
    public static function configurationByBatchCenterGenderCanDesigDistrictSlugAll(int $batch_id, int $center_id, int $gender, int $candidate_designation, string $eligible_district)
    {
        $configuration = SailorBatchConfiguration::find()
            ->select(['id', 'exam_date', 'exam_group', 'roll_swap_in_group', 'group_no_of_candidate', 'candidate_designation','team', 'check_max_candidate'])
            ->where(['batch_id' => $batch_id])
            ->andFilterWhere(['center_id' => $center_id])
            // ->andFilterWhere(['gender' => $gender])
            ->andFilterWhere(['REGEXP', 'gender', "(^|,)" . $gender . "(,|$)"])
            ->andFilterWhere(['REGEXP', 'candidate_designation', "(^|,)" . $candidate_designation . "(,|$)"])
            ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $eligible_district . "(,|$)"])
            ->andFilterWhere(['status' => Constants::STATUS_ACTIVE])
            ->orderBy('exam_group ASC');
        return  $configuration->asArray()->all();

        // echo     $sql_r = $configuration->createCommand()->getRawSql();
        // die();
    }
}
