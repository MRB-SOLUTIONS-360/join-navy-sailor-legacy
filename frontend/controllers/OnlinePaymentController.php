<?php

namespace frontend\controllers;

use common\models\DeSailors;
use common\models\payment\AamarPay;
use common\models\payment\ShurjoPayment;
use common\models\payment\SSLPayment;
use common\models\Sailors;
use common\models\User;
use common\static\Constants;
use common\static\StaticMethod;
use Yii;

class OnlinePaymentController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        $csrfFalse = [
            // 'payment-response-sailor',
            'ssl-success',
            'ssl-cancel',
            'ssl-fail',
        ];
        if (in_array($action->id, $csrfFalse)) {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }

    // SSL Payment
    public function actionPaymentSsl()
    {
        try {
            // Check if the session has the 'payment_info' key
            if (!Yii::$app->session->has('payment_info'))
                throw new \Exception("Some things went wrong. Please login again and try.");

            $payment_info = Yii::$app->session->get('payment_info');

             
            $response = SSLPayment::requestInit(dataArray: $payment_info);

            if ($response['success'] && $response['url']) {
                header("Location: " . $response['url']);
                exit;
            } else
                throw new \Exception("Could not retrieve the payment gateway URL. Please try after some time or contact with support.");
        } catch (\Exception $e) {
            // Set the flash message
            Yii::$app->session->setFlash('error', "An error occurred: " . $e->getMessage());
            return $this->redirect(Yii::$app->request->referrer ?: Yii::$app->homeUrl);
        }
    }

    // ssl-success
    public function actionSslSuccess()
    {
        try {
            $request = $_REQUEST;

            
            if ($request &&  isset($request['status']) &&  strtolower($request['status']) == strtolower(SSLPayment::PAYMENT_VALID)) {

                $opt_b =  explode('#', $request['value_b']);
                $user_id  = '';
                $row_id  = '';
                if (count($opt_b) == 2) {
                    $row_id = str_replace('r_', '', $opt_b[0]);
                    $user_id = str_replace('u_', '', $opt_b[1]);
                }

                //// login by user id 
                $identity = User::findOne(['id' => $user_id]);

                
                Yii::$app->user->login($identity);


                if ($user_id == '' || $row_id == '')
                    throw new \Exception("Some things went wrong. Please try after some time or contact with support.");

                if ($request['value_a'] == 'sailor') {
                    $sailor = Sailors::find()
                        ->select(['id', 'eligibility_info_id', 'candidate_type', 'app_unique_id', 'phase', 'validation_id', 'all_payment_response', 'amount', 'store_amount', 'card_type', 'card_no', 'trans_date', 'payment_api', 'payment_status', 'created_by', 'updated_by', 'updated_dt'])
                        ->where(['id' =>  $row_id])->one();
                } else if ($request['value_a'] == 'de_sailor') {
                    $sailor = DeSailors::find()
                        ->select(['id', 'eligibility_info_id', 'candidate_type', 'app_unique_id', 'phase', 'validation_id', 'all_payment_response', 'amount', 'store_amount', 'card_type', 'card_no', 'trans_date', 'payment_api', 'payment_status', 'created_by', 'updated_by', 'updated_dt'])
                        ->where(['id' =>  $row_id])->one();
                }else
                    throw new \Exception("We only allow sailor payment now.");

                $sailor->card_type = $request['card_type'] ?? '';
                $sailor->trans_date = $request['tran_date'];
                $sailor->ref_id = $request['val_id'];
                $sailor->card_no = $request['card_brand'] ?? $request['bank_tran_id'];
                $sailor->payment_api = json_encode($request, 128);
                $sailor->payment_status = Constants::PAYMENT_PAID;
                $sailor->phase = Constants::SAILOR_PHASE_TWO;
                $sailor->amount = $request['amount'];
                $sailor->store_amount = SSLPayment::STORE_AMOUNT;

                $all_payment_response = [];
                if ($sailor['all_payment_response'])
                    $all_payment_response = json_decode($sailor['all_payment_response'], true);
                $all_payment_response[] = $request;

                $sailor->all_payment_response = json_encode($all_payment_response, true);

                if ($sailor->save()) {
                    if ($request['value_a'] == 'sailor')
                        return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                    else if ($request['value_a'] == 'de_sailor')
                        return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                } else
                    throw new \Exception("We only allow sailor payment now.");
            }
        } catch (\Exception $e) {
            Yii::$app->session->setFlash('application_close', "An error occurred: " . $e->getMessage());
            return $this->redirect(Yii::$app->urlManager->createUrl(["my-application"]));
        }
    }


    public function actionSslCancel()
    {
        try {
            $request = $_REQUEST;
            $opt_b =  explode('#', $request['value_b']);
            $user_id  = '';          
            if (count($opt_b) == 2) {               
                $user_id = str_replace('u_', '', $opt_b[1]);
            }
            //// login by user id 
            $identity = User::findOne(['id' => $user_id]);
            Yii::$app->user->login($identity);
            throw new \Exception("Payment failed.");

        } catch (\Exception $e) {
            Yii::$app->session->setFlash('application_close', "An error occurred: " . $e->getMessage());
            return $this->redirect(Yii::$app->urlManager->createUrl(["my-application"]));
        }
    }
    public function actionSslFail()
    {
        try {
            $request = $_REQUEST;
            $opt_b =  explode('#', $request['value_b']);
            $user_id  = '';          
            if (count($opt_b) == 2) {               
                $user_id = str_replace('u_', '', $opt_b[1]);
            }
            //// login by user id 
            $identity = User::findOne(['id' => $user_id]);
            Yii::$app->user->login($identity);
            throw new \Exception("Payment failed.");

        } catch (\Exception $e) {
            Yii::$app->session->setFlash('application_close', "An error occurred: " . $e->getMessage());
            return $this->redirect(Yii::$app->urlManager->createUrl(["my-application"]));
        }
    }


    // SSL payment 



    // // AamarPay
    public function actionPayment()
    {
        if (Yii::$app->session->has('payment_info')) {
            $payment_info = Yii::$app->session->get('payment_info');
            $response = AamarPay::requestInit(dataArray: $payment_info);
            if ($response && $response['result']) {
                header("Location: " . $response['payment_url'] . "");
                exit();
            } // $req = AamarPay::paymentVerify(validationId:'231127121629426317');      
        }
    }

    // de sailor 
    public function actionPaymentResponseDeSailor()
    {
        $request = $_REQUEST;
        if ($request && array_key_exists('status_code', $request) && $request['status_code'] == AamarPay::AAMAR_PAY_SUCCESS) {
            //// login by user id 
            $identity = User::findOne(['id' =>  $request['opt_d']]);
            Yii::$app->user->login($identity);


            if ($request['opt_a'] == 'de_sailor') {
                $sailor = DeSailors::find()
                    ->select(['id', 'eligibility_info_id', 'app_unique_id', 'candidate_type', 'phase', 'validation_id', 'amount', 'store_amount', 'card_type', 'card_no', 'trans_date', 'payment_api', 'payment_status', 'created_by', 'updated_by', 'updated_dt'])
                    ->where(['id' => $request['opt_b']])->one();
            }

            $sailor->card_type = $request['card_type'] ?? '';
            $sailor->trans_date = $request['pay_time'];
            $sailor->ref_id = $request['pg_txnid'];
            $sailor->card_no = $request['epw_txnid'];
            $sailor->payment_api = json_encode($request, 128);
            $sailor->payment_status = Constants::PAYMENT_PAID;
            $sailor->phase = Constants::SAILOR_PHASE_TWO;
            $sailor->amount = $request['amount_original'];
            $sailor->store_amount = ShurjoPayment::STORE_AMOUNT;
            if ($sailor->save()) {
                // if ($request['opt_a'] == 'sailor')
                return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                // else if ($request['opt_a'] == 'de_sailor')
            }
            echo '<pre>';
            print_r($sailor->getErrors());
            echo '</pre>';
        }
    }





    /**
     * Payment response sailor
     */



    public function actionPaymentResponseSailor()
    {
        $request = $_REQUEST;
        if ($request &&  $request['status_code'] == AamarPay::AAMAR_PAY_SUCCESS) {



            $opt_b =  explode('#', $request['opt_b']);
            $user_id  = '';
            $row_id  = '';
            if (count($opt_b) == 2) {
                $row_id = str_replace('r_', '', $opt_b[0]);
                $user_id = str_replace('u_', '', $opt_b[1]);
            }

            if ($user_id && $row_id) {
                // login by user id 
                $identity = User::findOne(['id' => $user_id]);
                Yii::$app->user->login($identity);

                if ($request['opt_a'] == 'sailor') {
                    $sailor = Sailors::find()
                        ->select(['id', 'eligibility_info_id', 'candidate_type', 'app_unique_id', 'phase', 'validation_id', 'amount', 'store_amount', 'card_type', 'card_no', 'trans_date', 'payment_api', 'payment_status', 'created_by', 'updated_by', 'updated_dt'])
                        ->where(['id' =>  $row_id])->one();
                } else if ($request['opt_a'] == 'de_sailor') {
                    $sailor = DeSailors::find()
                        ->select(['id', 'eligibility_info_id', 'app_unique_id', 'candidate_type', 'phase', 'validation_id', 'amount', 'store_amount', 'card_type', 'card_no', 'trans_date', 'payment_api', 'payment_status', 'created_by', 'updated_by', 'updated_dt'])
                        ->where(['id' => $row_id])->one();
                }

                $sailor->card_type = $request['card_type'] ?? '';
                $sailor->trans_date = $request['pay_time'];
                $sailor->ref_id = $request['pg_txnid'];
                $sailor->card_no = $request['epw_txnid'];
                $sailor->payment_api = json_encode($request, 128);
                $sailor->payment_status = Constants::PAYMENT_PAID;
                $sailor->phase = Constants::SAILOR_PHASE_TWO;
                $sailor->amount = $request['amount_original'];
                $sailor->store_amount = ShurjoPayment::STORE_AMOUNT;

                if ($sailor->save()) {
                    if ($request['opt_a'] == 'sailor')
                        return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                    else if ($request['opt_a'] == 'de_sailor')
                        return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                }
                return $this->redirect(Yii::$app->urlManager->createUrl(["my-application"]));
            }
        }
        return $this->redirect(Yii::$app->urlManager->createUrl(["my-application"]));
        // verify token 
        // http://sandbox.aamarpay.com/api/v1/trxcheck/request.php?request_id=238uyasfdsanjsfs234sdfpdsds9075&store_id=aamarpaytest&signature_key=dbb74894e82415a2f7ff0ec3a97e4183&type=json


    }
}
