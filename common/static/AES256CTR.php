<?php

namespace common\static;

use Yii;

class AES256CTR
{

    public static function getKey()
    {
        $ciphering = "AES-256-CTR";
        $iv = '1234567891011121';
        $key = "sTer85jkfhjhafjhafsReqwrtetfdggdfgt8158sfdfs";
        return [
            'ciphering' => $ciphering,
            'iv_length' => openssl_cipher_iv_length($ciphering),
            'encdec_iv' => $iv,
            'key' => $key,
            'options' => 0,
        ];
    }

    public static function dataEncrypt($data = null)
    {
        if (empty($data)) return '';
        $enc_keys = self::getKey();
        $encryption = openssl_encrypt(
            $data,
            $enc_keys['ciphering'],
            $enc_keys['key'],
            $enc_keys['options'],
            $enc_keys['encdec_iv']
        );
        return  $encryption;
    }

    public static function dataDecrypt($data = null)
    {
        if (empty($data)) return '';
        $enc_keys = self::getKey();
        $encryption = openssl_decrypt(
            $data,
            $enc_keys['ciphering'],
            $enc_keys['key'],
            $enc_keys['options'],
            $enc_keys['encdec_iv']
        );
        return  $encryption;
    }
}
