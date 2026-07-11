<?php

namespace common\models\payment;

use common\models\Sailors;

/**
 * SSLPayment Gateway
 */
class SSLPayment
{
    const PAYMENT_VALID = 'VALID';
    const PAYMENT_VALIDATED = 'VALIDATED';
    const STORE_AMOUNT = 275;

    // Sandbox 
    const SANDBOX_STORE_ID = 'unloc67b319d54a54e';
    const SANDBOX_PASSWORD = 'unloc67b319d54a54e@ssl';
    const SANDBOX_URL = 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php';
    const SANDBOX_PAYMENT_CHECK = 'https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php';
    // LIVE 
    const LIVE_STORE_ID = 'joinnavynavymilbd0live';
    const LIVE_PASSWORD = '67D29D06BA5D147902';
    const LIVE_URL = 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
    const LIVE_PAYMENT_CHECK = 'https://securepay.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php';


    const HOST = 'https://www.joinnavysailor.org';
    // const HOST = 'http://joinnavysailor.local';
    // const HOST = 'http://localhost/NAVY/joinnavy-sailor-V2';
    //CONST HOST = "https://joinnavy.navy.mil.bd/application";

    const SUCCESS_URL_SAILOR = self::HOST . '/online-payment/ssl-success';
    const CANCEL_URL_SAILOR = self::HOST . '/online-payment/ssl-cancel';
    const FAIL_URL_SAILOR = self::HOST . '/online-payment/ssl-fail';


    public static function requestInit($dataArray = array())
    {

        // live access 
        $store_id = self::LIVE_STORE_ID;
        $store_password = self::LIVE_PASSWORD;

        if($dataArray['opt_a'] == 'de_sailor' && $dataArray['payment_type'] != 'sandbox'){ 
            $store_id = self::LIVE_STORE_ID;
            $store_password = self::LIVE_PASSWORD;    
        }

        $store_url = self::LIVE_URL;

        if ($dataArray['payment_type'] == 'sandbox') {
            $store_id = self::SANDBOX_STORE_ID;
            $store_password = self::SANDBOX_PASSWORD;
            $store_url = self::SANDBOX_URL;
        }

        $success_url = self::SUCCESS_URL_SAILOR;
        $cancel_url = self::CANCEL_URL_SAILOR;
        $fail_url = self::FAIL_URL_SAILOR;      

        $data =  [
            'store_id' => $store_id, #Mandatory 
            'store_passwd' => $store_password, #Mandatory 
            'tran_id' =>  $dataArray['tran_id'], # Mandatory Unique transaction ID to identify your order in both your end and SSLCOMMERZ
            'success_url' =>  $success_url, # Mandatory
            'fail_url' => $fail_url, # Mandatory
            'cancel_url' => $cancel_url, # Mandatory
            'ipn_url' => '', # Not Mandatory
            'total_amount' => $dataArray['amount'], # Mandatory
            'currency' => 'BDT', # Mandatory
            'emi_option' => 0,
            'cus_name' => $dataArray['cus_name'],
            'cus_email' => $dataArray['cus_email'] ?? 'defaultemail@register.com',
            "cus_add1" => $dataArray['cus_add1'],
            "cus_add2" => "",
            'cus_phone' => $dataArray['cus_phone'],
            "cus_city" => $dataArray['cus_city'],
            "cus_state" => $dataArray['cus_state'],
            "cus_postcode" => "1206",
            "cus_country" => $dataArray['cus_country'],
            'shipping_method' => 'NO', # Mandatory
            'num_of_item' => '1', # Mandatory
            'weight_of_items' => '0.00', # Mandatory
            'logistic_pickup_id' => $dataArray['tran_id'], # Mandatory
            'logistic_delivery_type' => 'no', # Mandatory
            'product_name' => 'Join NAVY Sailor Payment', # Mandatory
            'product_category' => 'Join NAVY', # Mandatory
            'product_profile' => 'general', # Mandatory
            "value_a" => $dataArray['opt_a'] ?? '',
            "value_b" => $dataArray['opt_b'] ?? '',
            "value_c" => $dataArray['opt_c'] ?? '',
            "value_d" => $dataArray['opt_d'] ?? '',
        ];
        // Initialize cURL session
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $store_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $response = curl_exec($ch);
        curl_close($ch);
        // return data 
        $return = [
            'success' => false,
            'url' => '',
        ];
        if ($response === false) {
            throw new \Exception(curl_error($ch));
        } else {
            $json_decode  = json_decode($response, true);      

            if (isset($json_decode['status'], $json_decode['GatewayPageURL']) && strtolower($json_decode['status']) === 'success' && !empty($json_decode['GatewayPageURL'])) {
                $return = [
                    'success' => true,
                    'url' => $json_decode['GatewayPageURL'],
                ];
                // save request train & request data 
                if ($dataArray['opt_a'] == 'sailor') {
                    $opt_b =  explode('#', $dataArray['opt_b']);
                    $row_id  = '';
                    if (count($opt_b) == 2)  $row_id = str_replace('r_', '', $opt_b[0]);
                    if ($row_id) {
                        $sailor = Sailors::find()
                            ->select(['id', 'all_requested_tran_id', 'all_payment_response', 'all_payment_request'])
                            ->where(['id' =>  $row_id])->one();
                        $all_requested_tran_id = [];
                        if ($sailor['all_requested_tran_id'])
                            $all_requested_tran_id = json_decode($sailor['all_requested_tran_id'], true);
                        $all_requested_tran_id[] = $dataArray['tran_id'];

                        $all_payment_request = [];
                        if ($sailor['all_payment_request'])
                            $all_payment_request = json_decode($sailor['all_payment_request'], true);
                        $all_payment_request[] = [
                            'time' => date('d-m-Y H:i:s'),
                            'gatewayPageURL' => $json_decode['GatewayPageURL'],
                            'data' => $data
                        ];
                        $sailor->all_requested_tran_id = json_encode($all_requested_tran_id, true);
                        $sailor->all_payment_request = json_encode($all_payment_request, true);
                        $sailor->save(false);
                    }
                }
            }
        }
        return $return;
    }



