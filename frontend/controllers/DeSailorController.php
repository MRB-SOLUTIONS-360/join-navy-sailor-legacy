<?php

namespace frontend\controllers;

use common\models\CanEligibilityCheckInfo;
use common\models\DeSailors;
use common\models\Districts;
use common\models\Eligibility;
use common\models\payment\AamarPay;
use common\models\payment\SSLPayment;
use common\models\SailorBatchConfiguration;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\models\Subjects;
use common\models\Unions;
use common\models\Upozilas;
use common\models\User;
use common\static\Constants;
use common\static\DataEncryption;
use common\static\StaticMethod;
use Yii;
use yii\web\NotFoundHttpException;
use common\models\SailorBatchConfigurationExamDate;

use Da\QrCode\QrCode as DaQr;
use yii\web\BadRequestHttpException;
use yii\web\UploadedFile;

class DeSailorController extends \yii\web\Controller
{

    
    /**
     * Candidate payment phase = 1
     */
    public function actionPayment($slug = null)
    {
        $sailor = $this->findModel($slug);

        if ($sailor) {
            /// if candidate not in payment then redirect application page 
            if ($sailor->phase != Constants::SAILOR_PHASE_ONE) {
                Yii::$app->session->setFlash('application_close', "Please continue application from here");
                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
            }

            $sailor->scenario = $sailor::PAYMENT;
            $sailor->payment_type = '';
            $sailor->birth_registration_no =  Yii::$app->user->identity->birth_registration_no;
            //// get batch setting info for payment 
            $batch_setting_info = SailorBatchs::batchById($sailor['batch_id']);
            $payment_mode = ($batch_setting_info['payment_mode'] == Constants::YES) ? Constants::PAYMENT_MODE_LIVE : Constants::PAYMENT_MODE_SANDBOX;

            // Ajax validation set
            if (Yii::$app->request->isAjax && $sailor->load(Yii::$app->request->post())) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($sailor);
            }

            if ($sailor->load(Yii::$app->request->post())) {

                if ($sailor->validate()) {
                    /// check application is close or not
                    // if close , candidate can not apply 
                    $can_apply = SailorBatchs::isCandidateContinueApplication(batch_id: $sailor['batch_id'], isPaid: $sailor['payment_status']);
                    if ($can_apply &&  $can_apply['can_apply'] == Constants::TEXT_NO) {
                        Yii::$app->session->setFlash('application_close', "Sorry. Application is closed now.");
                        return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                    }

                    /// try for payment retry 
                    if ($sailor->payment_status == Constants::PAYMENT_UNPAID && !empty($sailor->validation_id)) {
                        $tran_ids = [];
                        if ($sailor->all_requested_tran_id) {
                            $tran_ids = json_decode($sailor->all_requested_tran_id, true);
                        } else  $tran_ids[] = $sailor->validation_id;

                        $responseData =  SSLPayment::allRequestListByTranIds(app_type: 'de_sailor', payment_mode: $payment_mode, tranIds: $tran_ids);

                        if ($responseData['first_paid_tran_id'] && !empty($responseData['details_paid_tran_id'])) {
                            $request = $responseData['details_paid_tran_id'];
                            $sailor->card_type = $request['card_type'] ?? '';
                            $sailor->trans_date = $request['tran_date'];
                            $sailor->ref_id = $request['val_id'];
                            $sailor->card_no = $request['card_brand'] ?? $request['bank_tran_id'];
                            $sailor->payment_api = json_encode($request, 128);
                            $sailor->payment_status = Constants::PAYMENT_PAID;
                            $sailor->phase = Constants::SAILOR_PHASE_TWO;
                            $sailor->amount = $request['currency_amount'];
                            $sailor->store_amount = SSLPayment::STORE_AMOUNT;

                            $sailor->validation_id = $responseData['first_paid_tran_id'];
                            $sailor->all_paid_tran_id = json_encode($responseData['paid_tran_ids'] ?? [], true);

                            if ($sailor->save())
                                return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                        }
                    }
                    /// get eligibility info 
                    $eligibility_info = CanEligibilityCheckInfo::find()->select(['nationality', 'district', 'dob'])->where(['id' => $sailor->eligibility_info_id])->asArray()->one();

                    // if (empty($sailor->validation_id))
                    $sailor->validation_id = date('ymdhis') . rand(111111, 999999);
                    /// Payment info                  
                    $payment_info = [
                        'tran_id' => $sailor->validation_id,
                        'amount' => $batch_setting_info['payment_amount'],
                        'cus_name' => Yii::$app->user->identity->username,
                        'cus_email' => Yii::$app->user->identity->email ?? Yii::$app->user->identity->username . '_joinsailor@gmail.com',
                        'cus_add1' => isset($eligibility_info['district']) ? $eligibility_info['district'] . ', App ID:' . $sailor->app_unique_id : 'Dhaka',
                        'cus_city' => $eligibility_info['district'] ?? 'Dhaka',
                        'cus_state' => $eligibility_info['district'] ?? 'Dhaka',
                        'cus_country' =>  $eligibility_info['nationality'] ?? 'BD',
                        'cus_phone' =>   Yii::$app->user->identity->phone_no ?? '01718000000',
                        'opt_a' =>  'de_sailor',
                        'opt_b' => 'r_' . $sailor->id . '#u_' . Yii::$app->user->identity->id,    // modelid # user_id
                        'opt_c' => '',
                        'opt_d' => $sailor->app_unique_id, // Application ID
                        'payment_type' => $payment_mode,
                    ];

                    Yii::$app->session->set('payment_info', $payment_info);
                    //return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                    // set dob and nationality from eligibility info 
                    $sailor->dob = $eligibility_info['dob'] ?? null;
                    $sailor->nationality = $eligibility_info['nationality'] ?? null;
                    if ($sailor->save()) {
                        // return $this->redirect(Yii::$app->urlManager->createUrl(["online-payment/payment"]));
                        return $this->redirect(Yii::$app->urlManager->createUrl(["online-payment/payment-ssl"]));
                    }
                } else {
                    echo '<pre>';
                    print_r($sailor->getErrors());
                    echo '</pre>';
                    die();
                }

                /// check can continue application         

            }

            return $this->render(
                'payment',
                [
                    'model' => $sailor,
                    'batch_setting_info' => $batch_setting_info
                ]
            );
        }

