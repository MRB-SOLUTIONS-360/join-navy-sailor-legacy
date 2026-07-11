<?php

namespace common\static;

use Carbon\Carbon;
use common\static\Constants;

class StaticMethod
{
    // https://developer.wordpress.org/coding-standards/inline-documentation-standards/php/
    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function statusDropDown(int $status = null)
    {
        $array = [
            Constants::STATUS_ACTIVE => 'Active',
            Constants::STATUS_INACTIVE => 'Inactive',
        ];
        return self::returnValue($array, $status);
    }

    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function candidateType(int $status = null)
    {
        $array = [
            Constants::CANDIDATE_SAILOR => 'Sailor',
            Constants::CANDIDATE_DE_SAILOR => 'Direct Entry Artificer',
            Constants::CANDIDATE_DE_SAILOR_DOCKYARD => 'Direct Entry Dockyard',
            // Constants::CANDIDATE_DE_ARTIFICER => 'Direct Entry Artificer',
            // Constants::CANDIDATE_DE_DOCKYARD => 'Direct Entry Dockyard ',
        ];

        return self::returnValue($array, $status);
    }
    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function maritalStatus(int $status = null)
    {
        $array = [
            Constants::MARRIED => 'Married',
            Constants::UNMARRIED => 'Unmarried',
        ];

        return self::returnValue($array, $status);
    }
    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function team(int $status = null)
    {
        $array = [
            Constants::TEAM_A => 'Team A',
            Constants::TEAM_B => 'Team B',
            Constants::TEAM_C => 'Team C',
        ];

        return self::returnValue($array, $status);
    }
    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function gender(int $status = null)
    {
        $array = [
            Constants::GENDER_MALE => 'Male',
            Constants::GENDER_FEMALE => 'Female',
        ];

        return self::returnValue($array, $status);
    }
    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function yesNo(int $status = null)
    {
        $array = [
            Constants::YES => 'Yes',
            Constants::NO => 'No',
        ];
        return self::returnValue($array, $status);
    }
    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function yesNoForFreedom(int $status = null)
    {
        $array = [
            Constants::YES => 'Yes, Child of freedom fighter',
            Constants::YES_ETHNIC_MINORITY => 'Yes, Ethnic Minority',
            Constants::NO => 'No',
        ];
        return self::returnValue($array, $status);
    }

    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function isCanselApplication(int $status = null)
    {
        $array = [
            Constants::YES => 'No',
            Constants::NO => 'Yes',
        ];
        return self::returnValue($array, $status);
    }

    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function paymentStatus(int $status = null)
    {
        $array = [
            Constants::YES => 'Paid',
            Constants::NO => 'Unpaid',
        ];
        return self::returnValue($array, $status);
    }

    /**
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function navyUniformCivil(int $status = NULL)
    {
        $array = array(
            Constants::YES => 'Uniform',
            Constants::NO => 'Civilian'
        );
        return self::returnValue($array, $status);
    }


    /**
     * JSC / Academic Result Pass Fail
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function passFail(int $status = null)
    {
        $array = [
            Constants::YES => 'Pass',
            Constants::NO => 'Failed',
        ];
        return self::returnValue($array, $status);
    }


    /**
     * JSC / Academic Result Pass Fail
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function academicTypeHscDiploma(int $status = null)
    {
        $array = [
            Constants::YES => 'HSC',
            Constants::NO => 'Diploma',
        ];
        return self::returnValue($array, $status);
    }

    /**
     * Initializes the default button rendering callback for single button.
     * @param array $array its array 
     * @param int|null $status int or null
     * @return type Array or single Value .
     */

