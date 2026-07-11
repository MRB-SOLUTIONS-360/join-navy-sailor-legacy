<?php

namespace common\models;

use common\models\scopeQuery\SailorBatchs as ScopeQuerySailorBatchs;
use common\static\Constants;
use common\static\StaticMethod;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%sailor_batchs}}".
 *
 * @property int $id
 * @property int $candidate_type 1=>Sailor,2 =>DeSailor
 * @property string $name_en
 * @property string|null $name_bn
 * @property string|null $description
 * @property string $circular_date
 * @property string $circular_close_date
 * @property string $circular_start_date
 * @property string $roll_from if batch roll start from batch setting else roll start from configuration setting
 * @property string|null $start_roll
 * @property string|null $next_start_roll
 * @property string|null $next_start_roll_after
 * @property int $is_active_batch 1=>Yes, 2=>No, #Show all application list
 * @property int $allow_application_after_close 1=>Yes, 2=>No, #Allow candidate to complete the application after application date over
 * @property int $is_batch_live_mode 1=>Yes, 2=>No, # if yes not asked secret key for application
 * @property string|null $secrate_key Secrate key for application testing mode
 * @property int $payment_mode 1=>Live, 2=>Sandbox,
 * @property int $allow_refund 1=>Yes, 2=>No,
 * @property string $payment_amount 225 live amount
 * @property int $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int $status 1=>Active, 2=>Inactive
 */