        Yii::$app->session->setFlash('application_close', "Sorry try after some time");
        return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
    }

    /**
     * Candidate academic info 
     */
    public function actionAcademicInfo($slug = null)
    {

        $sailor = $this->findModel($slug);
        $courses_list = [];

        if ($sailor) {

            // course / trade list 
            $eligibility_model = Eligibility::eligibilityBySession($sailor->candidate_designation);

            if ($eligibility_model) {
                $courses = '';
                if ($eligibility_model['candidate_type'] == Constants::CANDIDATE_DE_SAILOR) $courses = $eligibility_model['hons_diploma_subject'];   // Artificer ->need diploma subjects 
                else if ($eligibility_model['candidate_type'] == Constants::CANDIDATE_DE_SAILOR_DOCKYARD)

                    $courses = $eligibility_model['trade_course_subject'];
                $courses_list = explode(',', $courses);

                if ($courses_list) {
                    $subjects = Subjects::getAllActiveSubjectByIdIn($courses_list);
                    $courses_list = Subjects::modelToDropdown($subjects);
                }
            }


            /// if candidate not in academic then redirect application page 
            if ($sailor->phase != Constants::SAILOR_PHASE_TWO) {
                Yii::$app->session->setFlash('application_close', "Please continue application from here");
                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
            }

            if ($sailor->candidate_type == Constants::CANDIDATE_DE_SAILOR) $sailor->scenario = $sailor::ACADEMIC_DE_SAILOR_ARTIFICER;
            else $sailor->scenario = $sailor::ACADEMIC_DE_SAILOR_DOCKYARD;

            /// Ajax validation set
            if (Yii::$app->request->isAjax && $sailor->load(Yii::$app->request->post())) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($sailor);
            }

            if ($sailor->load(Yii::$app->request->post()) && $sailor->validate()) {
                //// get batch setting info for payment 
                $batch_setting_info = SailorBatchs::batchById($sailor['batch_id']);
                /// check application is close or not
                // if close , candidate can not apply 
                $can_apply = SailorBatchs::isCandidateContinueApplication(batch_id: $sailor['batch_id'], isPaid: $sailor['payment_status']);
                if ($can_apply &&  $can_apply['can_apply'] == Constants::TEXT_NO) {
                    Yii::$app->session->setFlash('application_close', "Sorry. Application is closed now.");
                    return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                }

                //// SSC Academic Result 

                if (!$sailor->skipTeleTalkValidation) {
                    if ($sailor->ssc_edu_board && $sailor->ssc_reg_no && $sailor->ssc_roll_no && $sailor->ssc_passing_year) {
                        $ssc_result = StaticMethod::educationBoardResult(
                            examType: 'ssc',
                            board: trim($sailor->ssc_edu_board),
                            roll_no: trim($sailor->ssc_roll_no),
                            year: trim($sailor->ssc_passing_year),
                            reg_no: trim($sailor->ssc_reg_no)
                        );

                        if (!empty($ssc_result) && $ssc_result->responseDesc == 'Success' && ($ssc_result->result == 'P' || $ssc_result->result == 'PA' || $ssc_result->result == 'PB' || $ssc_result->result == 'PC')) {
                            // if api information and post information not same 
                            if (trim($ssc_result->rollNo) != trim($sailor->ssc_roll_no) || trim($ssc_result->passYear) != trim($sailor->ssc_passing_year) ||  trim($ssc_result->regNo) != trim($sailor->ssc_reg_no)  || strtolower(trim($ssc_result->board))  != strtolower(trim($sailor->ssc_edu_board))) {
                                Yii::$app->session->setFlash('error', "Please provide your valid SSC academic informaton");
                                return $this->render('academic_info',  ['model' => $sailor]);
                            }

                            $resultArr = (array) $ssc_result;
                            $edu_data['ssc_institute'] = $sailor->ssc_institute =  $resultArr['iName'] ?? '';
                            $edu_data['ssc_group'] = $sailor->ssc_group = $resultArr['studGroup'];
                            $edu_data['ssc_edu_board'] = $sailor->ssc_edu_board;
                            $edu_data['ssc_reg_no'] = $sailor->ssc_reg_no = $resultArr['regNo'];
                            $edu_data['ssc_passing_year'] = $sailor->ssc_passing_year = $resultArr['passYear'];
                            $edu_data['ssc_gpa'] = $sailor->ssc_gpa = $resultArr['gpa'];
                            $edu_data['name'] = $sailor->name = $resultArr['name'];
                            $edu_data['father_name'] = $sailor->father_name = $resultArr['father'];
                            $edu_data['mother_name'] = $sailor->mother_name = $resultArr['mother'];
                            $edu_data['ssc_roll_no'] = $sailor->ssc_roll_no = $resultArr['rollNo'];
                            $edu_data['gender'] = $sailor->gender = (strtoupper($resultArr['gender']) == 'MALE' || $resultArr['gender'] == '0'  || $resultArr['gender'] == '1') ? Constants::GENDER_MALE : Constants::GENDER_FEMALE;
                            $edu_data['dob_formated_y_m_d'] = '';

                            if (isset($resultArr['dob'])) {
                                $day = substr($resultArr['dob'], 0, 2);
                                $month = substr($resultArr['dob'], 2, 2);
                                $year = substr($resultArr['dob'], 4, 4);
                                $dob = $year . '-' . $month . '-' . $day;
                                $edu_data['dob_formated_y_m_d'] = $dob;

                                $sailor->dob = $dob;  // set dob according to teletalk result
                            }
                            $sailor->ssc_edu_data = json_encode($edu_data);
                            $sailor->ssc_teletalk_data = json_encode($resultArr);


                            //check ssc result and group            
                            $eligible_config = Eligibility::eligibilityBySession($sailor->candidate_designation);

                            if ($eligible_config && $sailor->ssc_edu_board != Constants::EDU_BOARD_TEC) {
                                if ($resultArr['gpa'] < $eligible_config['ssc_result']) {
                                    $sailor->addError('academic_info_already_used_in_ssc', ' আপনার প্রাপ্ত জিপিএ  ' . $resultArr['gpa'] . ' যা ' . $eligible_config['ssc_result'] . ' অপেক্ষা কম।');
                                    return $this->render('academic_info',  ['model' => $sailor, 'courses_list' => $courses_list,'skipTeleTalkValidation' => $sailor->skipTeleTalkValidation]);
                                } else if (!in_array(strtolower($resultArr['studGroup']), explode(',', $eligible_config['ssc_ac_group']))) {
                                    $sailor->addError('academic_info_already_used_in_ssc', 'আপনার একাডেমিক গ্রূপ ' . $resultArr['studGroup'] . ' যা এই ব্যাচে গ্রহণ জোগ্য নয়।');

                                    return $this->render('academic_info',  ['model' => $sailor, 'courses_list' => $courses_list,'skipTeleTalkValidation' => $sailor->skipTeleTalkValidation]);
                                }
                            }

                        } else {
                            Yii::$app->session->setFlash('error', "Sorry please provide your valid ssc academic informaton");
                            return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/academic-info", 'slug' => $slug]));
                        }
                    }
                    /// HSC Academic Result 
                    if ($sailor->hsc_dip_board && $sailor->hsc_dip_reg_no && $sailor->hsc_dip_roll_no && $sailor->hsc_dip_passing_year) {
                        $hsc_result = StaticMethod::educationBoardResult(
                            examType: 'hsc',
                            board: $sailor->hsc_dip_board,
                            roll_no: $sailor->hsc_dip_roll_no,
                            year: $sailor->hsc_dip_passing_year,
                            reg_no: $sailor->hsc_dip_reg_no
                        );

                        if (!empty($hsc_result) && $hsc_result->responseDesc == 'Success' && ($hsc_result->result == 'P' || $hsc_result->result == 'PA' || $hsc_result->result == 'PB')) {

                            $resultArr = (array) $hsc_result;

                            $returnhsc['hsc_dip_institute'] = $sailor->hsc_dip_institute =  $resultArr['iName'] ?? '';
                            $returnhsc['hsc_dip_group'] = $sailor->hsc_dip_group = $resultArr['studGroup'];
                            $returnhsc['hsc_dip_reg_no'] = $sailor->hsc_dip_reg_no = $resultArr['regNo'];
                            $returnhsc['hsc_dip_passing_year'] = $sailor->hsc_dip_passing_year = $resultArr['passYear'];
                            $returnhsc['hsc_dip_gpa'] = $sailor->hsc_dip_gpa = $resultArr['gpa'];
                            $returnhsc['hsc_dip_roll_no'] = $sailor->hsc_dip_roll_no = $resultArr['rollNo'];
                            $returnhsc['hsc_dip_board'] = $sailor->hsc_dip_board;
                            $sailor->hsc_edu_data = json_encode($returnhsc);
                            $sailor->hsc_teletalk_data = json_encode($resultArr);
                        }
                    }
                }else {
                    $ssc_fields = [
                        'ssc_institute', 'ssc_group', 'ssc_edu_board', 'ssc_reg_no',
                        'ssc_passing_year', 'ssc_gpa', 'name', 'father_name',
                        'mother_name', 'ssc_roll_no', 'gender', 'dob_formated_y_m_d'
                    ];
                    $edu_data = array_fill_keys($ssc_fields, '');
                    $sailor->ssc_edu_data = json_encode($edu_data);  
                    $sailor->ssc_teletalk_data = null;  
                    
                    //check ssc result and group            
                    $eligible_config = Eligibility::eligibilityBySession($sailor->candidate_designation);
                    if ($eligible_config && $sailor->ssc_edu_board != Constants::EDU_BOARD_TEC) {
                        if ($sailor['ssc_gpa'] < $eligible_config['ssc_result']) {
                            $sailor->addError('academic_info_already_used_in_ssc', ' আপনার প্রাপ্ত জিপিএ  ' . $sailor['ssc_gpa'] . ' যা ' . $eligible_config['ssc_result'] . ' অপেক্ষা কম।');
                            return $this->render('academic_info',  ['model' => $sailor, 'courses_list' => $courses_list,'skipTeleTalkValidation' => $sailor->skipTeleTalkValidation]);
                        } else if (!in_array(strtolower($sailor['ssc_group']), explode(',', $eligible_config['ssc_ac_group']))) {
                            $sailor->addError('academic_info_already_used_in_ssc', 'আপনার একাডেমিক গ্রূপ ' . $sailor['ssc_group'] . ' যা এই ব্যাচে গ্রহণ জোগ্য নয়।');
                            return $this->render('academic_info',  ['model' => $sailor, 'courses_list' => $courses_list,'skipTeleTalkValidation' => $sailor->skipTeleTalkValidation]);
                        }
                    }

                    $hsc_fields = [
                        'hsc_dip_institute', 'hsc_dip_group', 'hsc_dip_reg_no',
                        'hsc_dip_passing_year', 'hsc_dip_gpa', 'hsc_dip_roll_no', 'hsc_dip_board'
                    ];
                    $returnhsc = array_fill_keys($hsc_fields, '');
                    $sailor->hsc_edu_data = json_encode($returnhsc);
                    $sailor->hsc_teletalk_data = null;

                }


                $sailor->phase = Constants::SAILOR_PHASE_THREE;  // go personal info page

                // age calculate 
                if ($sailor->dob) {
                    $sailor->age_according_to_circular =  StaticMethod::getDifferenceBetweenTwoDateYearMonth(
                        maxDate: $sailor->dob,
                        minDate: $batch_setting_info['circular_date']
                    );
                    ///  $sailor->age_according_to_circular = $age_according_circular_date; 
                }

                if ($sailor->age_according_to_circular)
                    $sailor->age_according_to_circular =  str_replace('.', '-', $sailor->age_according_to_circular);


                if ($sailor->save())
                    return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/personal-info", 'slug' => $slug]));
                else {
                    echo '<pre>';
                    print_r($sailor->getErrors());
                    echo '</pre>';
                    die();
                }
            }






            return $this->render(
                'academic_info',
                [
                    'model' => $sailor,
                    'courses_list' => $courses_list,
                    'skipTeleTalkValidation' => $sailor->skipTeleTalkValidation
                ]
            );
        }
    }

    /**
     * Candidate personal info 
     */
    public function actionPersonalInfo($slug = null)
    {
        $sailor = $this->findModel($slug);

        $sailor->is_freedom_fighter = Constants::NO;
        $sailor->is_child_of_naval_officer = Constants::NO;

        $sailor->is_anser_vdp = Constants::NO;
        $sailor->is_khudro_jati_gosti = Constants::NO;

        if ($sailor) {
            /// if candidate not in personal info phase then redirect application page 
            if ($sailor->phase != Constants::SAILOR_PHASE_THREE && Yii::$app->request->getMethod() != 'POST') {
                Yii::$app->session->setFlash('application_close', "Please continue application from here");
                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
            }

            $prevImage = $sailor->photo;
            $sailor->scenario = $sailor::PERSONAL_INFO;

            // image no required
            if (!empty($prevImage) || !file_exists(Yii::getAlias('@rootDirFilUpload') . $prevImage))
                $sailor->scenario = $sailor::PERSONAL_INFO_WITH_IMAGE;



            /// Ajax validation set
            if (Yii::$app->request->isAjax && $sailor->load(Yii::$app->request->post())) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return \yii\widgets\ActiveForm::validate($sailor);
            }
            //echo   $sailor->scenario; die();
            /// form post and validate
            if ($sailor->load(Yii::$app->request->post())) {

                $batch_model = SailorBatchs::find()->select(['id', 'name_en'])->where(['id' => $sailor->batch_id])->asArray()->one();
                $batch_name = 'batch_not_found!';
                if ($batch_model) $batch_name = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $batch_model['name_en']), '_'));


                $uploadBasePath =  Yii::getAlias('@rootDirFilUpload');
                $r2Storage = Yii::$app->r2Storage;

                // if dir not exist make a dir and name is primary key
                if (!is_dir(Yii::getAlias('@rootDirFilUpload') . '/media/de_sailor_candidate/' . $sailor->id)) {
                    mkdir(Yii::getAlias('@rootDirFilUpload') . '/media/de_sailor_candidate/' . $sailor->id, 0777);
                }
                /// check application is close or not
                // if close , candidate can not apply 
                $can_apply = SailorBatchs::isCandidateContinueApplication(batch_id: $sailor['batch_id'], isPaid: $sailor['payment_status']);
                if ($can_apply &&  $can_apply['can_apply'] == Constants::TEXT_NO) {
                    Yii::$app->session->setFlash('application_close', "Sorry. Application is closed now.");
                    return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                }

                /// generate QR image 
                // $rq_name = '/media/de_sailor_candidate/' . $sailor->id . '/qr.png';
                // if (empty($sailor->qr_photo) || !file_exists(Yii::getAlias('@rootDirFilUpload') . $rq_name)) {
                //     $verification_link = Yii::getAlias('@rootMediaShow') . '/de-sailor/verify-candidate/?slug=' . $slug;
                //     $qrCode = (new DaQr($verification_link))->setSize(298)->setMargin(1);
                //     $qrCode->writeFile(Yii::getAlias('@rootDirFilUpload') . $rq_name); // writer defaults to PNG when none is specified

                //     $pathQr  = $uploadBasePath . $rq_name;
                //     $qrFileName =  $batch_name . '/' . $sailor->id . '_' . time() . '_qr' . '_' . '.png';
                //     $fileUrl = $r2Storage->uploadFile($qrFileName, $pathQr);
                //     if ($fileUrl)
                //         $sailor->qr_photo =  $qrFileName;
                //     unlink($pathQr);
                //     // $sailor->qr_photo = $rq_name;
                // }

                // if (empty($sailor->qr_photo) || !file_exists(Yii::getAlias('@rootDirFilUpload') . $rq_name)) {
                //     $verification_link = Yii::getAlias('@rootMediaShow') . '/de-sailor/verify-candidate/?slug=' . $slug;
                //     $qrCode = (new DaQr($verification_link))->setSize(298)->setMargin(1);
                //     $qrCode->writeFile(Yii::getAlias('@rootDirFilUpload') . $rq_name); // writer defaults to PNG when none is specified
                //     $sailor->qr_photo = $rq_name;
                // }

                // $fileImage = UploadedFile::getInstance($sailor, 'photo');

                // if ($fileImage) {
                //     $uploadBasePath =  Yii::getAlias('@rootDirFilUpload');
                //     $uniqueName = date('Ymdhms');
                //     $sailor->photo = '/media/de_sailor_candidate/' . $sailor->id . '/' . $uniqueName . '.' . $fileImage->extension;
                //     $path  = $uploadBasePath . $sailor->photo;
                //     // Unlink  file
                //     if (!empty($prevImage) &&  file_exists(Yii::getAlias('@rootDirFilUpload') . $prevImage))
                //         unlink(Yii::getAlias('@rootDirFilUpload') . $prevImage);
                // } else $sailor->photo = $prevImage;


                $fileImage =  UploadedFile::getInstance($sailor, 'photo');

                if ($fileImage) {
                    try {

                        $uniqueName = date('Ymdhms');
                        $sailor->photo = '/media/de_sailor_candidate/' . $sailor->id . '/' . $uniqueName . '.' . $fileImage->extension;
                        $path  = $uploadBasePath . $sailor->photo;
                        $unlink_dir = $uploadBasePath . '/media/de_sailor_candidate/' . $sailor->id;
                        // Save the uploaded file locally
                        $fileImage->saveAs($path);

                        $baseName = $fileImage->baseName;
                        $cleanBaseName = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName)));
                        $cleanFileName = $cleanBaseName . '.' . $fileImage->extension;
                        $fileName =  $batch_name . '/' . $sailor->id . '_' . time() . '_' . $cleanFileName;

                        $fileUrl = $r2Storage->uploadFile($fileName, $path);
                        if ($fileUrl)
                            $sailor->photo =  $fileName;

                        unlink($path);

                        if (is_dir($unlink_dir)) {
                            $files = array_diff(scandir($unlink_dir), array('.', '..'));
                            foreach ($files as $file) {
                                $filePath = $unlink_dir . DIRECTORY_SEPARATOR . $file;
                                unlink($filePath); // Delete the file                   
                            }
                            rmdir($unlink_dir);
                        }


                        // remove old file 
                        $r2Storage->deleteFile($prevImage);
                        // $prevImage
                        // need to unlink prev photo 

                    } catch (\Exception $e) {
                        Yii::error('Failed to upload file: ' . $e->getMessage(), __METHOD__);
                    }
                } else $sailor->photo = $prevImage;


                // age calculate 
                //// get batch setting info for payment 
                $batch_setting_info = SailorBatchs::batchById($sailor['batch_id']);
                if ($sailor->dob) {
                    $sailor->age_according_to_circular =  StaticMethod::getDifferenceBetweenTwoDateYearMonth(
                        maxDate: $sailor->dob,
                        minDate: $batch_setting_info['circular_date']
                    );
                }
                if ($sailor->age_according_to_circular)
                    $sailor->age_according_to_circular =  str_replace('.', '-', $sailor->age_according_to_circular);



                $sailor->phase = Constants::SAILOR_PHASE_FOUR;  // go Application preview     




                if ($sailor->validate()) {

                    // Data Decrypt for view
                    foreach ($sailor->encryption_fields_for_personal_info as $key => $field)
                        $sailor->$field = DataEncryption::dataEncrypt($sailor->$field);

                    $sailor->save(false);

                    // update user dob by application form dob 
                    // update user table dob 
                    if (Yii::$app->user->identity->dob !== $sailor->dob) {
                        $user = User::findOne(Yii::$app->user->identity->id);
                        $user->dob = $sailor->dob;
                        $user->save();
                    }

                    if ($fileImage) $fileImage->saveAs($path);


                    return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/application-preview", 'slug' => $slug]));
                } else {
                    echo $sailor->scenario;
                    echo '<br/>' . $sailor->photo;
                    echo '<br/>' . $sailor->ssc_reg_no;
                    echo '<pre>';
                    print_r($sailor->getErrors());
                    echo '</pre>';
                    die();
                }
            }

            /// district and permanent district 
            $district_dropdown = $permanent_district_dropdown  = Districts::getAllActiveDistrictBySlug($sailor->eligible_district);
            $all_district = Districts::getAllActiveDistrict();
            // unset nou scout and navy child dist 
            if (array_key_exists(Constants::DIST_NAVY_CHILD_SLUG, $all_district))
                unset($all_district[Constants::DIST_NAVY_CHILD_SLUG]);
            if (array_key_exists(Constants::DIST_NOU_SCOUT_SLUG, $all_district))
                unset($all_district[Constants::DIST_NOU_SCOUT_SLUG]);

            if (in_array($sailor->eligible_district, [Constants::DIST_NAVY_CHILD_SLUG, Constants::DIST_NOU_SCOUT_SLUG])) {
                $permanent_district_dropdown = Districts::getAllActiveDistrict();
                // unset nou scout and navy child dist 
                if (array_key_exists(Constants::DIST_NAVY_CHILD_SLUG, $permanent_district_dropdown))
                    unset($permanent_district_dropdown[Constants::DIST_NAVY_CHILD_SLUG]);
                if (array_key_exists(Constants::DIST_NOU_SCOUT_SLUG, $permanent_district_dropdown))
                    unset($permanent_district_dropdown[Constants::DIST_NOU_SCOUT_SLUG]);
            }

            $sailor->exam_center_name = $sailor->center_id ? SailorCenters::getAllCenterSession($sailor->center_id) : '';


            /// check is naval child 
            $can_eligible_info = CanEligibilityCheckInfo::find()->select(['id', 'candidate_type', 'p_o_no', 'rank', 'dob', 'nationality'])->where(['id' => $sailor->eligibility_info_id])->asArray()->one();
            if ($can_eligible_info) {
                if ($can_eligible_info['candidate_type'] == Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA) {
                    $sailor->is_child_of_naval_officer = Constants::YES;
                    $sailor->naval_office_no = $can_eligible_info['p_o_no'];
                    $sailor->naval_rank = $can_eligible_info['rank'];
                }

                /// if dob empty then come from eligible info 
                if (empty($sailor->dob)) {
                    $sailor->dob = $can_eligible_info['dob'];
                }
                if (empty($sailor->nationality)) {
                    $sailor->nationality = 'Bangladeshi';
                }
            }


            // course / trade list 
            $eligibility_model = Eligibility::eligibilityBySession($sailor->candidate_designation);
            $courses_list = [];
            if ($eligibility_model) {
                $courses = '';
                if ($eligibility_model['candidate_type'] == Constants::CANDIDATE_DE_SAILOR) $courses = $eligibility_model['hons_diploma_subject'];   // Artificer ->need diploma subjects 
                else if ($eligibility_model['candidate_type'] == Constants::CANDIDATE_DE_SAILOR_DOCKYARD)

                    $courses = $eligibility_model['trade_course_subject'];
                $courses_list = explode(',', $courses);

                if ($courses_list) {
                    $subjects = Subjects::getAllActiveSubjectByIdIn($courses_list);
                    $courses_list = Subjects::modelToDropdown($subjects);
                }
            }


            // Data Decrypt for view
            foreach ($sailor->encryption_fields_for_personal_info as $key => $field)
                $sailor->$field = $sailor->$field ? DataEncryption::dataDecrypt($sailor->$field) : '';


            $all_upazilas_current = [];
            if ($sailor->current_district) {
                $district = Districts::getIdBySlug($sailor->current_district);
                $all_upazilas_current = Upozilas::getUpazilaListCandidate($district['id']);
            }
            $all_upazilas_unions_current = [];
            if ($sailor->current_thana) {
                $all_upazilas_unions_current = Unions::getUnionsListForCandidate($sailor->current_thana);
            }
            $all_upazilas_permanent = [];
            if ($sailor->permanent_district) {
                $district = Districts::getIdBySlug($sailor->permanent_district);
                $all_upazilas_permanent = Upozilas::getUpazilaListCandidate($district['id']);
            }
            $all_upazilas_unions_permanent = [];
            if ($sailor->permanent_thana) {
                $all_upazilas_unions_permanent = Unions::getUnionsListForCandidate($sailor->permanent_thana);
            }

            return $this->render(
                'personal_info',
                [
                    'model' => $sailor,
                    'district_dropdown' => $district_dropdown,
                    'permanent_district_dropdown' => $permanent_district_dropdown,
                    'all_district' => $all_district,
                    'courses_list' => $courses_list,
                    'all_upazilas_current' => $all_upazilas_current,
                    'all_upazilas_unions_current' => $all_upazilas_unions_current,
                    'all_upazilas_permanent' => $all_upazilas_permanent,
                    'all_upazilas_unions_permanent' => $all_upazilas_unions_permanent,


                ]
            );
        }
    }


    /**
     * application-preview
     */
    public function actionApplicationPreview($slug = null)
    {
        $sailor = $this->findModel($slug);
        if ($sailor) {

            // if have roll return to list page 
            if ($sailor->serial_no) {
                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
            }

            $diploma_trade_course = '';
            if ($sailor->diploma_trade_course) {
                $data = Subjects::subjectFindById($sailor->diploma_trade_course);
                if ($data)
                    $diploma_trade_course = $data['name_en'];
            }

            // Data Decrypt for view
            foreach ($sailor->encryption_fields_for_personal_info as $key => $field)
                $sailor->$field = $sailor->$field ? DataEncryption::dataDecrypt($sailor->$field) : '';

            if ($sailor->current_thana)
                $sailor->current_thana = Upozilas::upazilaNameById($sailor->current_thana);

            if ($sailor->permanent_thana)
                $sailor->permanent_thana = Upozilas::upazilaNameById($sailor->permanent_thana);

            return $this->render(
                'application_preview',
                [
                    'model' => $sailor,
                    'diploma_trade_course' => $diploma_trade_course,
                ]
            );
        }
    }


    /**
     * application-preview
     */
    public function actionCompleteApplication($slug = null)
    {
        $sailor = $this->findModel($slug);
        if ($sailor) {
            // if have roll return to list page 
            if ($sailor->serial_no) {
                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
            }

            //// get batch setting info for payment 
            $batch_setting_info = SailorBatchs::batchById($sailor['batch_id']);
            /// check application is close or not
            // if close , candidate can not apply 
            $can_apply = SailorBatchs::isCandidateContinueApplication(batch_id: $sailor['batch_id'], isPaid: $sailor['payment_status']);
            if ($can_apply &&  $can_apply['can_apply'] == Constants::TEXT_NO) {
                Yii::$app->session->setFlash('application_close', "Sorry. Application is closed now.");
                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
            }

            if (Yii::$app->request->isPost) {
                /// if already have roll 
                if ($sailor->exam_date || $sailor->serial_no) {
                    $sailor->phase = Constants::SAILOR_PHASE_FIVE; // finally submit form 
                    $sailor->save(false);
                    return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                }

                /// roll from batch

                if ($batch_setting_info['roll_from'] == Constants::ROLL_FROM_BATCH) {
                    //get all configuration by batch 
                    $configuration_model =    SailorBatchConfiguration::configurationByBatchCenterGenderCanDesigDistrictSlugAll(
                        batch_id: $sailor['batch_id'],
                        center_id: $sailor['center_id'],
                        gender: $sailor['gender'],
                        candidate_designation: $sailor['candidate_designation'],
                        eligible_district: $sailor['eligible_district'],
                    );

                    // echo 'batch_id' . $sailor['batch_id'] . '<br/>';
                    // echo 'center_id' . $sailor['center_id'] . '<br/>';
                    // echo 'gender ' . $sailor['gender'] . '<br/>';
                    // echo 'candidate_designation ' . $sailor['candidate_designation'] . '<br/>';
                    // echo 'eligible_district ' . $sailor['eligible_district'] . '<br/>';
                  

                    // if no configuration found 
                    if (empty($configuration_model)) {
                        Yii::$app->session->setFlash('application_close', "Configuration missing please contact with support");
                        return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                    } else {
                        /// check have candidate this batch with roll 
                        /// Roll No  generate by Batch 
                        $roll_no = DeSailors::nextRollByBatchId(batchId: $sailor['batch_id'], batch_setting_info_roll_no: $batch_setting_info['start_roll']);
                        if (count($configuration_model) > 1) {
                            /// get 1st index couse of exam group asc order
                            $exam_group = $configuration_model[0];
                            $configuration_ids  = [];
                            if ($exam_group['roll_swap_in_group'] == Constants::YES) {
                                foreach ($configuration_model as $kk => $vv) {
                                    if ($vv['roll_swap_in_group'] == Constants::YES)
                                        $configuration_ids[$vv['id']] = $vv;
                                }
                            } else
                                $configuration_ids[$exam_group['id']]  = $exam_group;

                            /// get number of candidate each group 
                            $batch_config_keys = array_keys($configuration_ids);
                            $sailor_batch_wise_count  =  DeSailors::sailorBatchAndGroupWiseCount(batch_id: $sailor['batch_id'], center_id: $sailor['center_id'], gender: $sailor['gender'], candidate_designation: $sailor['candidate_designation'], eligible_district: $sailor['eligible_district'], batch_config_keys: $batch_config_keys);

                            if (!empty($sailor_batch_wise_count)) {
                                // if have roll 
                                $total_candidate = [];
                                foreach ($sailor_batch_wise_count as $key => $val) {
                                    if (array_key_exists($val['batch_config_id'], $configuration_ids)) {
                                        $total_candidate[$val['batch_config_id']] =  $val['total_candidate'];
                                        //$swap_yes_config_count[$val['batch_config_id']] = 2;  
                                    }
                                }
                                // check have candidate for configuration if not the set 0
                                foreach ($batch_config_keys as $k => $configId) {
                                    if (!array_key_exists($configId, $total_candidate))
                                        $total_candidate[$configId] = 0;
                                }

                                $config_id_for_batch_check = [];
                                foreach ($total_candidate as $ke => $value) {
                                    if (array_key_exists($ke, $configuration_ids) &&  $configuration_ids[$ke]['group_no_of_candidate'] > $value)
                                        $config_id_for_batch_check[$ke] = $value;
                                    else {
                                        $inactive_configuration  = SailorBatchConfiguration::findOne($ke);
                                        $inactive_configuration->status = Constants::NO;
                                        $inactive_configuration->save();
                                    }
                                }
                                // if empty config_id_for_batch_check
                                if (empty($config_id_for_batch_check)) {
                                    Yii::$app->session->setFlash('application_close', "Internal Error, Please Try Again Later.");
                                    return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                                }
                                /// get min candidate of by configuration id 
                                $min_array =  array_keys($config_id_for_batch_check, min($config_id_for_batch_check))[0];
                                $configuration_model = $configuration_ids[$min_array];
                            } else {
                                $configuration_model = reset($configuration_ids);
                            }
                        } else {
                            $batch_config_keys = [$configuration_model[0]['id']];
                            $sailor_batch_wise_count  =  DeSailors::sailorBatchAndGroupWiseCount(batch_id: $sailor['batch_id'], center_id: $sailor['center_id'], gender: $sailor['gender'], candidate_designation: $sailor['candidate_designation'], eligible_district: $sailor['eligible_district'], batch_config_keys: $batch_config_keys);
                          


                            if (!empty($sailor_batch_wise_count)) {

                                $total_candidate = $sailor_batch_wise_count[0]['total_candidate'];
                                if ($configuration_model[0]['group_no_of_candidate'] <= $total_candidate) {
                                    $inactive_configuration  = SailorBatchConfiguration::findOne($configuration_model[0]['id']);
                                    $inactive_configuration->status = Constants::NO;
                                    $inactive_configuration->save();

                                    Yii::$app->session->setFlash('application_close', "Configuration missing please contact with support");
                                    return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                                }
                            }
                            // no need check group_no_of_candidate and roll_swap_in_group
                            $configuration_model = $configuration_model[0];
                        }


                       $canInfo = [                           
                            'center_id'=> $sailor['center_id'],
                            'gender'=> $sailor['gender'],
                            'candidate_designation'=> $configuration_model['candidate_designation'],
                        ]; 
                        $exam_date_details = DeSailors::getLastRollExamDateByDesignationCenter(
                            batchId: $sailor['batch_id'],
                            canInfo: $canInfo,                        
                        );  

                        $exam_date_id  = $exam_date_details['exam_date_id'] ?? '';  
                        $max_candidate_in_exam_day=0;  
                                                
                        // get Active exam dates 
                        $examDays = SailorBatchConfigurationExamDate::getListByConfigurationId(batch_configuration_id:$configuration_model['id']);                                                                 
                        if($exam_date_id){                           
                            $firstIndex =  SailorBatchConfigurationExamDate::getNextKeyValue(key:$exam_date_id, dates:$examDays);
                            $exam_date_id = $firstIndex['id'] ?? '';
                            $exam_date =  $firstIndex['exam_date'];
                            $max_candidate_in_exam_day = $firstIndex['max_candidate_this_date'];
                        }else {                         
                            $firstIndex = reset($examDays); # frist index of array 
                            $exam_date_id = $firstIndex['id'] ?? '';
                            $exam_date =  $firstIndex['exam_date'];
                            $max_candidate_in_exam_day = $firstIndex['max_candidate_this_date'];
                        }
                        // if max candidate check is yes then get exam date from batch configuration
                        if($configuration_model['check_max_candidate'] == Constants::YES   ){                        
                            if($max_candidate_in_exam_day >0)
                            {
                                $count = DeSailors::getTotalCandidateByExamDate(
                                    batch_id:$sailor['batch_id'],
                                    exam_date_id:$exam_date_id,
                                    exam_date:$exam_date                        
                                   );                                     
                                
                                if ($count>0 && $count>=$max_candidate_in_exam_day){  
                                    // echo "exam_date ".$exam_date." max_candidate_in_exam_day ".$max_candidate_in_exam_day ." count ".$count;
                                                        
                                    $getNextDate = SailorBatchConfigurationExamDate::getNextAvailableExamDate(
                                        examDates:$examDays,
                                        checkDate:$exam_date,
                                        candidateCount:$count,
                                    );                                     
                                    
                                    if (!$getNextDate){
                                        Yii::$app->session->setFlash('application_close', "Configuration missing please contact with support");
                                        return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                                    }
                                    $exam_date = $getNextDate['exam_date'];
                                    $exam_date_id = $getNextDate['id'];
                                }
                            }else {
                                Yii::$app->session->setFlash('application_close', "Configuration missing please contact with support");
                                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));  
                            }                            
                        }  
                            

                        // $sailor->exam_date = $configuration_model['exam_date'];
                        $sailor->exam_date = $exam_date ?? $configuration_model['exam_date'];
                        $sailor->exam_date_id = $exam_date_id;

                        $sailor->exam_group = $configuration_model['exam_group'];
                        $sailor->batch_config_id = $configuration_model['id'];
                        $sailor->serial_no =  strval($roll_no);
                        $sailor->serial_generate_date =  date('Y-m-d');
                        $sailor->phase = Constants::SAILOR_PHASE_FIVE; // finally submit form           
                        

                        if ($sailor->save(false)) {


                            // create log in cloud
                            $logEntry = $sailor->toArray();

                            // Data Decrypt for view
                            foreach ($sailor->encryption_fields_for_personal_info as $key => $field)
                                $logEntry[$field] = $logEntry[$field] ? DataEncryption::dataDecrypt($logEntry[$field]) : '';


                            $batch_name = 'batch_not_found!';
                            if ($batch_setting_info && !empty($batch_setting_info['name_en']))
                                $batch_name = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $batch_setting_info['name_en']), '_'));
                            Yii::$app->r2Storage->upsertCandidateLog(model: $logEntry, logFile: $batch_name . '.ndjson');
                            // create log in cloud


                            // generate log after complete application 
                            DeSailors::generateLog($sailor->id);

                            return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/download", 'slug' => $slug]));
                        } else {
                            echo var_dump($sailor->serial_no);
                            echo '<pre>';
                            print_r($sailor->getErrors());
                            echo '</pre>';
                            die();
                        }
                    }
                } else {
                    /// no need now  
                }
            } else {
                throw new BadRequestHttpException('This action only accepts POST requests.');
            }
        }
    }



    /**
     * Congratulation 
     */
    public function actionDownload($slug = null)
    {
        $sailor = $this->findModel($slug);
        if ($sailor) {
            // Pdf library 
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 2,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_bottom' => 0,
                'mirrorMargins' => true,
                // 'format' => [190, 236],
                //'orientation' => 'L'
            ]);
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->debug = true;
            // $mpdf->curlAllowUnsafeSslRequests = false;
            // $mpdf->showImageErrors = false;
            // $mpdf->debug = false;

            $diploma_trade_course = '';
            if ($sailor->diploma_trade_course) {
                $data = Subjects::subjectFindById($sailor->diploma_trade_course);
                if ($data)  $diploma_trade_course = $data['name_en'];
            }

            // Data Decrypt for view
            foreach ($sailor->encryption_fields_for_personal_info as $key => $field)
                $sailor->$field = $sailor->$field ? DataEncryption::dataDecrypt($sailor->$field) : '';

            if ($sailor->current_thana)
                $sailor->current_thana = Upozilas::upazilaNameById($sailor->current_thana);

            if ($sailor->permanent_thana)
                $sailor->permanent_thana = Upozilas::upazilaNameById($sailor->permanent_thana);

            $mpdf->WriteHTML($this->renderPartial('candidate/application_form_pdf', ['model' => $sailor, 'diploma_trade_course' => $diploma_trade_course], true, false));
            $mpdf->Output(str_replace(' ', '_', $sailor->name) . '(' . $sailor->serial_no . ')' . '.pdf', 'I');
            exit();
        }
    }
    /**
     * Congratulation 
     */
    public function actionDownloadForm($slug = null)
    {

        $sailor =  DeSailors::find()
            ->where(['id' => StaticMethod::decryptPk($slug)])
            ->one();
        if ($sailor) {
            $diploma_trade_course = '';
            if ($sailor->diploma_trade_course) {
                $data = Subjects::subjectFindById($sailor->diploma_trade_course);
                if ($data)  $diploma_trade_course = $data['name_en'];
            }

            // Data Decrypt for view
            foreach ($sailor->encryption_fields_for_personal_info as $key => $field)
                $sailor->$field = $sailor->$field ? DataEncryption::dataDecrypt($sailor->$field) : '';

            if ($sailor->current_thana)
                $sailor->current_thana = Upozilas::upazilaNameById($sailor->current_thana);
            if ($sailor->permanent_thana)
                $sailor->permanent_thana = Upozilas::upazilaNameById($sailor->permanent_thana);

            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 2,
                'margin_left' => 8,
                'margin_right' => 8,
                'margin_bottom' => 0,
                'mirrorMargins' => true,
                // 'format' => [190, 236],
                //'orientation' => 'L'
            ]);
            $mpdf->curlAllowUnsafeSslRequests = true;
            $mpdf->debug = true;
            // $mpdf->curlAllowUnsafeSslRequests = false;
            // $mpdf->showImageErrors = false;
            // $mpdf->debug = false;
            // $mpdf->Image('https://www.joinnavysailor.org/media/main_logo.png', 0, 0, 210, 297, 'png', '', true, false);
            $mpdf->WriteHTML($this->renderPartial('candidate/application_form_pdf', ['model' => $sailor, 'diploma_trade_course' => $diploma_trade_course], true, false));
            $mpdf->Output(str_replace(' ', '_', $sailor->name) . '(' . $sailor->serial_no . ')' . '.pdf', 'I');
            //$mpdf->Output();
            exit();

            // Pdf library 


        }
    }

    public function actionVerifyCandidate($slug = null)
    {
        $sailor =  DeSailors::find()
            ->where(['id' => StaticMethod::decryptPk($slug)])
            ->one();
        if ($sailor) {
            $diploma_trade_course = '';
            if ($sailor->diploma_trade_course) {
                $data = Subjects::subjectFindById($sailor->diploma_trade_course);
                if ($data)  $diploma_trade_course = $data['name_en'];
            }

            // Data Decrypt for view
            foreach ($sailor->encryption_fields_for_personal_info as $key => $field)
                $sailor->$field = $sailor->$field ? DataEncryption::dataDecrypt($sailor->$field) : '';

            if ($sailor->current_thana)
                $sailor->current_thana = Upozilas::upazilaNameById($sailor->current_thana);

            if ($sailor->permanent_thana)
                $sailor->permanent_thana = Upozilas::upazilaNameById($sailor->permanent_thana);

            return $this->render(
                'application_verify_preview',
                [
                    'model' => $sailor,
                    'diploma_trade_course' => $diploma_trade_course
                ]
            );

            // Pdf library 
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'margin_top' => 2,
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
            $mpdf->WriteHTML($this->renderPartial('candidate/application_verification_pdf', ['model' => $sailor], true, false));
            $mpdf->Output(str_replace(' ', '_', $sailor->name) . '(' . $sailor->serial_no . ')' . '.pdf', 'I');
            exit();
        }
    }





    /**
     * Finds the Sailors model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return DeSailors the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        $model = DeSailors::find()
            ->where(['id' => StaticMethod::decryptPk($id)])
            ->andWhere(['created_by' => Yii::$app->user->identity->id])
            ->one();
        // if (($model = Sailors::findOne(['id' => StaticMethod::decryptPk($id)])) !== null) {
        if ($model !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
