<?php

namespace frontend\controllers;

use common\models\Districts;
use common\models\Subjects;
use common\models\Unions;
use common\models\Upozilas;
use common\static\Constants;
use common\static\StaticMethod;
use Yii;
use yii\helpers\Json;

class AjaxController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        $this->enableCsrfValidation = false;
        return parent::beforeAction($action);
    }
    /**
     * District list by candidate type , candidate_type is required 
     *
     * @return mixed
     */
    public function actionDistrictByCandidateType()
    {
        if (Yii::$app->request->isAjax) {
            $post = Yii::$app->request->post();
            $query = Districts::find()->select(['id', 'slug', 'name_en'])->asArray();
            $query->where(['status' => Constants::STATUS_ACTIVE]);
            if ($post['candidate_type'] == Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA) {
                $query->andFilterWhere(['slug' => Constants::DIST_NAVY_CHILD_SLUG]);
            } elseif (!empty($post['candidate_type'])) {
                $query->andFilterWhere(['<>', 'slug', Constants::DIST_NAVY_CHILD_SLUG]);
            }
            $query->orderBy('name_en ASC');
            $model = $query->all();
            return Json::encode($model);
        }
    }

    /**
     * hsc-diploma-ac-group 
     *
     * @return mixed
     */
    public function actionHscDiplomaAcGroup()
    {
        if (Yii::$app->request->isAjax) {
            /// have pull de sailor subject
            $post = Yii::$app->request->post();
            $list = [];
            if ($post['ac_type'] == Constants::NO) {
                $de_diploma_subject =  Subjects::getAllActiveSubjectBySubjectType(subject_type: Constants::SUBJECT_TYPE_DIPLOMA, candidate_type: Constants::CANDIDATE_DE_SAILOR);
                foreach ($de_diploma_subject as $k => $val) {
                    $list[$val['id']] = $val['name_en'];
                }
            } else {
                $list = StaticMethod::academicGroupHsc();
            }
            return Json::encode($list);
        }
    }


    // get upazila by district
    public function actionUpazialByDistrict()
    {
        if (Yii::$app->request->isAjax) {
            /// have pull de sailor subject
            $post = Yii::$app->request->get();
            $district = Districts::getIdBySlug($post['dist_thana']);
            $upazilas = Upozilas::getUpazilaListCandidate($district['id']);
            $return = [
                'status' => false,
                'data' => []
            ];
            if ($upazilas) {
                // asort($upazilas);
                $return['status'] = true;
                $return['data'] = array_flip($upazilas);
            }
            return Json::encode($return);
        }
    }
    public function actionUnionByUpazial()
    {
        if (Yii::$app->request->isAjax) {
            /// have pull de sailor subject
            $post = Yii::$app->request->get();
            $unions = Unions::getUnionsListForCandidate($post['dist_thana']);
            $return = [
                'status' => false,
                'data' => []
            ];
            if ($unions) {
                // asort($upazilas);
                $return['status'] = true;
                $return['data'] = array_flip($unions);
            }
            return Json::encode($return);
        }
    }
}
