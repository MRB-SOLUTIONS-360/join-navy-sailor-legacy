<?php

namespace backend\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class Report extends Model
{
    public $application_type;
    public $batch;
    public $payment_type;
    public $is_paid;

    public $district;
    public $center;
    public $designation;
    public $exam_date;
    public $gender;

    public $monitor_by;
    public $create_date;

    public $father_occupation;
    public $ssc_group;

    public $serial_no;



    const PAYMENT_REPORT = 'payment_report';
    const CANDIDATE_FILTER = 'candidate_filter';
    const CANDIDATE_MONITORING_BY = 'monitoring_by';
    const CANDIDATE_DISTRICT_WISE = 'district_wise';
    const CANDIDATE_CENTER_WISE = 'center_wise';
    const CANDIDATE_FILTER_WITH_SERIAL_NUMBER = 'candidate_filter_with_serial_number';
    const CANDIDATE_CENTER_DATE_WISE = 'center_exam_date_wise';


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['batch', 'payment_type', 'is_paid'], 'required', 'on' => self::PAYMENT_REPORT],
            [['batch', 'district', 'center', 'designation'], 'required', 'on' => self::CANDIDATE_FILTER],
            [['serial_no'], 'required', 'on' => self::CANDIDATE_FILTER_WITH_SERIAL_NUMBER],
            [['monitor_by', 'batch', 'create_date'], 'required', 'on' => self::CANDIDATE_MONITORING_BY],
            [['batch', 'district'], 'required', 'on' => self::CANDIDATE_DISTRICT_WISE],
            [['batch', 'center'], 'required', 'on' => self::CANDIDATE_CENTER_WISE],
            [['application_type','batch', 'district', 'center', 'designation', 'exam_date', 'gender', 'monitor_by', 'create_date', 'father_occupation', 'ssc_group','serial_no'], 'safe'],
            [['batch', 'exam_date','center'], 'required', 'on' => self::CANDIDATE_CENTER_DATE_WISE],
            [['application_type', 'exam_date', 'gender', 'monitor_by', 'create_date', 'father_occupation', 'ssc_group'], 'safe'],
            // rememberMe must be a boolean value            
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'batch' => Yii::t('app', 'Batch'),
            'payment_type' => Yii::t('app', 'Payment Type'),
            'is_paid' => Yii::t('app', 'Payment Status'),
            'district' => Yii::t('app', 'District'),
            'center' => Yii::t('app', 'Center'),
            'designation' => Yii::t('app', 'Designation'),
            'exam_date' => Yii::t('app', 'Exam Date'),
            'gender' => Yii::t('app', 'Gender'),
            'monitor_by' => Yii::t('app', 'Monitor By'),
            'create_date' => Yii::t('app', 'Apply Date'),
            'father_occupation' => Yii::t('app', 'Father\'s Occupation'),
            'ssc_group' => Yii::t('app', 'SSC Group'),
            'serial_no' => Yii::t('app', 'Roll No'),

        ];
    }




    public static function getSscResult($teletalkData)
    {
        $data_arr = [];
        if ($teletalkData) {
            $data = json_decode($teletalkData, true);
            if (isset($data['ltrgd'])) {
                $is_sciecne = strtolower($data['studGroup']) == 'science';
                $is_madrasha = strtolower($data['board']) == 'madrasah';
                $pairs = explode(',', $data['ltrgd']);

                $subjectMapping = [
                    'non_madrasha' => [
                        '101' => 'bangla',
                        '102' => 'bangla',
                        '1921' => 'bangla', // Bangla technology
                        '109' => 'math',
                        '1923' => 'math', // Math technology
                        '107' => 'english',
                        '108' => 'english',
                        '1922' => 'english', // English technology
                        '136' => 'physics',
                        '1925' => 'physics', // Physics technology
                        '138' => 'biology', // Biology (science only)
                        '127' => 'science', // General Science  
                    ],
                    'madrasha' => [
                        '136+137' => 'english', // English
                        '134+135' => 'bangla', // Bangla
                        '108' => 'math', // Math
                        '130' => 'physics', // Physics
                    ],
                ];

                // Iterate through each pair (subject code and grade)
                foreach ($pairs as $pair) {
                    $explode = explode(':', $pair);
                    $s_code = trim($explode[0] ?? '');
                    $s_grade = trim($explode[1] ?? '');
                    if (!$is_madrasha) {
                        if (isset($subjectMapping['non_madrasha'][$s_code])) {
                            $subject = $subjectMapping['non_madrasha'][$s_code];
                            if ($s_code == '138' && $is_sciecne) {
                                $data_arr[$subject] = ucfirst($subject) . ' : ' . $s_grade;
                            } elseif ($s_code == '127' && !$is_sciecne) {
                                $data_arr[$subject] = ucfirst($subject) . ' : ' . $s_grade;
                            } else {
                                $data_arr[$subject] = ucfirst($subject) . ' : ' . $s_grade;
                            }
                        }
                    } elseif ($is_madrasha) {
                        if (isset($subjectMapping['madrasha'][$s_code])) {
                            $subject = $subjectMapping['madrasha'][$s_code];
                            $data_arr[$subject] = ucfirst($subject) . ' : ' . $s_grade;
                        }
                    }
                }
            }
        }

        return $data_arr;
    }
}
