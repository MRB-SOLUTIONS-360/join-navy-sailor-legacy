<?php

namespace backend\controllers;

use backend\models\Report;
use common\models\CanDesignation;
use common\models\DeSailors;
use common\models\Districts;
use common\models\SailorBatchConfiguration;
use common\models\SailorBatchConfigurationExamDate;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\models\Sailors;
use common\models\SailorsSearch;
use common\static\Constants;
use common\static\DataEncryption;
use common\static\StaticMethod;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\RichText\Run;
use Yii;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use yii\base\DynamicModel;
use yii\db\Expression;

class ReportController extends \yii\web\Controller
{
    /**
     * Payment Report 
     */
    public function actionPayment()
    {
        $model = new Report();
        $model->scenario = $model::PAYMENT_REPORT;

        $sailor = [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            $sailor = Sailors::find()
                ->select(['app_unique_id', 'candidate_designation', 'center_id', 'batch_id', 'exam_date', 'exam_group', 'serial_no', 'name', 'ref_id', 'validation_id', 'card_type', 'card_no', 'trans_date', 'payment_status'])
                ->where(['batch_id' => $model->batch])
                ->andWhere(['payment_type' => $model->payment_type])
                ->andWhere(['payment_status' => $model->is_paid])->asArray()->orderBy('trans_date DESC')->all();

            Yii::$app->session->set('report',  $sailor);
            Yii::$app->session->set('filter_value',  $model->attributes);
        }

        return $this->render('payment_report', ['model' => $model, 'sailor' => $sailor]);
    }

