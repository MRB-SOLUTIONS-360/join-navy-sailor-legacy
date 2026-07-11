<?php

namespace backend\controllers;

use common\models\CanDesignation;
use common\models\DeSailors;
use common\models\Districts;
use common\models\SailorCentDistMapping;
use common\models\Sailors;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use common\static\DataEncryption;
use common\static\AES256CTR;

class AjaxController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }

    /**
     * get-sailor-information-by-roll
     * for reference candidate 
     */
    public function actionGetSailorInformationByRoll()
    {
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            $return_data['data'] = [];
            $return_data['have_data'] = 'no';
            $model = Sailors::find()->select(['id', 'name', 'eligible_district', 'permanent_district','application_status','referred_by','reference_details','relationship','reference_add_on'])->where(['serial_no' => $post['roll']])->asArray()->one();
            if ($model) {
                $eligible_district = Districts::find()->select(['name_en'])->where(['slug' => $model['eligible_district']])->asArray()->one();
                $permanent_district =  Districts::find()->select(['name_en'])->where(['slug' => $model['permanent_district']])->asArray()->one();
                $model['eligible_district'] = $eligible_district['name_en'] ?? '';
                $model['permanent_district'] = $permanent_district['name_en'] ?? '';
                $return_data['data'] = $model;
                $return_data['have_data'] = 'yes';
                $return_data['is_cancel_application'] = $model['application_status'] ==1 ? 'no' : 'yes';
            }
            return Json::encode($return_data);
        }
    }
    /**
     * get-sailor-information-by-roll
     * for reference candidate 
     */
    public function actionGetDeSailorInformationByRoll()
    {
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            $return_data['data'] = [];
            $return_data['have_data'] = 'no';
            $model = DeSailors::find()->select(['id', 'name', 'eligible_district', 'permanent_district','application_status','referred_by','reference_details','relationship'])->where(['serial_no' => $post['roll']])->asArray()->one();
            if ($model) {
                $eligible_district = Districts::find()->select(['name_en'])->where(['slug' => $model['eligible_district']])->asArray()->one();
                $permanent_district =  Districts::find()->select(['name_en'])->where(['slug' => $model['permanent_district']])->asArray()->one();
                $model['eligible_district'] = $eligible_district['name_en'] ?? '';
                $model['permanent_district'] = $permanent_district['name_en'] ?? '';
                $return_data['data'] = $model;
                $return_data['have_data'] = 'yes';
                $return_data['is_cancel_application'] = $model['application_status'] ==1 ? 'no' : 'yes';
            }
            return Json::encode($return_data);
        }
    }
    public function actionGetCandesignationByCantype()
    {
        if (Yii::$app->request->isAjax) {
            $get = Yii::$app->request->get();
            $return_data['data'] = CanDesignation::getAllActiveDesignation($get['candidate_type']);
            return Json::encode($return_data);
        }
    }
    public function actionGetAllAssignedDistrictByCenter()
    {
        if (Yii::$app->request->isAjax) {
            $get = Yii::$app->request->get();
            $center_id = $get['center'] ?? '';

            $modelMap = SailorCentDistMapping::GetAllAssignedDistrictByCenter($center_id);    

            $return_data['data'] =$modelMap;
            return Json::encode($return_data);
        }
    }



    public function actionDecodePhone()
    {
        if (Yii::$app->request->isAjax) {
            $sailor = Sailors::find()->select(['id', 'permanent_phone', 'permanent_phone_de'])
                ->where(['batch_id' => 1])
                ->andWhere(['is not', 'serial_no', null])
                ->andWhere(['is', 'permanent_phone_de', null])
                ->limit(5000)->orderBy('id desc')->asArray()->all(); 

            $decode_phone = 0;
            foreach ($sailor as $key => $val) {
                $de_phone = $val['permanent_phone'] ? DataEncryption::dataDecrypt($val['permanent_phone']) : '';
                $en = AES256CTR::dataEncrypt($de_phone);
                $sql = 'UPDATE jnavy_sailors SET permanent_phone_de = "' . $en . '" WHERE id =' . $val['id'] . '';
                $command = Yii::$app->db->createCommand($sql);
                $command->execute();
                $decode_phone++;
            }
            $return_data['data'] ='success';
            $return_data['decode_phone'] =$decode_phone;
            return Json::encode($return_data);
        }
    }
}