    public static function allRequestListByTranIds($app_type, $payment_mode = 'sandbox', $tranIds = array())
    {

        $store_id = self::LIVE_STORE_ID;
        $store_password = self::LIVE_PASSWORD;
        if($app_type== 'de_sailor' &&  $payment_mode != 'sandbox'){ 
            $store_id = self::LIVE_STORE_ID;
            $store_password = self::LIVE_PASSWORD;        
        }

        $url = self::LIVE_PAYMENT_CHECK;

        if ($payment_mode == 'sandbox') {
            $store_id = self::SANDBOX_STORE_ID;
            $store_password = self::SANDBOX_PASSWORD;
            $url = self::SANDBOX_PAYMENT_CHECK;
        }        
        $all_response = [];
        $first_paid_tran_id = '';
        $details_paid_tran_id = [];
        $paid_tran_ids = [];
        $ch = curl_init();
        foreach ($tranIds as $key => $tranId) {
            $curlUrl = $url . '?tran_id=' . $tranId . '&store_id=' . $store_id . '&store_passwd=' . $store_password . '';
            // Set cURL options
            curl_setopt($ch, CURLOPT_URL, $curlUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            // Execute the cURL request
            $response = curl_exec($ch);
            if (!curl_errno($ch)) {
                $decode = json_decode($response, true);
                if ($decode['APIConnect'] == 'DONE') {               
                    $responseDataList = $decode['element']?? [];
                    foreach( $responseDataList as $k=> $responseData){
                        $all_response[] = $responseData;
                        if ($responseData && isset($responseData['val_id']) && $responseData['val_id'] != '' && strtolower($responseData['status']) == strtolower(self::PAYMENT_VALIDATED)) {
                            $paid_tran_ids[] = $responseData['tran_id'];
                            if ($first_paid_tran_id == '') {
                                $first_paid_tran_id = $responseData['tran_id'];
                                $details_paid_tran_id = $responseData;
                            }
                        }
                    }                    
                }
            }
        }
        // Close the cURL session
        curl_close($ch);
        return [
            'first_paid_tran_id' => $first_paid_tran_id,
            'details_paid_tran_id' => $details_paid_tran_id,
            'paid_tran_ids' => $paid_tran_ids,
        ];
    }
}