class SailorBatchs extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sailor_batchs}}';
    }

    /**
     * @inheritdoc
     * @return SailorBatchsScopeQuery
     */
    public static function find()
    {
        return new ScopeQuerySailorBatchs(get_called_class());
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['candidate_type', 'allow_refund', 'name_en', 'circular_date', 'circular_close_date', 'circular_start_date', 'payment_amount', 'start_roll', 'secrate_key'], 'required'],

            // ['start_roll', 'required', 'when' => function($model) {
            //     return $model->roll_from == 'batch';
            // }],

            [['candidate_type', 'is_active_batch', 'allow_application_after_close', 'is_batch_live_mode', 'payment_mode', 'created_by', 'updated_by', 'status'], 'integer'],
            [['circular_date', 'circular_close_date', 'created_dt', 'updated_dt'], 'safe'],
            [['roll_from'], 'string'],
            [['name_en', 'name_bn', 'description'], 'string', 'max' => 255],
            [['secrate_key'], 'string', 'max' => 50],
            [['start_roll', 'payment_amount', 'next_start_roll', 'next_start_roll_after'], 'number'],
            // [['is_active_batch','allow_application_after_close','is_batch_live_mode'], 'in', 'range' =>StaticMethod::yesNo()],
            ['payment_amount', 'in', 'range' => StaticMethod::paymentAmount()],
            //['start_roll', 'startRollValidation', 'skipOnEmpty' => false]

        ];
    }

    // public function startRollValidation($attribute, $params, $validator)
    // {
    //     if ($this->roll_from == 'batch' && empty($attribute))
    //         $this->addError($attribute, $this->getAttributeLabel('start_roll').' cannot be blank.');
    // }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'candidate_type' => Yii::t('app', 'Candidate Type'),
            'name_en' => Yii::t('app', 'Name English'),
            'name_bn' => Yii::t('app', 'Name Bangla'),
            'description' => Yii::t('app', 'Description'),
            'circular_date' => Yii::t('app', 'Circular Date'),
            'circular_start_date' => Yii::t('app', 'Circular Start Datetime'),
            'circular_close_date' => Yii::t('app', 'Circular Close Datetime'),
            'circular_media' => Yii::t('app', 'Circular Media'),
            'media_for_api' => Yii::t('app', 'Media File for API'),
            'roll_from' => Yii::t('app', 'Roll From'),
            'start_roll' => Yii::t('app', 'Start Roll'),
            'next_start_roll' => Yii::t('app', 'Next Start Roll'),
            'next_start_roll_after' => Yii::t('app', 'Next Start Roll After'),
            'is_active_batch' => Yii::t('app', 'Is Active Batch'),
            'allow_application_after_close' => Yii::t('app', 'Allow Application After Close'),
            'is_batch_live_mode' => Yii::t('app', 'Is Batch Live Mode'),
            'allow_refund' => Yii::t('app', 'Allow Refund'),
            'secrate_key' => Yii::t('app', 'Secrate Key'),
            'payment_mode' => Yii::t('app', 'Payment Mode'),
            'payment_amount' => Yii::t('app', 'Payment Amount'),
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

        if ($this->circular_close_date && !empty($this->circular_close_date))
            $this->circular_close_date  = date('Y-m-d H:i', strtotime($this->circular_close_date));
        if ($this->circular_start_date && !empty($this->circular_start_date))
            $this->circular_start_date  = date('Y-m-d H:i', strtotime($this->circular_start_date));



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
     * All active batch  for dropdown
     * @param type array      
     */
    public static function getAllActiveBatch(int $isActive = Constants::STATUS_ACTIVE)
    {
        $model = self::find()
            ->where('status =:status', [':status' => $isActive])
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'name_en');
    }


    /**
     * All batch in system for dropdown filter 
     * @param type array      
     */
    public static function getAllBatch(int $type = null, int $type_2 = null)
    {
        $model = self::find()
            ->andFilterWhere(['candidate_type' => $type])
            ->orFilterWhere(['candidate_type' => $type_2])
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'name_en');
    }


    /**
     * Check applied batch is active and status active also check circular close date 
     * if active and status active then candidate then candidate can continue application 
     */
    public static function isBatchActiveAndRunning(int $batch_id)
    {

        return $batch = self::find()
            ->isCircularCloseDateGraterCurrentDate()
            ->isActive()
            ->isActiveBatch()
            ->andFilterWhere(['id' => $batch_id])
            ->count();

        // echo $sql_r = $batch->createCommand()->getRawSql();
        // echo $batch_id;
    }

    /**
     * batch by id
     */
    public static function batchById(int $batch_id)
    {

        return $batch = self::find()
            ->select(['id', 'circular_date', 'circular_close_date', 'roll_from', 'start_roll', 'name_en', 'status', 'is_active_batch', 'circular_close_date', 'payment_mode', 'payment_amount', 'is_batch_live_mode', 'secrate_key', 'next_start_roll', 'next_start_roll_after', 'allow_refund'])
            ->where(['id' => $batch_id])
            ->asArray()
            ->one();
    }

    /**
     * batch by id
     */
    public static function isCandidateContinueApplication(int $batch_id, int $isPaid = 2)
    {
        $batch = self::find()
            ->select(['id', 'name_en', 'status', 'is_active_batch', 'circular_close_date', 'allow_application_after_close'])
            ->where(['id' => $batch_id])
            ->asArray()
            ->one();


        $can_continue_application = Constants::TEXT_YES;
        if ($batch['status'] != Constants::STATUS_ACTIVE || $batch['is_active_batch'] != Constants::STATUS_ACTIVE || date('Y-m-d H:i') > $batch['circular_close_date']) {
            if ($batch['allow_application_after_close'] == Constants::YES && $isPaid == Constants::YES)
                $can_continue_application = Constants::TEXT_YES;
            else
                $can_continue_application = Constants::TEXT_NO;
        }

        $return['name_en'] = $batch['name_en'] ?? '';
        $return['can_apply'] = $can_continue_application;


        return $return;
    }



    /**
     * All batch set session
     * @param type array      
     */
    public static function getAllBatchSession(int $id)
    {
        $session = Yii::$app->session;
        if ($session->has('batchSession')) {
            $batch = $session->get('batchSession');
            if (array_key_exists($id, $batch))
                return $batch[$id];
        }
        $model = self::find()
            ->orderBy('name_en ASC')
            ->all();
        $batch = ArrayHelper::map($model, 'id', 'name_en');
        $session->set('batchSession', $batch);

        return  $batch[$id];
    }
}
