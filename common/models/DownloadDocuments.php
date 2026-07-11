<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class DownloadDocuments extends Model
{
    public $download_by;
    public $application_id;
    public $batch;
    public $serial_no;
    public $dob; 


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['download_by'], 'required'],
            // rememberMe must be a boolean value
             [['application_id','batch','serial_no','dob'],'safe']  ,
             ['application_id','applicationIdValidation','skipOnError' => false, 'skipOnEmpty' => false],        
             [['batch','serial_no','dob'],'batchSerialValidation','skipOnError' => false, 'skipOnEmpty' => false],        
        ];
    }

    
    public function applicationIdValidation($attribute, $params, $validator)
    {
        if ( $this->download_by == 1 && empty($this->$attribute)) {
            $this->addError($attribute, 'Application ID');
        }
        return true;
    }
    public function batchSerialValidation($attribute, $params, $validator)
    {
        if ( $this->download_by == 2 && empty($this->$attribute)) {
            $this->addError($attribute, ' cannot be blank.');
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'download_by' => Yii::t('app', 'Document Download By'),
            'application_id' => Yii::t('app', 'Application ID'),
            'batch' => Yii::t('app', 'Batch') ,          
            'serial_no' => Yii::t('app', 'Serial No') ,          
            'dob' => Yii::t('app', 'Date of Birth') ,          
        ];
    }

    
}