    public static function returnValue($array, $status = null)
    {
        if ($status && array_key_exists($status, $array))
            return $array[$status];
        else if ($status && !array_key_exists($status, $array))
            return '';
        return $array;
    }
    /**
     * SSC Academic group with vocational
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function academicGroupSsc(int $status = null)
    {
        $array = [
            Constants::AC_GROUP_SCIENCE => 'Science',
            Constants::AC_GROUP_BUSINESS => 'Business',
            Constants::AC_GROUP_HUMANITIES => 'Humanities',
            Constants::AC_GROUP_VOCATIONAL => 'Vocational',
            Constants::AC_GROUP_GENERAL => 'General(Madrasah)',
            Constants::AC_GROUP_MADRASHA_MUZABBID => 'Muzabbid(Madrasah)',
        ];

        return self::returnValue($array, $status);
    }

    /**
     * Candidate religion
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function academicReligion(int $status = null)
    {
        $array = [
            Constants::RELIGION_MUSLIM => 'Muslim',
            Constants::RELIGION_HINDU => 'Hindu',
            Constants::RELIGION_CHRISTIAN => 'Christian',
            Constants::RELIGION_BUDDHIST => 'Buddhist',
            Constants::RELIGION_OTHER => 'Other',
        ];

        return self::returnValue($array, $status);
    }

    /**
     * HSC Academic group 
     * @param type $status integer or null .
     * @return type Array or single Value .
     */
    public static function academicGroupHsc(int $status = null)
    {
        $array = [
            Constants::AC_GROUP_SCIENCE => 'Science',
            Constants::AC_GROUP_BUSINESS => 'Business',
            Constants::AC_GROUP_HUMANITIES => 'Humanities',
            Constants::AC_GROUP_VOCATIONAL => 'Vocational',
            Constants::AC_GROUP_GENERAL => 'General(Madrasah)',

        ];

        return self::returnValue($array, $status);
    }

    /**
     * get difference between two date by using Carbon.      
     * It accept two params ( maxDate ='2000-01-17' and minDate = '2023-0-01' and )
     * Note : Min Date must be grater then Max Date.
     * @return  date 15 years, 0 months, 15 days
     */
    public static function getDifferenceBetweenTwoDate(string $maxDate = null, string $minDate = null)
    {
        if ($maxDate && $minDate) {
            $age = Carbon::createFromDate($maxDate)
                ->diff($minDate)
                ->format('%y years, %m months,  %d days');
            return $age;
        }
        return '';
    }

    /**
     * get difference between two date by using Carbon.      
     * It accept two params ( maxDate ='2000-01-17' and minDate = '2023-0-01' and )
     * Note : Min Date must be grater then Max Date.
     * @return  date 15 years,01 months
     */
    public static function getDifferenceBetweenTwoDateYearMonth(string $maxDate = null, string $minDate = null)
    {
        if ($maxDate && $minDate) {

            // $d1 = '2024-01-01';  // minDate
            // $d2 = '1996-02-12';  // maxDate
            // $toDate = Carbon::parse($d2);
            // $fromDate = Carbon::parse($d1);
            // echo Carbon::createFromDate($toDate)->diff($fromDate)->format('%y years, %m months and %d days')

            $toDate = Carbon::parse($minDate);
            $fromDate = Carbon::parse($maxDate);
            $age = Carbon::createFromDate($toDate)
                ->diff($fromDate)
                ->format('%y.%M.%D');
            return $age;
        }
        return '';
    }

    /**
     * Division list of Bangladesh    
     * @return list or single division
     */
    public static function getDivisionList(int $id = null)
    {
        $array = [
            Constants::DIVISION_BARISHAL => 'Barishal',
            Constants::DIVISION_CHITTAGONG => 'Chattogram',
            Constants::DIVISION_DHAKA => 'Dhaka',
            Constants::DIVISION_KHULNA => 'Khulna',
            Constants::DIVISION_MYMENSINGH => 'Mymensingh',
            Constants::DIVISION_RAJSHAHI => 'Rajshahi',
            Constants::DIVISION_RANGPUR => 'Rangpur',
            Constants::DIVISION_SYLHET => 'Sylhet',
            Constants::DIVISION_SPECIAL => 'Special(Nou Scout / Navy)',
        ];
        return self::returnValue($array, $id);
    }

    /**
     * Roll from : From where roll will start. From Batch setting or Configuration Setting,
     * if Batch : Roll start from batch setting and skip roll setting from configuration else : Roll start from configuration setting  and skip from batch batch setting.
     * @return array or single 
     */
    public static function getRollFrom(int $id = null)
    {
        $array = [
            Constants::ROLL_FROM_BATCH => 'Batch',
            Constants::ROLL_FROM_CONFIG => 'Config',
        ];
        return self::returnValue($array, $id);
    }


    /**
     * Payment mode : 1=>Live Payment or 2=>Sandbox payment 
     * @return type Array or single Value .
     */
    public static function paymentMode(int $status = null)
    {
        $array = [
            Constants::YES => 'Live',
            Constants::NO => 'Sandbox',
        ];
        return self::returnValue($array, $status);
    }
    /**
     * Payment Amount for live and sendbox 
     * @return type Array or single Value .
     */
    public static function paymentAmount(int $status = null)
    {
        $array = [
            Constants::PAYMENT_AMOUNT_LIVE => Constants::PAYMENT_AMOUNT_LIVE,
            Constants::PAYMENT_AMOUNT_SANDBOX => Constants::PAYMENT_AMOUNT_SANDBOX,
        ];
        return self::returnValue($array, $status);
    }


