<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "{{%sailor_batch_configuration_exam_date}}".
 *
 * @property int $id
 * @property int $batch_configuration_id
 * @property string|null $exam_date
 * @property float|null $max_candidate_this_date
 * @property int|null $status 1=>Active,2=>Inactive
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 */
class SailorBatchConfigurationExamDate extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sailor_batch_configuration_exam_date}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['exam_date', 'max_candidate_this_date', 'created_by', 'updated_by', 'created_dt', 'updated_dt'], 'default', 'value' => null],
            [['status'], 'default', 'value' => 1],
            [['batch_configuration_id'], 'required'],
            [['batch_configuration_id', 'status', 'created_by', 'updated_by'], 'integer'],
            [['exam_date', 'created_dt', 'updated_dt'], 'safe'],
            [['max_candidate_this_date'], 'number'],
            // [['exam_date'], 'validateExamDate'],
        ];
    }

    public function validateExamDate($attribute, $params)
    {
        if (isset($this->$attribute) && is_array($this->$attribute)) {
            // Check if the first element is empty and apply required validation
            echo 'ds';
            if (empty($this->$attribute[0])) {
                $this->addError($attribute, 'The first exam date is required.');
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'batch_configuration_id' => 'Batch Configuration ID',
            'exam_date' => 'Exam Date - ##',
            'max_candidate_this_date' => 'Max Candidate This Date',
            'status' => 'Status',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'created_dt' => 'Created Dt',
            'updated_dt' => 'Updated Dt',
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


    // getListByConfigurationId
    public static function getListByConfigurationId(int $batch_configuration_id)
    {
        return  self::find()->select(['exam_date', 'max_candidate_this_date', 'id','batch_configuration_id'])
            ->where(['batch_configuration_id' => $batch_configuration_id])
            ->andWhere(['status' => 1])
            ->orderBy('exam_date ASC')
            ->indexBy('id') // Index by 'id' directly to avoid the manual loop
            ->asArray()->all();
    }



    // Function to get the next key and date in a circular manner
    public static function getNextKeyValue(int $key, $dates = array())
    {
        // Get all the keys from the array
        $keys = array_keys($dates);
        // Find the index of the input
        $index = array_search($key, $keys);
        // If key is not found or index is empty, reset it to 0
        $index = ($index === false) ? 0 : $index;
        if ($index === count($keys) - 1) $nextKey = $keys[0];
        else $nextKey = $keys[$index + 1] ?? '';
        
        return array(
            'id' => $nextKey ?? '', 
            'exam_date' => $dates[$nextKey]['exam_date'] ?? '',
            'max_candidate_this_date' => $dates[$nextKey]['max_candidate_this_date'] ?? '',
        );
    }
 
    // getNextAvailableExamDate 
    public static function getNextAvailableExamDate(array $examDates, string $checkDate, int $candidateCount)
    {   
        $selectedExam = null;
        $overLimitIds = [];
        $checkTimestamp = strtotime($checkDate);       
    
        foreach ($examDates as $exam) {
            $examTimestamp = strtotime($exam['exam_date']);

            if ($candidateCount >= $exam['max_candidate_this_date']) {
                $overLimitIds[] = $exam['id']; // mark to deactivate later
                continue;
            }
            // Choose the first date >= checkDate
            if ($examTimestamp >= $checkTimestamp) {
                $selectedExam = $exam;
                break;
            }
        }    

        // Wrap-around if no future date available
        if (!$selectedExam) {       
            foreach ($examDates as $exam) {
                if ($candidateCount <= $exam['max_candidate_this_date']) {
                    $selectedExam = $exam;
                    break;
                }
            } 
            // If still null, pick the earliest date (all dates over limit)
            if (!$selectedExam && !empty($examDates)) {
                $selectedExam = null;
            }
        }     
    
        // Bulk update over-limit dates to inactive
        if (!empty($overLimitIds)) {
            SailorBatchConfigurationExamDate::updateAll(
                ['status' =>2],
                ['id' => $overLimitIds]
            );
        }    

        return $selectedExam;
    }



}
