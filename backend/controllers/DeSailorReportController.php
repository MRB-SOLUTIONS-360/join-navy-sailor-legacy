<?php

namespace backend\controllers;

use backend\models\Report;
use common\models\CanDesignation;
use common\models\DeSailors;
use common\models\SailorBatchConfiguration;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\models\Sailors;
use common\models\SailorsSearch;
use common\static\Constants;
use common\static\DataEncryption;
use common\static\StaticMethod;
use Yii;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DeSailorReportController extends \yii\web\Controller
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
            $sailor = DeSailors::find()
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


            $xlxFile = 'De_Sailor_Candidate_List_';
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
        $model = new Report();
        $model->scenario = $model::CANDIDATE_FILTER;
        $sailor = [];
        if ($this->request->isPost && $model->load($this->request->post())) {
            $sailor = DeSailors::find()
                ->select(['app_unique_id', 'photo', 'gender', 'candidate_designation', 'permanent_district', 'permanent_phone', 'center_id', 'batch_id', 'exam_date', 'exam_group', 'serial_no', 'name', 'ref_id', 'validation_id', 'card_type', 'card_no', 'trans_date', 'payment_status'])
                ->where(['batch_id' => $model->batch])
                ->andWhere(['center_id' => $model->center])
                ->andWhere(['in', 'eligible_district', $model->district])
                ->andWhere(['in', 'candidate_designation', $model->designation])
                ->andWhere(['payment_status' => Constants::PAYMENT_PAID])
                ->andFilterWhere(['exam_date' => $model->exam_date])
                ->andFilterWhere(['gender' => $model->gender])
                ->andWhere(['not', ['serial_no' => null]])
                //->andWhere(['REGEXP', 'marital_status', $model['marital_status']]); // 
                ->asArray()->orderBy('serial_no ASC')->all();

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
                ->setCellValue('G' . $init_cell, 'Phone No')
                ->setCellValue('H' . $init_cell, 'Serial No')
                ->setCellValue('I' . $init_cell, 'Exam Date')
                ->setCellValue('J' . $init_cell, 'Photo');

            $value_cell = $init_cell + 1;
            foreach ($session_value  as $k => $value) {
                $spreadsheet->getActiveSheet()->getRowDimension($value_cell)->setRowHeight(50);
                $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
                $sheet->setCellValue('A' . $value_cell, ($k + 1))
                    ->setCellValue('B' . $value_cell,  (string) $value['app_unique_id'])
                    ->setCellValue('C' . $value_cell,  $desig)
                    ->setCellValue('D' . $value_cell, ucfirst($value['permanent_district']))
                    ->setCellValue('E' . $value_cell, $value['name'])
                    ->setCellValue('F' . $value_cell, ($value['gender']) ? StaticMethod::gender($value['gender']) : '')
                    ->setCellValue('G' . $value_cell, DataEncryption::dataDecrypt($value['permanent_phone']))
                    ->setCellValue('H' . $value_cell, $value['serial_no'])
                    ->setCellValue('I' . $value_cell, date('d-m-Y', strtotime($value['exam_date'])));

                if ($value['photo'] && file_exists(Yii::getAlias('@rootDirFilUpload') . $value['photo'])) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Photo');
                    $drawing->setDescription('Photo');
                    $drawing->setPath(Yii::getAlias('@rootDirFilUpload') . $value['photo']); // put your path and image here
                    $drawing->setCoordinates('J' . $value_cell);
                    // $drawing->setOffsetX(110);
                    //  $drawing->setRotation(25);
                    $drawing->getShadow()->setVisible(true);
                    // $drawing->getShadow()->setDirection(45);
                    $drawing->setHeight(50);
                    $drawing->setWorksheet($spreadsheet->getActiveSheet());
                }
                ///  ->setCellValue('H' . $value_cell, 'Photo');
                $value_cell++;
            }

            $xlxFile = 'DE_Sailor_Candidate_Filter_List_';
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

        $model = new Report();
        $model->scenario = $model::CANDIDATE_MONITORING_BY;
        $sailor_model = [];
        if ($this->request->isPost && $model->load($this->request->post())) {

            ////
            $sailor = DeSailors::find()->where(['batch_id' => $model->batch]);
            if ($model->monitor_by == Constants::CAN_MONITOR_BY_IMAGE_MISSING) {
                $sailor->select(['id', 'name', 'app_unique_id', 'photo', 'gender', 'candidate_designation', 'serial_no', 'exam_date', 'phase']);
                $sailor->andWhere(["DATE_FORMAT(created_dt,'%Y-%m-%d')" => $model->create_date]);
                $sailor->andWhere(['not', ['serial_no' => null]]);
                $sailor->andWhere(['not', ['exam_date' => null]]);
                $sailor =  $sailor->asArray()->orderBy('app_unique_id ASC')->all();
                $missing_list = [];
                foreach ($sailor as $k => $val) {
                    if (empty($val['photo']) || !file_exists(Yii::getAlias('@rootDirFilUpload') . $val['photo']))
                        $missing_list[] = $val;
                }
                $sailor_model = $missing_list;
            }            
        }


        return $this->render('monitoring_application', ['model' => $model, 'sailor' => $sailor_model]);
    }


    /**
     * reference-candidate-pdf
     */
    public function actionReferenceCandidatePdf()
    {
        $searchModel = DeSailors::find()->select(['id', 'name', 'father_name', 'serial_no', 'exam_date', 'eligible_district', 'batch_id', 'center_id', 'permanent_phone', 'permanent_district', 'candidate_designation', 'ssc_gpa', 'ssc_group', 'referred_by', 'reference_details', 'relationship'])->where(['have_reference' => Constants::YES]);
        $search_param = Yii::$app->session->get('reference_candidate_query_param');
        if (isset($search_param['SailorsSearch'])) {
            if (empty($search_param['SailorsSearch']['eligible_district']) || empty($search_param['SailorsSearch']['batch_id'])) {
                die('You must have select eligible district & Batch');
            }
            foreach ($search_param['SailorsSearch'] as $k => $val) {
                if ($val) $searchModel->andFilterWhere([$k => $val]);
            }
        } else {
            die('You must have select eligible district & Batch');
        }
        $searchModel = $searchModel->orderBy('updated_dt ASC')->asArray()->all();
        /// echo  $searchModel->createCommand()->getRawSql();

        $configuration = SailorBatchConfiguration::find()
            ->select(['id', 'du_uc_can_total', 'medical_can_total', 'pertol_store_can_total', 'cook_steward_can_total', 'modc_can_total', 'topass_can_total'])
            ->where(['batch_id' => $search_param['SailorsSearch']['batch_id']])
            ->andFilterWhere(['REGEXP', 'district_slug', "(^|,)" . $search_param['SailorsSearch']['eligible_district'] . "(,|$)"])->asArray()->one();
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

    /**
     * reference-candidate-pdf
     */
    public function actionReferenceCandidateExcel()
    {
        $searchModel = DeSailors::find()->select(['id', 'name', 'father_name', 'serial_no', 'eligible_district', 'batch_id', 'permanent_phone', 'candidate_designation', 'ssc_gpa', 'ssc_group', 'referred_by', 'reference_details', 'relationship'])->where(['have_reference' => Constants::YES]);
        $search_param = Yii::$app->session->get('reference_candidate_query_param');
        if (isset($search_param['SailorsSearch'])) {
            foreach ($search_param['SailorsSearch'] as $k => $val) {
                if ($val) $searchModel->andFilterWhere([$k => $val]);
            }
        }
        $searchModel = $searchModel->orderBy('eligible_district')->asArray()->all();
        /// echo  $searchModel->createCommand()->getRawSql();
        if ($searchModel) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setCellValue('C2', 'We are working');
            $sheet->mergeCells('C2:E2');

            $xlxFile = 'Sailor_Reference_Candidate_List_';
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
}