    /**
     * Candidate type for eligibility check. 
     * @return type Array or single Value .
     */
    public static function candidateTypeForEligibilityCheck(int $status = null, string $is_sailor_deo = 'sailor')
    {
        $array = [
            Constants::ELIGIBILITY_CANDIDATE_TYPE_GENERAL => 'General Candidate',
            Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA => 'Navy Personnel Descendent',
            // Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA => 'Posso kota (Navy Only)',
            // Constants::ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL => 'Departmental Candidate',
        ];
        return self::returnValue($array, $status);
    }


    /**
     * Candidate type eye status 1=>6/6 or 2=>6/12. 
     * @return type Array or single Value .
     */
    public static function candidateEyeStatus(int $status = null)
    {
        $array = [
            Constants::YES => '6/6',
            // Constants::NO => '6/12'
        ];
        return self::returnValue($array, $status);
    }

    /**
     * Subject type 1=>Diploma, 2=>Trade. 
     * @return type Array or single Value .
     */
    public static function subjectType(int $status = null)
    {
        $array = [
            Constants::SUBJECT_TYPE_DIPLOMA => 'Diploma',
            Constants::SUBJECT_TYPE_TRADE => 'Trade'
        ];
        return self::returnValue($array, $status);
    }

    /**
     * Sailor Batch Configuration group 
     * Subject type 1=>Group A, 2=> Group B, 3=>Group C 
     * @return type Array or single Value .
     */
    public static function sailorGroup(int $status = null)
    {
        $array = [
            Constants::GROUP_A => 'Group A',
            Constants::GROUP_B => 'Group B',
            Constants::GROUP_C => 'Group C',
            // Constants::GROUP_D => 'Group D',
        ];
        return self::returnValue($array, $status);
    }

    /**
     * paymentType
     * Subject type online=>online, manual=>manual 
     * @return type Array or single Value .
     */
    public static function paymentType(int $status = null)
    {
        $array = [
            Constants::PAYMENT_TYPE_ONLINE => 'Online',
            //Constants::PAYMENT_TYPE_MANUAL => 'Manual'
        ];
        return self::returnValue($array, $status);
    }

    public static function paymentTypeAdmin(int $status = null)
    {
        $array = [
            Constants::PAYMENT_TYPE_ONLINE => 'Online',
            Constants::PAYMENT_TYPE_MANUAL => 'Manual'
        ];
        return self::returnValue($array, $status);
    }


    /**
     * Education Board List
     * @return type Array or single Value .
     */
    public static function educationBoard(string $status = null)
    {
        $array = [
            Constants::EDU_BOARD_BARISHAL => 'Barishal',
            Constants::EDU_BOARD_CTG => 'Chittagong',
            Constants::EDU_BOARD_COMILLA => 'Comilla',
            Constants::EDU_BOARD_DHAKA => 'Dhaka',
            Constants::EDU_BOARD_DINAJPUR => 'Dinajpur',
            Constants::EDU_BOARD_JESSORE => 'Jessore',
            Constants::EDU_BOARD_MYMENSINGH => 'Mymensingh',
            Constants::EDU_BOARD_RAJSHAHI => 'Rajshahi',
            Constants::EDU_BOARD_SYLHET => 'Sylhet',
            Constants::EDU_BOARD_MARRASHA => 'Madrasah',
            Constants::EDU_BOARD_TEC => 'Technical',
            // Constants::EDU_BOARD_DIBS => 'DIBS'
        ];
        return self::returnValue($array, $status);
    }

    /**
     * candidateMonitorBy
     */
    public static function candidateMonitorBy(string $id = null)
    {
        $array = [
            Constants::CAN_MONITOR_BY_IMAGE_MISSING => 'Image Missing',
            Constants::CAN_MONITOR_BY_QR => 'QR Missing',
            
        ];
        return self::returnValue($array, $id);
    }

    /**
     * Change Height Feet Inche to CM 
     * @return string single Value .
     */
    public static function heightChangeFeetInchToCM(int $feet = 0, int $inch = 0)
    {
        $feet_to_inch = 0;
        if ($feet > 0)
            $feet_to_inch = $feet * Constants::FEET_TO_INCH_MULTI_BY;
        $total_inch = $feet_to_inch + $inch;
        return strval($total_inch * Constants::INCH_TO_CM_MULTI_BY);
    }


