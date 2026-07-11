<?php

namespace frontend\controllers;

use common\models\DeSailors;
use common\models\payment\AamarPay;
use common\models\payment\ShurjoPayment;
use common\models\Sailors;
use common\models\User;
use common\static\Constants;
use common\static\StaticMethod;
use Yii;

class OnlinePaymentController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        if ($action->id == 'payment-response-sailor') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }


    // public function actionPayment()
    // {

    //     if (Yii::$app->session->has('payment_info')) {            
    //         $payment_info = Yii::$app->session->get('payment_info');

    //         if ($payment_info['value1'] == 'sailor') {
    //             $payment_type =  $payment_info['payment_type'] ?? 'live';
    //             $app_type = $payment_info['value1'] ?? 'sailor'; // sailor or de sailor 
    //             $spg_get_token = ShurjoPayment::getToken(app_type: $app_type, payment_mode: $payment_type);
    //             // check token is exist 
    //             if ($spg_get_token && array_key_exists('token', $spg_get_token) && !empty($spg_get_token['token'])) {
    //                 $_SESSION['token'] = $spg_get_token['token'];
    //                 $_SESSION['store_id'] = $spg_get_token['store_id'];

    //                 /// after payment redirect url base on application type 
    //                 if ($app_type == 'sailor') {
    //                     $returnUrl = ShurjoPayment::RETURN_URL_SAILOR;
    //                     $cancelUrl = ShurjoPayment::CANCEL_URL_SAILOR;
    //                     if ($payment_type == 'live')
    //                         $store_code = ShurjoPayment::STORE_CODE_SAILOR;
    //                     else
    //                         $store_code = ShurjoPayment::STORE_CODE_SAILOR_SANDBOX;
    //                 } else {
    //                     $returnUrl = ShurjoPayment::RETURN_URL_DE_SAILOR;
    //                     $cancelUrl = ShurjoPayment::CANCEL_URL_DE_SAILOR;
    //                     if ($payment_type == 'live')
    //                         $store_code = ShurjoPayment::STORE_CODE_SAILORDEO;
    //                     else
    //                         $store_code = ShurjoPayment::STORE_CODE_SAILORDEO_SANDBOX;
    //                 }

    //                 /// payment gateway required fields 
    //                 $create_payment = array(
    //                     'token' => $spg_get_token['token'],
    //                     'store_id' => $spg_get_token['store_id'],
    //                     'prefix' => $store_code, //'JNB',
    //                     'currency' => 'BDT',
    //                     'return_url' => $returnUrl, // call after payment success
    //                     'cancel_url' => $cancelUrl, // if cancel payment 
    //                     'amount' => $payment_info['amount'], // anount 225, 750
    //                     'order_id' => $payment_info['order_id'],  // requirend & unique
    //                     'discsount_amount' => '1', // $payment_info['discsount_amount'], // 
    //                     'disc_percent' => '',
    //                     'client_ip' => $_SERVER['REMOTE_ADDR'],
    //                     'customer_name' => $payment_info['customer_name'],
    //                     'customer_phone' => $payment_info['customer_phone'],
    //                     'customer_email' => $payment_info['customer_email'],
    //                     'customer_address' => $payment_info['customer_address'],
    //                     'customer_city' => $payment_info['customer_city'],
    //                     'customer_state' => $payment_info['customer_state'],
    //                     'customer_postcode' => $payment_info['customer_postcode'],
    //                     'customer_country' =>  $payment_info['customer_country'],
    //                     'value1' => $payment_info['value1'],
    //                     'value2' =>  $payment_info['value2'],
    //                     'value3' =>  $payment_info['value3'],
    //                     'value4' =>  $payment_info['value4'],
    //                 );

    //                 /// 
    //                 $spg_secret_key_url  = ShurjoPayment::SPG_PAYMENT_SANDBOX;
    //                 if ($payment_type  == 'live')
    //                     $spg_secret_key_url  = ShurjoPayment::SPG_PAYMENT;
    //                 ////// redirect payment page 
    //                 $create_payment_response = ShurjoPayment::spgCurlPost($create_payment, $spg_secret_key_url);

    //                 if ($create_payment_response && array_key_exists('transactionStatus', $create_payment_response) && $create_payment_response['transactionStatus'] == ShurjoPayment::SP_INITIATED) {

    //                     if($app_type == 'sailor'){                            
    //                         $sModel = Sailors::findOne($payment_info['value2']);
    //                         $sModel->order_id_original =  $create_payment_response['sp_order_id'];
    //                         $sModel->save();
    //                     }

    //                     header("Location: " . $create_payment_response['checkout_url'] . "");
    //                     exit();
    //                 }

    //                 // echo  $returnUrl . '<br/>';
    //                 // echo  $store_code . '<br/>';
    //                 // echo '<pre>';
    //                 // print_r($create_payment_response);
    //                 // echo '</pre>';

    //             } else {
    //                 // redirect previous page 
    //             }
    //         }

    //         // echo '<pre>';
    //         // print_r($payment_info);
    //         // echo '</pre>';
    //         // die();
    //     }

    //     /// echo 'Online Payment';
    // }



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
                    ->select(['id', 'eligibility_info_id', 'app_unique_id','candidate_type' ,'phase', 'validation_id', 'amount', 'store_amount', 'card_type', 'card_no', 'trans_date', 'payment_api', 'payment_status', 'created_by', 'updated_by', 'updated_dt'])
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

    // public function actionPaymentResponseSailor()
    // {
    //     $request = $_REQUEST;

    //     if ($request && array_key_exists('order_id', $request)) {


    //         $order_id = $request['order_id'];

    //         $is_live_sand_box = 'live';
    //         $payment_verification  = ShurjoPayment::SPG_PAYMENT_VERIICATION;
    //         if (strpos($order_id, ShurjoPayment::STORE_CODE_SAILOR_SANDBOX) !== false) {
    //             $is_live_sand_box = 'sandbox';
    //             $payment_verification  = ShurjoPayment::SPG_PAYMENT_VERIICATION_SANDBOX;
    //         }

    //         $session  = ShurjoPayment::getToken(app_type: 'sailor', payment_mode: $is_live_sand_box);
    //         $user_credential = array(
    //             'order_id' => $order_id,
    //             'token' => $session['token'],
    //             'store_id' => $session['store_id']
    //         );
    //         $spg_payment_verification = ShurjoPayment::spgCurlPost($user_credential, $payment_verification);

    //         // can re-try using order_id            
    //         if (count($spg_payment_verification) == 1) {
    //             $spg_payment_verification = (array) $spg_payment_verification[0];

    //             if ($spg_payment_verification['sp_code'] == ShurjoPayment::SP_PAYMENT_SUCCESS && $spg_payment_verification['sp_message'] == ShurjoPayment::SP_PAYMENT_MESSAGE) {

    //                 //// login by user id 
    //                 $identity = User::findOne(['id' =>  $spg_payment_verification['value4']]);
    //                 Yii::$app->user->login($identity);

    //                 $sailor = Sailors::find()
    //                     ->select(['id', 'eligibility_info_id', 'app_unique_id', 'phase', 'validation_id', 'amount', 'store_amount', 'card_type', 'card_no', 'trans_date', 'payment_api', 'payment_status', 'created_by', 'updated_by', 'updated_dt'])
    //                     ->where(['id' => $spg_payment_verification['value2']])->one();



    //                 $sailor->card_type = $spg_payment_verification['method'] ?? '';
    //                 $sailor->trans_date = $spg_payment_verification['date_time'];
    //                 $sailor->ref_id = $spg_payment_verification['order_id'];
    //                 $sailor->card_no = $spg_payment_verification['bank_trx_id'];
    //                 $sailor->payment_api = json_encode($spg_payment_verification, 128);
    //                 $sailor->payment_status = Constants::PAYMENT_PAID;
    //                 $sailor->phase = Constants::SAILOR_PHASE_TWO;
    //                 $sailor->amount = $spg_payment_verification['amount'];
    //                 $sailor->store_amount = ShurjoPayment::STORE_AMOUNT;
    //                 /// $sailor->validation_id = $spg_payment_verification['order_id'];



    //                 if ($sailor->save()) {
    //                     return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
    //                 }

    //                 //Yii::$app->session->setFlash('error', "Sorry! Payment has been failed. Please try again.");
    //                 return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/my-application"]));
    //             }
    //         }

    //         /// Yii::$app->session->setFlash('error', "Sorry! Payment has been failed. Please try again.");
    //         return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/my-application"]));
    //     }
    //     echo '01';
    //     echo '<pre>';
    //     print_r($request);
    //     echo '</pre>';
    //     die();
    // }



    public function actionPaymentResponseSailor()
    {
        $request = $_REQUEST;
        echo '01';
        echo '<pre>';
        print_r($request);
        echo '</pre>';
        die();

        // verify token 
        // http://sandbox.aamarpay.com/api/v1/trxcheck/request.php?request_id=238uyasfdsanjsfs234sdfpdsds9075&store_id=aamarpaytest&signature_key=dbb74894e82415a2f7ff0ec3a97e4183&type=json

        if ($request && array_key_exists('order_id', $request)) {


            $order_id = $request['order_id'];

            $is_live_sand_box = 'live';
            $payment_verification  = ShurjoPayment::SPG_PAYMENT_VERIICATION;
            if (strpos($order_id, ShurjoPayment::STORE_CODE_SAILOR_SANDBOX) !== false) {
                $is_live_sand_box = 'sandbox';
                $payment_verification  = ShurjoPayment::SPG_PAYMENT_VERIICATION_SANDBOX;
            }

            $session  = ShurjoPayment::getToken(app_type: 'sailor', payment_mode: $is_live_sand_box);
            $user_credential = array(
                'order_id' => $order_id,
                'token' => $session['token'],
                'store_id' => $session['store_id']
            );
            $spg_payment_verification = ShurjoPayment::spgCurlPost($user_credential, $payment_verification);

            // can re-try using order_id            
            if (count($spg_payment_verification) == 1) {
                $spg_payment_verification = (array) $spg_payment_verification[0];

                if ($spg_payment_verification['sp_code'] == ShurjoPayment::SP_PAYMENT_SUCCESS && $spg_payment_verification['sp_message'] == ShurjoPayment::SP_PAYMENT_MESSAGE) {

                    //// login by user id 
                    $identity = User::findOne(['id' =>  $spg_payment_verification['value4']]);
                    Yii::$app->user->login($identity);

                    $sailor = Sailors::find()
                        ->select(['id', 'eligibility_info_id', 'app_unique_id', 'phase', 'validation_id', 'amount', 'store_amount', 'card_type', 'card_no', 'trans_date', 'payment_api', 'payment_status', 'created_by', 'updated_by', 'updated_dt'])
                        ->where(['id' => $spg_payment_verification['value2']])->one();



                    $sailor->card_type = $spg_payment_verification['method'] ?? '';
                    $sailor->trans_date = $spg_payment_verification['date_time'];
                    $sailor->ref_id = $spg_payment_verification['order_id'];
                    $sailor->card_no = $spg_payment_verification['bank_trx_id'];
                    $sailor->payment_api = json_encode($spg_payment_verification, 128);
                    $sailor->payment_status = Constants::PAYMENT_PAID;
                    $sailor->phase = Constants::SAILOR_PHASE_TWO;
                    $sailor->amount = $spg_payment_verification['amount'];
                    $sailor->store_amount = ShurjoPayment::STORE_AMOUNT;
                    /// $sailor->validation_id = $spg_payment_verification['order_id'];



                    if ($sailor->save()) {
                        return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/academic-info", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                    }

                    //Yii::$app->session->setFlash('error', "Sorry! Payment has been failed. Please try again.");
                    return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/my-application"]));
                }
            }

            /// Yii::$app->session->setFlash('error', "Sorry! Payment has been failed. Please try again.");
            return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/my-application"]));
        }
    }
}
