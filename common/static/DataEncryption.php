<?php

namespace common\static;

use Yii;

class DataEncryption
{


    const PREFIX = 'ENC:';
    public static function getKey()
    {
        // $key = Yii::$app->security->generateRandomString(32);
        $key = 'KsfPndU9ciNhJG8r3QLb1q5Oocxo2I8S';
        return $key;
    }

    public static function dataEncrypt($data = null)
    {
        if (empty($data)) return $data;
        else {
            return self::PREFIX . Yii::$app->security->encryptByKey($data, self::getKey());
        }
    }

    public static function dataDecrypt($data = null)
    {
        if (empty($data)) return $data;
        else {
            if (strpos($data, 'ENC:') === 0) {
                return Yii::$app->security->decryptByKey(substr($data, 4), self::getKey());
            } else return $data;
        }
    }
}