    /**
     * Encrypt Primary Key
     * @return string single Value .
     */
    public static function encryptPk($pk)
    {
        $se = '';
        $array  = array_map('intval', str_split($pk));
        foreach ($array as $val)
            $se .= $val . mt_rand(0, 9);

        $slug = $se;
        $id_str = (string) $slug;
        $offset = rand(0, 9);
        $encoded = chr(79 + $offset);
        for ($i = 0, $len = strlen($id_str); $i < $len; ++$i) {
            $encoded .= chr(65 + $id_str[$i] + $offset);
        }
        return $encoded;
    }


    /**
     * Decrypt Primary Key
     * @return string single Value .
     */
    public static function decryptPk($enPk)
    {
        $encoded = $enPk;
        $offset = ord($encoded[0]) - 79;
        $encoded = substr($encoded, 1);
        for ($i = 0, $len = strlen($encoded); $i < $len; ++$i) {
            $encoded[$i] = ord($encoded[$i]) - $offset - 65;
        }

        $pt = '';
        $rowId = $encoded;
        $array  = array_map('intval', str_split($rowId));
        foreach ($array as $ke => $val) {
            if (($ke % 2) === 0) {
                $pt .= $val;
            }
        }
        return $pt;
    }

    /**
     * load academic result 
     */
    public static function educationBoardResult(string $examType, string $board, $roll_no, $year, $reg_no = NULL)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "http://ebapi.teletalk.com.bd//v1.0/ebapi.php");
        $post = array(
            'commandID' => 'getDetailsResult',
            'exam' => $examType,
            'board' => $board,  // jessore
            'rollNo' => $roll_no,  // 244925
            'year' => $year,
            'regNo' => $reg_no   // 1413371296
        );
        $post = json_encode($post);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'APIKEY: 46f4dd2a52453d4ef3fc137b65ee10040e73638e';
        $headers[] = 'Content-Length: ' . strlen($post);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $server_output = curl_exec($ch);
        return json_decode($server_output);
    }



    //  https://stackoverflow.com/questions/6161556/php-encode-methods
    // public static function encryptPk($pk){
    //     $se ='';
    //     $array  = array_map('intval', str_split($pk));	
    // foreach($array as $val)		
    // 	$se .= $val.mt_rand(0, 9);		
    // $slug= rand(1000,9999).$se.rand(10000,99999) ;
    //     return $slug;
    // }
    // public static function decryptPk($enPk){
    //     $pt = '';
    //     $rowId= substr($enPk, 4, -5);
    // $array  = array_map('intval', str_split($rowId));	
    // foreach($array as $ke=>$val){		
    // 	if(($ke%2)===0){
    // 		$pt .=$val;
    // 	}
    // }
    // return $pt;
    // }





    // function encode($id) {
    //   $rand_1 = rand(1111,9999);
    //   $rand_2 = rand(1111,9999);
    //   $id = $rand_1.$id.$rand_2;
    //   $id_str = (string) $id;
    //   $offset = rand(0, 9);
    //   $encoded = chr(79 + $offset);
    //   for ($i = 0, $len = strlen($id_str); $i < $len; ++$i) {
    //     $encoded .= chr(65 + $id_str[$i] + $offset);
    //   }
    //   return strtolower($encoded);
    // }


    // function decode($encoded) { 
    //   $encoded = strtoupper($encoded);
    //   $offset = ord($encoded[0]) - 79;
    //   $encoded = substr($encoded, 1);
    //   for ($i = 0, $len = strlen($encoded); $i < $len; ++$i) {
    //     $encoded[$i] = ord($encoded[$i]) - $offset - 65;
    //   }
    //   return (int) substr($encoded, 4, -4);
    // }
    // $original_no = 113;
    // $enc_v = $original_no; 
    // $encode_cv = encode($enc_v);
    // $dec = decode($encode_cv);

    // // $dec2 = decode($encode_cv);

    // echo 'Original No :'.$original_no;
    // echo '<br/>Encoded :'.$encode_cv;
    // echo '<br/>Decoded :'.$dec;



    /**
     * English to bangla number convert 
     */
    public static function convertToBanglaNumber(int $number)
    {
        $bangla = array('০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯');
        $english = range(0, 9);
        return str_replace($english, $bangla, $number);
    }


    public static function relationWithFreedomFighter($id = NULL)
    {
        $dataArray = array(
            Constants::YES => 'Father',
            Constants::NO => 'Grandfather'
        );
        if ($id == NULL)  return $dataArray;
        else return $dataArray[$id];
    }
}
