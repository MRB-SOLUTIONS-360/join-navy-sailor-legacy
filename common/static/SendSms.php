<?php

namespace common\static;

use common\models\SendSms as ModelsSendSms;

class SendSms
{
    
    const SMS_API_URL_BOOM_CAST = "http://api.boom-cast.com/boomcast/WebFramework/boomCastWebService/externalApiSendTextMessage.php?masking=JOIN NAVY&userName=Unlocklive&password=&MsgType=TEXT&receiver=Number&message=Your Message";

    // send sms here
    public static function sendSms($mobile, string $smsBody, string $application_type, string $serial_no)
    {
        $mobile = preg_replace("/[^0-9]/", "", $mobile);
        if (substr($mobile, 0, 2) != '88') $mobile = '88' . $mobile;
        $url = self::SMS_API_URL_BOOM_CAST . '&receiver=' . $mobile . '&message=' . urlencode($smsBody);
        $ch = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,   // return web page
            CURLOPT_HEADER         => false,  // don't return headers
            CURLOPT_FOLLOWLOCATION => true,   // follow redirects
            CURLOPT_SSL_VERIFYPEER => false,   // ssl
            CURLOPT_MAXREDIRS      => 10,     // stop after 10 redirects
            CURLOPT_AUTOREFERER    => true,   // set referrer on redirect
            CURLOPT_CONNECTTIMEOUT => 120,    // time-out on connect
            CURLOPT_TIMEOUT        => 120,    // time-out on response
        );
        curl_setopt_array($ch, $options);
        curl_exec($ch);
        if (curl_errno($ch)) {
            echo '<pre>';
            print_r(curl_error($ch));
            exit();
        } else // no error
        {
            // save to model
            $model = new ModelsSendSms();
            $model->application_type = $application_type;
            $model->serial_no = $serial_no;
            $model->phone_no = $mobile;
            $model->sms_body = $smsBody;
            if ($model->save(false)) return '_success';
            else return '_failure';
        }
        curl_close($ch);
    }
}