    /**
     * Payment repoprt pdf 
     */
    public function actionPaymentPdf()
    {
        if (Yii::$app->session->has('report')) {
            $session_value = Yii::$app->session->get('report');
            $filter_value_value = Yii::$app->session->get('filter_value');
            // Pdf library 
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 10,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_bottom' => 0,
                'mirrorMargins' => true,
                // 'format' => [190, 236],
                //'orientation' => 'L'
            ]);
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->debug = true;
            // $mpdf->Image('https://www.joinnavysailor.org/media/main_logo.png', 0, 0, 210, 297, 'png', '', true, false);
            $mpdf->WriteHTML($this->renderPartial('pdf/payment_report_pdf', ['model' => $session_value, 'filter' => $filter_value_value], true, false));
            $mpdf->Output();
            exit();
        }
    }

    /**
     * Payment repoprt pdf 
     */
    public function actionPaymentExcel()
    {
        if (Yii::$app->session->has('report')) {
            $session_value = Yii::$app->session->get('report');
            $filter_value_value = Yii::$app->session->get('filter_value');

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Hello World !');


            $xlxFile = 'Sailor_Candidate_List_';
            $xlxRoot = Yii::getAlias('@rootDirFilUpload') . '/media/exportXls/';
            $filenameXls = $xlxRoot . $xlxFile . '.xlsx';

            $writer = new Xlsx($spreadsheet);
            $writer->save($filenameXls);

            // download and delete xls
            if (file_exists($xlxRoot . $xlxFile . '.xlsx')) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header('Content-type: application/xlsx');
                header('Content-Disposition: attachment; filename=' . $xlxFile . '.xlsx');
                readfile($xlxRoot . $xlxFile . '.xlsx');
                @unlink($xlxRoot . $xlxFile . '.xlsx');
            }
            exit();
        }
    }

    /**
     * candidate-filter
     */
    public function actionCandidateFilter()
    {
        ini_set('memory_limit', '1024M');
        $model = new Report();
        // $model->scenario = $model::CANDIDATE_FILTER;
        $sailor = [];
        if ($this->request->isPost && $model->load($this->request->post())) {
            $query = Sailors::find()
                ->select(['app_unique_id', 'photo', 'gender', 'candidate_designation', 'permanent_district', 'permanent_phone', 'center_id', 'batch_id', 'exam_date', 'exam_group', 'serial_no', 'name', 'ref_id', 'validation_id', 'card_type', 'card_no', 'trans_date', 'payment_status', 'father_occupation', 'ssc_group', 'dob', 'age_according_to_circular', 'ssc_gpa', 'ssc_teletalk_data']);

            if (!empty($model->serial_no) && empty($model->batch) && empty($model->center) && empty($model->district) && empty($model->designation)) {
                $query->andWhere(['serial_no' => $model->serial_no]);
            } else {
                $query->andWhere(['batch_id' => $model->batch])
                    ->andWhere(['center_id' => $model->center])
                    ->andWhere(['in', 'eligible_district', $model->district])
                    ->andWhere(['in', 'candidate_designation', $model->designation])
                    ->andWhere(['payment_status' => Constants::PAYMENT_PAID])
                    ->andFilterWhere(['exam_date' => $model->exam_date])
                    ->andFilterWhere(['gender' => $model->gender])
                    ->andFilterWhere(['ssc_group' => $model->ssc_group])
                    ->andFilterWhere(['like', 'father_occupation', $model->father_occupation])
                    ->andWhere(['not', ['serial_no' => null]]);
                if ($model->serial_no) {
                    $query->andWhere(['serial_no' => $model->serial_no]);
                }
            }

            //->andWhere(['REGEXP', 'marital_status', $model['marital_status']]); // 

            $sailor = $query->asArray()->orderBy('serial_no ASC')->all();
            // echo "<pre>";
            // echo $query->createCommand()->getRawSql()
            // // print_r($sailor);
            // die();
            Yii::$app->session->set('report',  $sailor);
            Yii::$app->session->set('filter_value',  $model->attributes);
        }

        return $this->render('candidate_filter', ['model' => $model, 'sailor' => $sailor]);
    }

    /**
     * candidate-filter-pdf 
     */
    public function actionCandidateFilterPdf()
    {

        // Increase backtrack limit
        ini_set('pcre.backtrack_limit', 10000000);
        ini_set('memory_limit', '-1');
        if (Yii::$app->session->has('report')) {
            $session_value = Yii::$app->session->get('report');
            $filter_value_value = Yii::$app->session->get('filter_value');

            // Pdf library 
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 10,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_bottom' => 0,
                'mirrorMargins' => true,
                // 'format' => [190, 236],
                //'orientation' => 'L'
            ]);
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->debug = false;
            // $mpdf->Image('https://www.joinnavysailor.org/media/main_logo.png', 0, 0, 210, 297, 'png', '', true, false);
            $mpdf->WriteHTML($this->renderPartial('pdf/candidate_filter_pdf', ['model' => $session_value, 'filter' => $filter_value_value], true, false));
            $mpdf->Output();
            exit();
        }
    }

    /**
     * candidate-filter-pdf 
     */

    public function actionCandidateFilterExcel()
    {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('memory_limit', '-1');
        if (Yii::$app->session->has('report')) {
            $session_value = Yii::$app->session->get('report');
            $filter_value_value = Yii::$app->session->get('filter_value');


            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Photo');
            $drawing->setDescription('Photo');
            $drawing->setPath(Yii::getAlias('@rootDirFilUpload') . '/media/main_logo.png'); // put your path and image here
            $drawing->setCoordinates('D1');
            // $drawing->setOffsetX(110);
            //  $drawing->setRotation(25);
            $drawing->getShadow()->setVisible(true);
            // $drawing->getShadow()->setDirection(45);
            $drawing->setHeight(100);
            $drawing->setWorksheet($spreadsheet->getActiveSheet());
            $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(80);
            $sheet->mergeCells('A1:G1');

            $sheet->setCellValue('C2', 'বাংলাদেশ নৌবাহিনী');
            $sheet->mergeCells('C2:E2');

            $sheet->setCellValue('B3', 'ব্যাচ:' . SailorBatchs::getAllBatchSession($filter_value_value['batch']));
            $sheet->mergeCells('B3:E3');

            $sheet->setCellValue('B4', 'কেন্দ্র:' .  SailorCenters::getAllCenterSession($filter_value_value['center']));
            $sheet->mergeCells('B4:H4');

            $init_cell = 6;
            $sheet->setCellValue('A' . $init_cell, 'SL')
                ->setCellValue('B' . $init_cell, 'Application ID')
                ->setCellValue('C' . $init_cell, 'Designation')
                ->setCellValue('D' . $init_cell, 'District')
                ->setCellValue('E' . $init_cell, 'Name')
                ->setCellValue('F' . $init_cell, 'Gender')
                ->setCellValue('G' . $init_cell, 'DOB')
                ->setCellValue('H' . $init_cell, 'Age')
                ->setCellValue('I' . $init_cell, 'SSC Group')
                ->setCellValue('J' . $init_cell, 'SSC GPA')
                ->setCellValue('K' . $init_cell, 'Bangla')
                ->setCellValue('L' . $init_cell, 'English')
                ->setCellValue('M' . $init_cell, 'Math')
                ->setCellValue('N' . $init_cell, 'Science/Physics')
                ->setCellValue('O' . $init_cell, 'Biology')
                ->setCellValue('P' . $init_cell, 'Father Occupation')
                ->setCellValue('Q' . $init_cell, 'Phone No')
                ->setCellValue('R' . $init_cell, 'Serial No')
                ->setCellValue('S' . $init_cell, 'Exam Date')
                ->setCellValue('T' . $init_cell, 'Photo');

            $value_cell = $init_cell + 1;
            foreach ($session_value  as $k => $value) {
                // $spreadsheet->getActiveSheet()->getRowDimension($value_cell)->setRowHeight(-1);
                $spreadsheet->getActiveSheet()->getRowDimension($value_cell)->setRowHeight(50);
                $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);

                $gpa_data = ($value['ssc_teletalk_data']) ? Report::getSscResult($value['ssc_teletalk_data']) : [];
                $gpa = ($gpa_data) ? implode(', ', $gpa_data) : '';
                $subjects = explode(',', $gpa);
                $data_arr = [
                    'bangla' => $gpa_data['bangla'] ?? '',
                    'english' => $gpa_data['english'] ?? '',
                    'math' => $gpa_data['math'] ?? '',
                    'science_physics' => $gpa_data['science'] ?? $gpa_data['physics'] ?? '',
                    'biology' => $gpa_data['biology'] ?? '',
                ];

                $grades = [];
                foreach ($data_arr as $subject_name => $subject_grad) {
                    if (!empty($subject_grad)) {
                        $grades[$subject_name] = trim(substr($subject_grad, strrpos($subject_grad, ':') + 1));
                    } else {
                        $grades[$subject_name] = 'N/A';
                    }
                }
                // $colE = $value['name'] . "\n DOB :" . $value['dob'] . "\n Age :" . $value['age_according_to_circular'] . "\n SSC Group : " . $value['ssc_group'];
                $sheet->setCellValue('A' . $value_cell, ($k + 1))
                    ->setCellValue('B' . $value_cell,  (string) $value['app_unique_id'])
                    ->setCellValue('C' . $value_cell,  $desig)
                    ->setCellValue('D' . $value_cell, ucfirst($value['permanent_district']));

                $sheet->setCellValue('E' . $value_cell, $value['name'])
                    ->setCellValue('F' . $value_cell, ($value['gender']) ? StaticMethod::gender($value['gender']) : '');
                $sheet->setCellValue('G' . $value_cell, $value['dob']);
                $sheet->setCellValue('H' . $value_cell, $value['age_according_to_circular']);
                $sheet->setCellValue('I' . $value_cell, ucwords(strtolower($value['ssc_group'])));
                $sheet->setCellValue('J' . $value_cell, $value['ssc_gpa']);
                // $sheet->setCellValue('J' . $value_cell, 'GPA : ' . $value['ssc_gpa'] . "\n" . $gpa);
                // $sheet->getStyle('J' . $value_cell)->getAlignment()->setWrapText(true);
                $sheet->setCellValue('K' . $value_cell, $grades['bangla'] ?? '');
                $sheet->setCellValue('L' . $value_cell, $grades['english'] ?? '');
                $sheet->setCellValue('M' . $value_cell, $grades['math'] ?? '');
                $sheet->setCellValue('N' . $value_cell, $grades['science_physics'] ?? '');
                $sheet->setCellValue('O' . $value_cell, $grades['biology'] ?? '');
                $sheet->setCellValue('P' . $value_cell, $value['father_occupation'])
                    ->setCellValue('Q' . $value_cell, DataEncryption::dataDecrypt($value['permanent_phone']))
                    ->setCellValue('R' . $value_cell, $value['serial_no'])
                    ->setCellValue('S' . $value_cell, date('d-m-Y', strtotime($value['exam_date'])));

                if ($value['photo'] && file_exists(Yii::getAlias('@rootDirFilUpload') . $value['photo'])) {

                    try {
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

                        $drawing->setName('Photo');
                        $drawing->setDescription('Photo');
                        $drawing->setPath(Yii::getAlias('@rootDirFilUpload') . $value['photo']); // put your path and image here
                        $drawing->setCoordinates('O' . $value_cell);
                        // $drawing->setOffsetX(110);
                        // $drawing->setRotation(25);
                        $drawing->getShadow()->setVisible(true);
                        // $drawing->getShadow()->setDirection(45);
                        $drawing->setHeight(50);
                        $drawing->setWorksheet($spreadsheet->getActiveSheet());
                    } catch (\Exception $e) {

                        echo '<pre>';
                        print_r($value);
                        echo '</pre>';
                        echo '<pre>';
                        print_r($e->getMessage());
                        echo '</pre>';
                        die();
                        // Log the error or handle it as needed
                        // error_log($e->getMessage());
                    }

                    // Check if the image is valid
                    // $imageInfo = @getimagesize(Yii::getAlias('@rootDirFilUpload') . $value['photo']);
                    // if ($imageInfo) {
                    //     $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();

                    //     $drawing->setName('Photo');
                    //     $drawing->setDescription('Photo');
                    //     $drawing->setPath(Yii::getAlias('@rootDirFilUpload') . $value['photo']); // put your path and image here
                    //     $drawing->setCoordinates('J' . $value_cell);
                    //     // $drawing->setOffsetX(110);
                    //     //  $drawing->setRotation(25);
                    //     $drawing->getShadow()->setVisible(true);
                    //     // $drawing->getShadow()->setDirection(45);
                    //     $drawing->setHeight(50);
                    //     $drawing->setWorksheet($spreadsheet->getActiveSheet());
                    // }


                    // $sheet->setCellValue('J' . $value_cell, $value['photo']);
                }

                // $sheet->setCellValue('J' . $value_cell, $value['photo']);
                $value_cell++;
            }
            $xlxFile = 'Sailor_Candidate_Filter_List_';
            $xlxRoot = Yii::getAlias('@rootDirFilUpload') . '/media/exportXls/';
            $filenameXls = $xlxRoot . $xlxFile . '.xlsx';

            $writer = new Xlsx($spreadsheet);
            $writer->save($filenameXls);

            // download and delete xls
            if (file_exists($xlxRoot . $xlxFile . '.xlsx')) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header('Content-type: application/xlsx');
                header('Content-Disposition: attachment; filename=' . $xlxFile . '.xlsx');
                readfile($xlxRoot . $xlxFile . '.xlsx');
                @unlink($xlxRoot . $xlxFile . '.xlsx');
            }
            exit();
        }
    }


    /**
     * monitoring-application
     */
    public function actionMonitoringApplication()
    {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        ini_set('memory_limit', '-1');
        $model = new Report();
        $model->scenario = $model::CANDIDATE_MONITORING_BY;
        $sailor_model = [];
        if ($this->request->isPost && $model->load($this->request->post())) {

            ////
            $sailor = Sailors::find()->where(['batch_id' => $model->batch]);
            if ($model->monitor_by == Constants::CAN_MONITOR_BY_IMAGE_MISSING) {

                $r2Storage = Yii::$app->r2Storage;
                $sailor->select(['id', 'name', 'app_unique_id', 'photo', 'gender', 'candidate_designation', 'serial_no', 'exam_date', 'phase']);
                $sailor->andWhere(["DATE_FORMAT(created_dt,'%Y-%m-%d')" => $model->create_date]);
                $sailor->andWhere(['not', ['serial_no' => null]]);
                $sailor->andWhere(['not', ['exam_date' => null]]);               
                $sailor->andWhere(['is', 'is_image_exist_check', null]);
                // echo $sailor->createCommand()->getRawSql();
                // die();
                $sailor =  $sailor->asArray()->orderBy('app_unique_id ASC')->all();
                $missing_list = [];               
                $idsHaveImage = [];
                $table = Yii::$app->db->tablePrefix . 'sailors';
                foreach ($sailor as $k => $val) {                    
                    // if (empty($val['photo']) || !file_exists(Yii::getAlias('@rootDirFilUpload') . $val['photo']))
                    if (empty($val['photo']) || ! $r2Storage->fileExists($val['photo'])) $missing_list[] = $val;
                    else {
                        $idsHaveImage [] = $val['id']; 
                        $sql = "UPDATE ".$table." set is_image_exist_check=1 where id =". $val['id'].""; 
                        Yii::$app->db->createCommand($sql)->execute();
                    } 
                }              
               
                $sailor_model = $missing_list;
            } 
        }


        return $this->render('monitoring_application', ['model' => $model, 'sailor' => $sailor_model]);
    }


    public function actionExamDateCheck()
    {
        $sailor_model = [];
        $exam_dates = [];
        $model = new DynamicModel(['batch', 'center_id', 'create_date', 'candidate_designation', 'district_slug']);
        $model->addRule(['batch'], 'required')
            ->addRule(['center_id'], 'required')
            ->addRule(['create_date'], 'required')
            ->addRule(['candidate_designation', 'district_slug'], 'required');         
        return $this->render('exam_date_check_by_center_designation', ['model' => $model, 'sailor' => $sailor_model, 'exam_dates'=>$exam_dates]);
    }


     /**
     * all-reference-candidate-excel
     */
    public function actionAllReferenceCandidateExcel(){

        try{            
            $searchModel = Sailors::find()->select(['id', 'name', 'father_name', 'serial_no', 'exam_date', 'eligible_district', 'batch_id', 'center_id', 'permanent_phone', 'permanent_district', 'candidate_designation', 'ssc_gpa', 'ssc_group', 'referred_by', 'reference_details', 'relationship', 'photo'])->where(['have_reference' => Constants::YES]);
            $search_param = Yii::$app->session->get('reference_candidate_query_param');    
            // // Some condition to check

            if (!isset($search_param['SailorsSearch']) || empty($search_param['SailorsSearch']['center_id']) )   
                throw new \Exception("Please select exam center.");              
            
            if($search_param){
                foreach ($search_param['SailorsSearch'] as $k => $val) 
                    if ($val) $searchModel->andFilterWhere([$k => $val]);
            } 
           
            // echo $searchModel->createCommand()->getRawSql();
            $searchModel = $searchModel->orderBy('updated_dt ASC')->asArray()->all();  

            $all_designation = CanDesignation::getAllDesignation(Constants::CANDIDATE_SAILOR);
           

            if ($searchModel) {
                $district_by_slug = Districts::getAllActiveDistrictBySlug();
                $model =  $searchModel;

               
                $centerName =  ($model[0]['center_id']) ? SailorCenters::find()->select(['id', 'name_en'])->where(['id' => $model[0]['center_id']])->one()->name_en : '';
 
                // unique branch name
                $branchs = [];
                $exam_date_list = [];
                foreach ($model as $k => $val){
                    $branch = CanDesignation::getAllDesignationSession($val['candidate_designation']);
                    $branchs[] = $branch;  
                    $exam_date_list[] = $val['exam_date'] ? date('d M Y', strtotime( $val['exam_date'])) : '';
                }
                $branchs = array_unique($branchs);

                $total_candidate = Sailors::find()->andWhere(['batch_id' => $model[0]['batch_id']])->andWhere(['center_id' => $model[0]['center_id']])->andWhere(['not', ['exam_group' => null]])->count();

                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                // $sheet->setCellValue('B2',  strtoupper($district_full_name));
                $sheet->setCellValue('B2',  'Branch : '.htmlspecialchars(implode(' / ', $branchs)));
                $sheet->setCellValue('B3', htmlspecialchars(strtoupper($centerName)));
                // $dt = 'DATE ' . strtoupper(date('d M Y', strtotime($ex_date))) . '-TOTAL APPLICANT-' . $total_candidate;
                // $sheet->setCellValue('B4', strtoupper(htmlspecialchars($dt)));

                // Merge cells          
                $sheet->mergeCells('B2:H2');
                $sheet->mergeCells('B3:H3');
                $sheet->mergeCells('B4:H4');
                // Set alignment and formatting  
                $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $init_cell = 6;
                $sheet->setCellValue('A' . $init_cell, 'SL')
                    ->setCellValue('B' . $init_cell, 'Roll')
                    ->setCellValue('C' . $init_cell, 'Mobile')
                    ->setCellValue('D' . $init_cell, 'Designation')
                    ->setCellValue('E' . $init_cell, 'District')
                    ->setCellValue('F' . $init_cell, 'Description')
                    ->setCellValue('G' . $init_cell, 'Reference')
                    ->setCellValue('H' . $init_cell, 'Relationship')    ;              

                $sheet->getStyle('A:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
                $value_cell = $init_cell + 1;               

                foreach ($model  as $k => $value) {

                    $description = $this->getDescription($value);
                    $description =   $description ? htmlspecialchars(implode(PHP_EOL, $description)) : '';

                    $reference = $this->getReference($value['referred_by']);
                    $reference =   $reference ? htmlspecialchars(implode(PHP_EOL, $reference)) : '';

                    $relation = $this->getRelation($value['relationship']);
                    $relation =   $relation ? htmlspecialchars(implode(PHP_EOL, $relation)) : '';

                    $sheet->setCellValue('A' . $value_cell, ($k + 1))
                        ->setCellValue('B' . $value_cell,  (string) $value['serial_no'])
                        ->setCellValue('C' . $value_cell,  DataEncryption::dataDecrypt($value['permanent_phone']))
                        ->setCellValue('D' . $value_cell, $value['candidate_designation'] ? $all_designation[$value['candidate_designation']] : '')
                        ->setCellValue('E' . $value_cell,  ($value['eligible_district']) ? $district_by_slug[$value['eligible_district']] : '')
                        ->setCellValue('F' . $value_cell,  $description)
                        ->setCellValue('G' . $value_cell,  $reference)
                        ->setCellValue('H' . $value_cell,  $relation );


                    $sheet->getStyle('A:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A:G')->getAlignment()->setWrapText(true);
                    $sheet->getRowDimension($value_cell)->setRowHeight(30);
                    $value_cell++;
                }
                $xlxFile =  str_replace(' ', '', strtolower('all_reference_')) . '_' . time();
                $xlxRoot = Yii::getAlias('@rootDirFilUpload') . '/media/exportXls/';
                $filenameXls = $xlxRoot . $xlxFile . '.xlsx';
                $writer = new Xlsx($spreadsheet);
                $writer->save($filenameXls);

                 // download and delete xls
                if (file_exists($xlxRoot . $xlxFile . '.xlsx')) {
                    header("Pragma: public");
                    header("Expires: 0");
                    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                    header("Cache-Control: private", false);
                    header('Content-type: application/xlsx');
                    header('Content-Disposition: attachment; filename=' . $xlxFile . '.xlsx');
                    readfile($xlxRoot . $xlxFile . '.xlsx');
                    @unlink($xlxRoot . $xlxFile . '.xlsx');
                }
                exit();
        }       
           

        }catch(\Exception $e){
            Yii::$app->getSession()->setFlash('error', $e->getMessage());       
            return $this->redirect(Yii::$app->request->referrer);           
        }   
        die('This is not available now.');
    }


    /**
     * reference-candidate-pdf
     */
    public function actionReferenceCandidatePdf()
    {
        $searchModel = Sailors::find()->select(['id', 'name', 'father_name', 'serial_no', 'exam_date', 'eligible_district', 'batch_id', 'center_id', 'permanent_phone', 'permanent_district', 'candidate_designation', 'ssc_gpa', 'ssc_group', 'referred_by', 'reference_details', 'relationship', 'photo'])->where(['have_reference' => Constants::YES]);
        $search_param = Yii::$app->session->get('reference_candidate_query_param');

           // Some condition to check
        if (!isset($search_param['SailorsSearch']) || empty($search_param['SailorsSearch']['center_id']) || empty($search_param['SailorsSearch']['exam_date'])) {      
            Yii::$app->getSession()->setFlash('error', 'Please select exam date & center.');       
            return $this->redirect(Yii::$app->request->referrer);
        }


        // if (isset($search_param['SailorsSearch'])) {
        //     if (empty($search_param['SailorsSearch']['eligible_district']) || empty($search_param['SailorsSearch']['batch_id'])) {
        //         die('You must have select eligible district & Batch');
        //     }
        //     foreach ($search_param['SailorsSearch'] as $k => $val) {
        //         if ($val) $searchModel->andFilterWhere([$k => $val]);
        //     }
        // } else {
        //     die('You must have select eligible district & Batch');
        // }

        foreach ($search_param['SailorsSearch'] as $k => $val) {
            if ($val) $searchModel->andFilterWhere([$k => $val]);
        }
        // echo  $searchModel->createCommand()->getRawSql();
        // die();
        
        $searchModel = $searchModel->orderBy('updated_dt ASC')->asArray()->all();       


        $configuration = SailorBatchConfiguration::find()
            ->select(['id', 'du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'])
            ->where(['batch_id' => $search_param['SailorsSearch']['batch_id']])
            ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $search_param['SailorsSearch']['eligible_district'] . "(,|$)"])->asArray()->one();
       
        // Pdf library 
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'margin_top' => 15,
            'margin_left' => 4,
            'margin_right' => 4,
            'margin_bottom' => 5,
            'mirrorMargins' => false,
            'format' => 'A4-L',
            'orientation' => 'P'
            // 'format' => [190, 236],
            //'orientation' => 'L'
        ]);
        $mpdf->curlAllowUnsafeSslRequests = true;
        $mpdf->debug = false;
        $mpdf->SetHTMLFooter('<div style="text-align: right;">Page - {PAGENO}/{nbpg}</div>');
        // $mpdf->Image('https://www.joinnavysailor.org/media/main_logo.png', 0, 0, 210, 297, 'png', '', true, false);
        if ($searchModel) 
            $mpdf->WriteHTML($this->renderPartial('pdf/reference_candidate_pdf', ['model' => $searchModel, 'configuration' => $configuration], true, false));
        else
            $mpdf->WriteHTML('<h1 style="text-align: center;">No Data Found</h1>');
        
        $mpdf->Output('reference_candidate.pdf', 'I');
        exit();
       
    }

   
    /**
     * reference-candidate-pdf
     */
    public function actionReferenceDeCandidatePdf()
    {
        $searchModel = DeSailors::find()->select(['id', 'name', 'father_name', 'serial_no', 'exam_date', 'eligible_district', 'batch_id', 'center_id', 'permanent_phone', 'permanent_district', 'candidate_designation', 'ssc_gpa', 'ssc_group', 'referred_by', 'reference_details', 'relationship'])->where(['have_reference' => Constants::YES]);
        $search_param = Yii::$app->session->get('reference_candidate_query_param');
        if (isset($search_param['DeSailorsSearch'])) {
            if (empty($search_param['DeSailorsSearch']['eligible_district']) || empty($search_param['DeSailorsSearch']['batch_id'])) {
                die('You must have select eligible district & Batch');
            }
            foreach ($search_param['DeSailorsSearch'] as $k => $val) {
                if ($val) $searchModel->andFilterWhere([$k => $val]);
            }
        } else {
            die('You must have select eligible district & Batch');
        }
        $searchModel = $searchModel->orderBy('updated_dt ASC')->asArray()->all();
        /// echo  $searchModel->createCommand()->getRawSql();

        $configuration = SailorBatchConfiguration::find()
            ->select(['id', 'du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'])
            ->where(['batch_id' => $search_param['DeSailorsSearch']['batch_id']])
            ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $search_param['DeSailorsSearch']['eligible_district'] . "(,|$)"])->asArray()->one();
        if ($searchModel) {
            // Pdf library 

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 15,
                'margin_left' => 4,
                'margin_right' => 4,
                'margin_bottom' => 5,
                'mirrorMargins' => false,
                'format' => 'A4-L',
                'orientation' => 'P'
                // 'format' => [190, 236],
                //'orientation' => 'L'
            ]);
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->debug = false;
            $mpdf->SetHTMLFooter('<div style="text-align: right;">Page - {PAGENO}/{nbpg}</div>');
            // $mpdf->Image('https://www.joinnavysailor.org/media/main_logo.png', 0, 0, 210, 297, 'png', '', true, false);
            $mpdf->WriteHTML($this->renderPartial('pdf/reference_candidate_pdf', ['model' => $searchModel, 'configuration' => $configuration], true, false));
            $mpdf->Output('reference_candidate.pdf', 'I');
            exit();
        }
    }


    protected function getBranch($candidate_designation)
    {
        $branch = CanDesignation::getAllDesignationSession($candidate_designation);
        $explode = explode('(', $branch);
        $b_name = '';
        if (array_key_exists(0, $explode))
            $b_name .= $explode[0];
        if (array_key_exists(1, $explode))
            $b_name .=  '(' . $explode[1];
        return  $b_name;
    }
    protected function getDescription($val)
    {
        $descAr  = [];
        $descAr['name'] = 'Name : ' . $val['name'];
        $descAr['f_name'] = 'F/Name : ' . $val['father_name'];
        $descAr['dist'] = 'Dist: ' . ucfirst(strtolower($val['permanent_district']));
        if ($val['ssc_gpa'] && $val['ssc_group']) {
            $descAr['gpa'] = 'GPA : ' . $val['ssc_gpa'] . ',' . $val['ssc_group'];
        }

        return $descAr;
    }
    protected function getReference($val)
    {
        $ref = [];
        if (!empty($val)) {
            $ref_dec = json_decode($val, true);
            foreach ($ref_dec as $k => $v) {
                if ($v)   $ref[] = $v;
            }
        }
        return $ref;
    }
    protected function getRelation($val)
    {
        $relat = [];
        if (!empty($val)) {
            $ref_dec = json_decode($val, true);
            foreach ($ref_dec as $k => $v) {
                if ($v)  $relat[] = $v;
            }
        }

        return $relat;
    }

    /**
     * reference-candidate-excel
     */
    public function actionReferenceCandidateExcel()
    {
        $searchModel = Sailors::find()->select(['id', 'name', 'father_name', 'serial_no', 'exam_date', 'eligible_district', 'batch_id', 'center_id', 'permanent_phone', 'permanent_district', 'candidate_designation', 'ssc_gpa', 'ssc_group', 'referred_by', 'reference_details', 'relationship', 'photo'])->where(['have_reference' => Constants::YES]);
        $search_param = Yii::$app->session->get('reference_candidate_query_param');


        if (!isset($search_param['SailorsSearch']) || empty($search_param['SailorsSearch']['center_id']) || empty($search_param['SailorsSearch']['exam_date'])) {      
            Yii::$app->getSession()->setFlash('error', 'Please select exam date & center.');       
            return $this->redirect(Yii::$app->request->referrer);
        }

        foreach ($search_param['SailorsSearch'] as $k => $val) {
            if ($val) $searchModel->andFilterWhere([$k => $val]);
        }   
       
        // if (isset($search_param['SailorsSearch'])) {

        //     if (empty($search_param['SailorsSearch']['eligible_district']) || empty($search_param['SailorsSearch']['batch_id'])) {
        //         die('You must have select eligible district & Batch');
        //     }

        //     foreach ($search_param['SailorsSearch'] as $k => $val) {
        //         if ($val) $searchModel->andFilterWhere([$k => $val]);
        //     }
        // } else {
        //     die('You must have select eligible district & Batch');
        // }


        $searchModel = $searchModel->orderBy('eligible_district')->asArray()->all();
        /// echo  $searchModel->createCommand()->getRawSql();

        // $configuration = SailorBatchConfiguration::find()
        //     ->select(['id', 'du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'])
        //     ->where(['batch_id' => $search_param['SailorsSearch']['batch_id']])
        //     ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $search_param['SailorsSearch']['eligible_district'] . "(,|$)"])->asArray()->one();

        if ($searchModel) {

            $district_by_slug = Districts::getAllActiveDistrictBySlug();
            $model =  $searchModel;

            $centerName =  ($model[0]['center_id']) ? SailorCenters::find()->select(['id', 'name_en'])->where(['id' => $model[0]['center_id']])->one()->name_en : '';
            $ex_date  = $model[0]['exam_date'] ?? date('d-m-Y');
            $district = $model[0]['eligible_district'];

            $district_full_name = Districts::find()
                ->select(['name_en'])
                ->where(['in', 'slug', $district])
                ->asArray()
                ->one();

            if ($district_full_name)
                $district_full_name = $district_full_name['name_en'];
            else $district_full_name = '';

            $branchs = [];
            foreach ($model as $k => $val){
                $branch = CanDesignation::getAllDesignationSession($val['candidate_designation']);
                $branchs[] = $branch;  
            }
            $branchs = array_unique($branchs);

            // $total_candidate = Sailors::find()->where(['eligible_district' => $district])->andWhere(['batch_id' => $model[0]['batch_id']])->andWhere(['center_id' => $model[0]['center_id']])->andWhere(['not', ['exam_group' => null]])->count();

            $total_candidate = Sailors::find()->where(['batch_id' => $model[0]['batch_id']])
                ->andWhere(['center_id' => $model[0]['center_id']])
                ->andWhere(['exam_date' => $model[0]['exam_date']])
                ->andWhere(['not', ['exam_group' => null]])->andWhere(['application_status' => 1])->count();


            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            // $sheet->setCellValue('B2',  strtoupper($district_full_name));
            $sheet->setCellValue('B2',  'Branch : '.htmlspecialchars(implode(' / ', $branchs)));
            $sheet->setCellValue('B3', htmlspecialchars(strtoupper($centerName)));
            $dt = 'DATE ' . strtoupper(date('d M Y', strtotime($ex_date))) . '-TOTAL APPLICANT-' . $total_candidate;
            $sheet->setCellValue('B4', strtoupper(htmlspecialchars($dt)));

            // Merge cells          
            $sheet->mergeCells('B2:H2');
            $sheet->mergeCells('B3:H3');
            $sheet->mergeCells('B4:H4');
            // Set alignment and formatting  
            $sheet->getStyle('C3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $init_cell = 6;
            $sheet->setCellValue('A' . $init_cell, 'SL')
                ->setCellValue('B' . $init_cell, 'Roll')
                ->setCellValue('C' . $init_cell, 'Mobile')
                ->setCellValue('D' . $init_cell, 'District')
                ->setCellValue('E' . $init_cell, 'Description')
                ->setCellValue('F' . $init_cell, 'Reference')
                ->setCellValue('G' . $init_cell, 'Relationship')
                ->setCellValue('H' . $init_cell, 'Subject')
                ->setCellValue('I' . $init_cell, 'Picture');

            $sheet->getStyle('A:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $value_cell = $init_cell + 1;
            foreach ($model  as $k => $value) {
                // $branch = $this->getBranch($value['candidate_designation']);
                $district = $district_by_slug[$value['eligible_district']] ?? $value['eligible_district']; 
                $description = $this->getDescription($value);
                $reference = $this->getReference($value['referred_by']);
                $relation = $this->getRelation($value['relationship']);

                $spreadsheet->getActiveSheet()->getRowDimension($value_cell)->setRowHeight(80);


                $description =   $description ? htmlspecialchars(implode(PHP_EOL, $description)) : '';
                $reference =   $reference ? htmlspecialchars(implode(PHP_EOL, $reference)) : '';
                $relation =   $relation ? htmlspecialchars(implode(PHP_EOL, $relation)) : '';

                $subject = "B-" . PHP_EOL . "E-" . PHP_EOL . "M-" . PHP_EOL . "Sc-" . PHP_EOL . "Gk-";

                $sheet->setCellValue('A' . $value_cell, ($k + 1))
                    ->setCellValue('B' . $value_cell,  (string) $value['serial_no'])
                    ->setCellValue('C' . $value_cell,  DataEncryption::dataDecrypt($value['permanent_phone']))
                    ->setCellValue('D' . $value_cell, $district)
                    ->setCellValue('E' . $value_cell, $description)
                    ->setCellValue('F' . $value_cell, $reference)
                    ->setCellValue('G' . $value_cell,  $relation)
                    ->setCellValue('H' . $value_cell,  $subject);

                // Set vertical alignment to top
                $sheet->getStyle('A:I')->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // Set the cell to wrap text
                $sheet->getStyle('A:H')->getAlignment()->setWrapText(true);               

                // if ($value['photo'] && file_exists(Yii::getAlias('@rootDirFilUpload') . $value['photo'])) {
                if ($value['photo'] &&  Yii::$app->r2Storage->fileExists($value['photo'])  ) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Photo');
                    $drawing->setDescription('Photo');
                    $drawing->setPath( Yii::$app->r2Storage->fileUrl. $value['photo']); // put your path and image here
                    $drawing->setCoordinates('I' . $value_cell);
                    // $drawing->setOffsetX(110);
                    //  $drawing->setRotation(25);
                    $drawing->getShadow()->setVisible(true);
                    // $drawing->getShadow()->setDirection(45);
                    $drawing->setHeight(75);
                    $drawing->setWorksheet($spreadsheet->getActiveSheet());
                }
                ///  ->setCellValue('H' . $value_cell, 'Photo');
                $value_cell++;
            }
            $xlxFile =  str_replace(' ', '', strtolower('reference_')) . '_' . time();
            $xlxRoot = Yii::getAlias('@rootDirFilUpload') . '/media/exportXls/';
            $filenameXls = $xlxRoot . $xlxFile . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $writer->save($filenameXls);

            // download and delete xls
            if (file_exists($xlxRoot . $xlxFile . '.xlsx')) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header('Content-type: application/xlsx');
                header('Content-Disposition: attachment; filename=' . $xlxFile . '.xlsx');
                readfile($xlxRoot . $xlxFile . '.xlsx');
                @unlink($xlxRoot . $xlxFile . '.xlsx');
            }
            exit();
        }

        Yii::$app->getSession()->setFlash('error', 'No Record found.');       
        return $this->redirect(Yii::$app->request->referrer);
    }


    // district wise candidate 

    public function actionDistrictCandidate()
    {

        $model = new Report();
        $model->scenario = $model::CANDIDATE_DISTRICT_WISE;
        $sailor_model = [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            $sailor_model = Sailors::find()
                ->select(['candidate_designation', 'COUNT(*) AS candidate_count'])
                ->where(['batch_id' => $model->batch])
                ->andFilterWhere(['center_id' => $model->center])
                ->andWhere(['in', 'eligible_district', $model->district])
                ->andWhere(['payment_status' => Constants::PAYMENT_PAID])
                ->andWhere(['IS NOT', 'serial_no', null])
                ->andWhere(['application_status' => Constants::STATUS_ACTIVE])
                ->groupBy('candidate_designation')
                ->orderBy('candidate_designation')
                ->asArray()
                ->all();

            Yii::$app->session->set('report',  $sailor_model);
            Yii::$app->session->set('filter_value',  $model->attributes);
        }



        return $this->render('district_candidate', ['model' => $model, 'sailor' => $sailor_model]);
    }

    // center-date-candidate

    public function actionCenterDateCandidate()
    {

        $model = new Report();
        $model->scenario = $model::CANDIDATE_CENTER_DATE_WISE;
        $sailor_model = [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            $sailor_model = Sailors::find()
                ->select(['candidate_designation', 'COUNT(*) AS candidate_count'])
                ->where(['batch_id' => $model->batch])
                ->andFilterWhere(['center_id' => $model->center])
                ->andWhere(['exam_date'=>$model->exam_date])
                ->andWhere(['payment_status' => Constants::PAYMENT_PAID])
                ->andWhere(['IS NOT', 'serial_no', null])
                ->groupBy('candidate_designation')
                ->orderBy('candidate_designation')
                ->asArray()
                ->all();

              
                

            Yii::$app->session->set('report',  $sailor_model);
            Yii::$app->session->set('filter_value',  $model->attributes);
        }



        return $this->render('center_date_candidate', ['model' => $model, 'sailor' => $sailor_model]);
    }


    /**
     * exam-date-center-candidate-pdf
     */
    public function actionExamDateCenterCandidatePdf()
    {
        if (Yii::$app->session->has('report')) {
            $session_value = Yii::$app->session->get('report');
            $filter_value_value = Yii::$app->session->get('filter_value');
            // Pdf library 
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 10,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_bottom' => 0,
                'mirrorMargins' => true,
                // 'format' => [190, 236],
                //'orientation' => 'L'
            ]);
            $mpdf->WriteHTML($this->renderPartial('pdf/exam_date_center_candidate_pdf', ['model' => $session_value, 'filter' => $filter_value_value], true, false));
            $mpdf->Output('district_wise_candidate_' . time() . '.pdf', 'I');
            exit();
        }
    }
    /**
     * candidate-filter-pdf 
     */
    public function actionDistrictCandidatePdf()
    {
        if (Yii::$app->session->has('report')) {
            $session_value = Yii::$app->session->get('report');
            $filter_value_value = Yii::$app->session->get('filter_value');
            // Pdf library 
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 10,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_bottom' => 0,
                'mirrorMargins' => true,
                // 'format' => [190, 236],
                //'orientation' => 'L'
            ]);
            $mpdf->WriteHTML($this->renderPartial('pdf/district_candidate_pdf', ['model' => $session_value, 'filter' => $filter_value_value], true, false));
            $mpdf->Output('district_wise_candidate_' . time() . '.pdf', 'I');
            exit();
        }
    }
    /**
     * district-candidate-excel
     */

    public function actionDistrictCandidateExcel()
    {
        if (Yii::$app->session->has('report')) {
            $session_value = Yii::$app->session->get('report');
            $filter_value_value = Yii::$app->session->get('filter_value');


            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Photo');
            $drawing->setDescription('Photo');
            $drawing->setPath(Yii::getAlias('@rootDirFilUpload') . '/media/main_logo.png'); // put your path and image here
            $drawing->setCoordinates('D1');
            // $drawing->setOffsetX(110);
            //  $drawing->setRotation(25);
            $drawing->getShadow()->setVisible(true);
            // $drawing->getShadow()->setDirection(45);
            $drawing->setHeight(100);
            $drawing->setWorksheet($spreadsheet->getActiveSheet());
            $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(80);
            $sheet->mergeCells('A1:C1');

            $sheet->setCellValue('B2', 'বাংলাদেশ নৌবাহিনী');
            $sheet->mergeCells('B2:C2');

            $sheet->setCellValue('B2', 'ব্যাচ:' . SailorBatchs::getAllBatchSession($filter_value_value['batch']));
            $sheet->mergeCells('B2:C2');

            // $sheet->setCellValue('B4', 'কেন্দ্র:' .  $filter_value_value['center'] ? SailorCenters::getAllCenterSession($filter_value_value['center']) : '');
            // $sheet->mergeCells('B4:H4');

            $sheet->setCellValue('B3', 'জেলা:' . ucfirst($filter_value_value['district']));
            $sheet->mergeCells('B3:H3');


            $init_cell = 5;
            $sheet->setCellValue('A' . $init_cell, 'SL')
                ->setCellValue('B' . $init_cell, 'Designation')
                ->setCellValue('C' . $init_cell, 'Total Candidate');

            $value_cell = $init_cell + 1;

            $total = 0;

            foreach ($session_value  as $k => $value) {
                $total  += $value['candidate_count'];
                $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
                $sheet->setCellValue('A' . $value_cell, ($k + 1))
                    ->setCellValue('B' . $value_cell,  $desig)
                    ->setCellValue('C' . $value_cell, $value['candidate_count']);

                $value_cell++;
            }

            $sheet->setCellValue('A' . $value_cell, '')
                ->setCellValue('B' . $value_cell,  'Total')
                ->setCellValue('C' . $value_cell, $total);


            $xlxFile = 'district_wise_candidate_' . time();
            $xlxRoot = Yii::getAlias('@rootDirFilUpload') . '/media/exportXls/';
            $filenameXls = $xlxRoot . $xlxFile . '.xlsx';

            $writer = new Xlsx($spreadsheet);
            $writer->save($filenameXls);

            // download and delete xls
            if (file_exists($xlxRoot . $xlxFile . '.xlsx')) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Cache-Control: private", false);
                header('Content-type: application/xlsx');
                header('Content-Disposition: attachment; filename=' . $xlxFile . '.xlsx');
                readfile($xlxRoot . $xlxFile . '.xlsx');
                @unlink($xlxRoot . $xlxFile . '.xlsx');
            }
            exit();
        }
    }



    /**
     * reference-candidate-pdf
     */
    public function actionReferenceCandidateWord()
    {

        $searchModel = Sailors::find()->select(['id', 'name', 'father_name', 'serial_no', 'exam_date', 'eligible_district', 'batch_id', 'center_id', 'permanent_phone', 'permanent_district', 'candidate_designation', 'ssc_gpa', 'ssc_group', 'referred_by', 'reference_details', 'relationship', 'photo'])->where(['have_reference' => Constants::YES]);
        $search_param = Yii::$app->session->get('reference_candidate_query_param');      

        if (!isset($search_param['SailorsSearch']) || empty($search_param['SailorsSearch']['center_id']) || empty($search_param['SailorsSearch']['exam_date'])) {      
            Yii::$app->getSession()->setFlash('error', 'Please select exam date & center.');       
            return $this->redirect(Yii::$app->request->referrer);
        }

        foreach ($search_param['SailorsSearch'] as $k => $val) {
            if ($val) $searchModel->andFilterWhere([$k => $val]);
        }         

        $searchModel = $searchModel->orderBy('updated_dt ASC')->asArray()->all();
        /// echo  $searchModel->createCommand()->getRawSql();

        $configuration = SailorBatchConfiguration::find()
            ->select(['id', 'du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'])
            ->where(['batch_id' => $search_param['SailorsSearch']['batch_id']])
            ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $search_param['SailorsSearch']['eligible_district'] . "(,|$)"])->asArray()->one();
        if ($searchModel) {
            $model =  $searchModel;

            $centerName =  ($model[0]['center_id']) ? SailorCenters::find()->select(['id', 'name_en'])->where(['id' => $model[0]['center_id']])->one()->name_en : '';
            $ex_date  = $model[0]['exam_date'] ?? date('d-m-Y');
            $district = $model[0]['eligible_district'];

            // $district_full_name = Districts::find()
            //     ->select(['name_en'])
            //     ->where(['in', 'slug', $district])
            //     ->asArray()
            //     ->one();
            // if ($district_full_name)
            //     $district_full_name = $district_full_name['name_en'];
            // else $district_full_name = '';

            // $total_candidate = Sailors::find()->where(['eligible_district' => $district])->andWhere(['batch_id' => $model[0]['batch_id']])->andWhere(['center_id' => $model[0]['center_id']])->andWhere(['not', ['exam_group' => null]])->count();


            $total_candidate = Sailors::find()->where(['batch_id' => $model[0]['batch_id']])
                ->andWhere(['center_id' => $model[0]['center_id']])
                ->andWhere(['exam_date' => $model[0]['exam_date']])
                ->andWhere(['not', ['exam_group' => null]])->andWhere(['application_status' => 1])->count();


            $total = 0;
            $medical = $petrolman = $seaman_comm_tech = $cook = $modc = $topas = 0;
            if ($configuration) {
                $seaman_comm_tech = !empty($configuration['du_uc_can_total']) ? $configuration['du_uc_can_total'] : 0;
                $medical = !empty($configuration['medical_can_total']) ? $configuration['medical_can_total'] : 0;
                $petrolman = !empty($configuration['pertol_store_can_total']) ? $configuration['pertol_store_can_total'] : 0;
                $cook = !empty($configuration['cook_steward_can_total']) ? $configuration['cook_steward_can_total'] : 0;
                $modc = !empty($configuration['modc_can_total']) ? $configuration['modc_can_total'] : 0;
                $topas = !empty($configuration['topass_can_total']) ? $configuration['topass_can_total'] : 0;
                $total = $seaman_comm_tech + $medical + $petrolman + $cook + $modc + $topas;
            }


            $district_by_slug = Districts::getAllActiveDistrictBySlug();
          
            // word 
            $phpWord = new PhpWord();

            $sectionStyle = [
                'marginTop'    => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(20), // 15 points
                'marginLeft'   => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(50),  // 4 points
                'marginRight'  => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(50),  // 4 points
                'marginBottom' => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(5),  // 5 points
                // 'pageSizeW'    => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(21.0),  // A4 width in cm
                // 'pageSizeH'    => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(29.7),  // A4 height in cm
                'orientation'  => 'landscape', // For 'P' (Portrait), 'landscape' for 'L'
            ];
            // $section = $phpWord->addSection([
            //     'pageSizeW' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(8.5),  // Width in inches
            //     'pageSizeH' => \PhpOffice\PhpWord\Shared\Converter::inchToTwip(11),   // Height in inches
            //     'orientation' => 'landscape', // Optional: Set orientation to landscape
            // ]);
            $section = $phpWord->addSection($sectionStyle);
            // $section->addText('Hello, this is a dynamically generated Word document.');

            // Add text to the section
            // $textCenter = 'TEAM ' . strtoupper($centerName);
            $textCenter = strtoupper($centerName);
            $textStyle = array(
                'name' => 'Times New Roman',
                'size' => 11,
                'bold' => true,
                'underline' => 'single' // Underline the text
            );
            $textStyleTbl = array(
                'name' => 'Times New Roman',
                'size' => 10,
                'bold' => false,

            );
            $paragraphStyleCenter = array(
                'alignment' => 'center' // Center the text
            );

            $paragraphStyle = [
                'spaceBefore' => 0, // No space before
                'spaceAfter' => 0,  // No space after
                'alignment' => 'center'
            ];

            $paragraphStyleTwo = [
                'spaceBefore' => 0, // No space before
                'spaceAfter' => 0,  // No space after

            ];

            $branchs = [];
            foreach ($model as $k => $val){
                $branch = CanDesignation::getAllDesignationSession($val['candidate_designation']);
                $branchs[] = $branch;  
            }
            $branchs = array_unique($branchs);

            $section->addText(
                strtoupper('Branch : '.implode(' / ', $branchs)),
                $textStyle,
                $paragraphStyle
            );
            $section->addText(
                htmlspecialchars(strtoupper($textCenter)),
                $textStyle,
                $paragraphStyle
            );
            $dt = 'DATE ' . strtoupper(date('d M Y', strtotime($ex_date))) . '-TOTAL APPLICANT-' . $total_candidate;
            $section->addText(
                strtoupper($dt),
                $textStyle,
                $paragraphStyle
            );
            // $section->addText("Quota ".."");
            // $section->addText(
            //     "Quota -" . strtoupper($district_full_name),
            //     $textStyle,
            // );

            $section->addText(
                "",
                $textStyle,
                $paragraphStyleTwo
            );


            $tableStyle = [
                'borderSize' => 5, // Border thickness in points
                'borderColor' => '000000', // Black color
                'cellMargin' => 14, // Margin inside cells
                'width' => 100 * 50, // Full width (100% of the page width)
            ];
            $cellStyle = [
                // 'valign' => 'center', // Vertical alignment
            ];
            $headerCellStyle = [
                // 'bold' => true,
                // 'align' => 'center',
            ];            


            $table = $section->addTable($tableStyle);
            

            // Add table header row
            $r_width = 3000;
            $table->addRow();
            $table->addCell(1000, $cellStyle)->addText('Ser', $headerCellStyle,  $paragraphStyleCenter); // Adjust width for full table
            $table->addCell($r_width, $cellStyle)->addText(htmlspecialchars('Roll & Mobile'), $headerCellStyle,  $paragraphStyleCenter);
            // $table->addCell($r_width, $cellStyle)->addText('Branch', $headerCellStyle,  $paragraphStyleCenter);
            $table->addCell($r_width, $cellStyle)->addText('District', $headerCellStyle,  $paragraphStyleCenter);
            $table->addCell(($r_width + 500), $cellStyle)->addText('Description', $headerCellStyle,  $paragraphStyleCenter);
            $table->addCell($r_width, $cellStyle)->addText('Reference', $headerCellStyle,  $paragraphStyleCenter);
            $table->addCell(($r_width + 500), $cellStyle)->addText('Relationship', $headerCellStyle,  $paragraphStyleCenter);
            $table->addCell($r_width, $cellStyle)->addText('Subject', $headerCellStyle,  $paragraphStyleCenter);
            // $table->addCell($r_width, $cellStyle)->addText('Medical', $headerCellStyle,  $paragraphStyleCenter);
            // $table->addCell(2000, $cellStyle)->addText('Fit/Unfit', $headerCellStyle,  $paragraphStyleCenter);
            $table->addCell(2000, $cellStyle)->addText('Picture', $headerCellStyle,  $paragraphStyleCenter);

            $sl = 1;
            foreach ($model as $k => $val) {
                // $branch = CanDesignation::getAllDesignationSession($val['candidate_designation']);
               
                // $explode = explode('(', $branch);
                // $b_name = '';
                // if (array_key_exists(0, $explode))
                //     $b_name .= $explode[0];
                // if (array_key_exists(1, $explode))
                //     $b_name .=  '(' . $explode[1];

                // echo '<p>(' . $explode[1] . '</p>';

                $descAr  = [];
                $descAr['name'] = 'Name : ' . $val['name'];
                $descAr['f_name'] = 'F/Name : ' . $val['father_name'];
                $descAr['dist'] = 'Dist: ' . ucfirst(strtolower($val['permanent_district']));

                // $desc = '';
                // $desc .= 'Name : ' . $val['name'] . ',';
                // $desc .= 'F/Name : ' . $val['father_name'] . ',';
                // $desc .= 'Dist: ' . ucfirst(strtolower($val['permanent_district'])) . ', ';

                if ($val['ssc_gpa'] && $val['ssc_group']) {
                    // $desc .=  'GPA : ' . $val['ssc_gpa'] . ',' . $val['ssc_group'];

                    $descAr['gpa'] = 'GPA : ' . $val['ssc_gpa'] . ',' . $val['ssc_group'];
                }

                $ref = [];
                if (!empty($val['referred_by'])) {
                    $ref_dec = json_decode($val['referred_by'], true);
                    foreach ($ref_dec as $k => $v) {
                        if ($v)   $ref[] = $v;
                        // if ($v) echo '<p>' . $v . '</p>';
                    }
                }

                $relat = [];
                if (!empty($val['relationship'])) {
                    $ref_dec = json_decode($val['relationship'], true);
                    foreach ($ref_dec as $k => $v) {
                        if ($v)  $relat[] = $v;
                    }
                }

                $district = $district_by_slug[$val['eligible_district']] ?? $val['eligible_district']; 


                $table->addRow();
                $table->addCell(1000, $cellStyle)->addText($sl, [], $paragraphStyleCenter); // Adjust width for full table
                $cellOne = $table->addCell($r_width, $cellStyle);
                $cellOne->addText('Roll No:' . $val['serial_no'], $textStyleTbl, $paragraphStyleTwo);
                $cellOne->addText('Mob:' . DataEncryption::dataDecrypt($val['permanent_phone']), $textStyleTbl, $paragraphStyleTwo);

                // $table->addCell($r_width, $cellStyle)->addText(htmlspecialchars($b_name),  $textStyleTbl);
                $table->addCell($r_width, $cellStyle)->addText(htmlspecialchars(ucfirst($district)),  $textStyleTbl);
                // $table->addCell($r_width, $cellStyle)->addText($desc, $textStyleTbl);

                $discCell =  $table->addCell(($r_width + 500), $cellStyle);

                foreach ($descAr as $keyd => $item)
                    $discCell->addText(htmlspecialchars($item), $textStyleTbl, $paragraphStyleTwo);



                $cellRef = $table->addCell($r_width, $cellStyle);
                foreach ($ref as $keyd => $item)
                    $cellRef->addText(htmlspecialchars($item), $textStyleTbl, $paragraphStyleTwo);

                $cellRela = $table->addCell(($r_width + 500), $cellStyle);
                foreach ($relat as $k => $v) {
                    $cellRela->addText(htmlspecialchars($v), $textStyleTbl, $paragraphStyleTwo);
                }
                // $table->addCell($r_width, $cellStyle)->addText($relat, $textStyleTbl);

                $cell = $table->addCell($r_width, $cellStyle);

                // Add formatted text (with line breaks) inside the cell
                $cell->addText('B-', $textStyleTbl, $paragraphStyleTwo);
                $cell->addText('E-', $textStyleTbl, $paragraphStyleTwo);
                $cell->addText('M-', $textStyleTbl, $paragraphStyleTwo);
                $cell->addText('Sc-', $textStyleTbl, $paragraphStyleTwo);
                $cell->addText('Gk-', $textStyleTbl, $paragraphStyleTwo);

                // $table->addCell($r_width, $cellStyle)->addText('');
                // $table->addCell(2000, $cellStyle)->addText('');

                if (!empty($val['photo']) && Yii::$app->r2Storage->fileExists($val['photo']) ) {
                    $table->addCell(2000)->addImage(
                        Yii::$app->r2Storage->fileUrl. $val['photo'], // Path to your image
                        [
                            'height' => 65
                        ]
                    );
                } else {
                    $table->addCell(2000, $cellStyle)->addText('');
                }
                $sl++;
            }


            // Set headers to force download
            // header('Content-type: text/html; charset=utf-8');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Disposition: attachment;filename="' . str_replace(' ', '', strtolower('reference_')) . '_' . time() . '.docx"');
            header('Cache-Control: max-age=0');

            // Save the file to PHP output stream
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save('php://output');

            exit; // Ensure the script ends after serving the file

        }

        Yii::$app->getSession()->setFlash('error', 'No data found .');       
        return $this->redirect(Yii::$app->request->referrer);
    }


    public function actionCenterCandidate()
    {

        $model = new Report();
        $model->scenario = $model::CANDIDATE_CENTER_WISE;
        $sailor_model = [];

        if ($this->request->isPost && $model->load($this->request->post())) {
            $sailor_model = Sailors::find()
                ->select(['candidate_designation', 'COUNT(*) AS candidate_count'])
                ->where(['batch_id' => $model->batch])
                ->andFilterWhere(['center_id' => $model->center])
                ->andFilterWhere(['exam_date' => $model->exam_date])
                ->andWhere(['payment_status' => Constants::PAYMENT_PAID])
                ->andWhere(['application_status' => Constants::STATUS_ACTIVE])
                ->andWhere(['IS NOT', 'serial_no', null])
                ->groupBy('candidate_designation')
                ->orderBy('candidate_designation')
                ->asArray()
                ->all();

            Yii::$app->session->set('report',  $sailor_model);
            Yii::$app->session->set('filter_value',  $model->attributes);
        }

        return $this->render('center_candidate', ['model' => $model, 'sailor' => $sailor_model]);
    }

    public function actionCenterCandidatePdf()
    {
        if (Yii::$app->session->has('report')) {            
            $session_value = Yii::$app->session->get('report');
            $filter_value_value = Yii::$app->session->get('filter_value');
            // Pdf library 
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 10,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_bottom' => 0,
                'mirrorMargins' => true,
                // 'format' => [190, 236],
                //'orientation' => 'L'
            ]);
            $mpdf->WriteHTML($this->renderPartial('pdf/center_candidate_pdf', ['model' => $session_value, 'filter' => $filter_value_value], true, false));
            $mpdf->Output('center_wise_candidate_' . time() . '.pdf', 'I');
            exit();
        }
    }

    /**
     * center-candidate-excel
     */

     public function actionCenterCandidateExcel()
     {
         if (Yii::$app->session->has('report')) {
             $session_value = Yii::$app->session->get('report');
             $filter_value_value = Yii::$app->session->get('filter_value');
 
 
             $spreadsheet = new Spreadsheet();
             $sheet = $spreadsheet->getActiveSheet();
 
             $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
             $drawing->setName('Photo');
             $drawing->setDescription('Photo');
             $drawing->setPath(Yii::getAlias('@rootDirFilUpload') . '/media/main_logo.png'); // put your path and image here
             $drawing->setCoordinates('D1');
             // $drawing->setOffsetX(110);
             //  $drawing->setRotation(25);
             $drawing->getShadow()->setVisible(true);
             // $drawing->getShadow()->setDirection(45);
             $drawing->setHeight(100);
             $drawing->setWorksheet($spreadsheet->getActiveSheet());
             $spreadsheet->getActiveSheet()->getRowDimension(1)->setRowHeight(80);
             $sheet->mergeCells('A1:C1');
 
             $sheet->setCellValue('B2', 'বাংলাদেশ নৌবাহিনী');
             $sheet->mergeCells('B2:C2');
 
             $sheet->setCellValue('B2', 'ব্যাচ:' . SailorBatchs::getAllBatchSession($filter_value_value['batch']));
             $sheet->mergeCells('B2:C2');
 
             $sheet->setCellValue('B3', 'কেন্দ্র:' .  $filter_value_value['center'] ? SailorCenters::getAllCenterSession($filter_value_value['center']) : ''); 
             $sheet->mergeCells('B3:H3');
 
            //  $sheet->setCellValue('B3', 'জেলা:' . ucfirst($filter_value_value['district']));
            //  $sheet->mergeCells('B3:H3');
 
 
             $init_cell = 5;
             $sheet->setCellValue('A' . $init_cell, 'SL')
                 ->setCellValue('B' . $init_cell, 'Designation')
                 ->setCellValue('C' . $init_cell, 'Total Candidate');
 
             $value_cell = $init_cell + 1;
 
             $total = 0;
 
             foreach ($session_value  as $k => $value) {
                 $total  += $value['candidate_count'];
                 $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
                 $sheet->setCellValue('A' . $value_cell, ($k + 1))
                     ->setCellValue('B' . $value_cell,  $desig)
                     ->setCellValue('C' . $value_cell, $value['candidate_count']);
 
                 $value_cell++;
             }
 
             $sheet->setCellValue('A' . $value_cell, '')
                 ->setCellValue('B' . $value_cell,  'Total')
                 ->setCellValue('C' . $value_cell, $total);
 
 
             $xlxFile = 'center_wise_candidate_' . time();
             $xlxRoot = Yii::getAlias('@rootDirFilUpload') . '/media/exportXls/';
             $filenameXls = $xlxRoot . $xlxFile . '.xlsx';
 
             $writer = new Xlsx($spreadsheet);
             $writer->save($filenameXls);
 
             // download and delete xls
             if (file_exists($xlxRoot . $xlxFile . '.xlsx')) {
                 header("Pragma: public");
                 header("Expires: 0");
                 header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                 header("Cache-Control: private", false);
                 header('Content-type: application/xlsx');
                 header('Content-Disposition: attachment; filename=' . $xlxFile . '.xlsx');
                 readfile($xlxRoot . $xlxFile . '.xlsx');
                 @unlink($xlxRoot . $xlxFile . '.xlsx');
             }
             exit();
         }
     }

    //  Apply with same ssc info
    public function actionSameAcademicInfo()
    {
        $model = new Report();
        $model->scenario = $model::CANDIDATE_CENTER_WISE;
        $model->exam_date = date('Y-m-d');
        $sailor_model = [];

       

        return $this->render('same_academic_info', ['model' => $model, 'sailor' => $sailor_model]);
    }


    protected function folderTurncate(){
        $folder = Yii::getAlias('@webroot') . '/district_wise/';
            $files = glob($folder . '*'); // Get all files in the folder
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file); // Delete the file
                }
            }
    }

    public function actionJsonForLs()
    {

        if (Yii::$app->user->isGuest || !in_array(Yii::$app->user->id, [1])){
            return Yii::$app->response->redirect(Yii::$app->homeUrl);
        }

        $model = new Report();
        $model->scenario = $model::CANDIDATE_CENTER_WISE;
        $model->exam_date = date('Y-m-d');
        $sailor_model = [];
        $sailor_model_district_wise_count = [];
        $returnData = [];      
        
        return $this->render('json_for_ls', ['model' => $model, 'sailor' => $sailor_model_district_wise_count]);
    } 
}
