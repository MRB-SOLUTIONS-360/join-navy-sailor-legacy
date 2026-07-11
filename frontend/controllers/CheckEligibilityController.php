<?php

namespace frontend\controllers;

use common\models\CanDesignation;
use common\models\CanEligibilityCheckInfo;
use common\models\DeSailors;
use common\models\Eligibility;
use common\models\SailorBatchConfiguration;
use common\models\SailorBatchs;
use common\models\Sailors;
use common\models\Session;
use common\models\User;
use common\static\Constants;
use common\static\StaticMethod;
use Yii;
use yii\db\Query;
use yii\web\NotFoundHttpException;

class CheckEligibilityController extends \yii\web\Controller
{

    public function beforeAction($action)
    {
        if ($action->id == 'get-description') {
            $this->enableCsrfValidation = false;
        }
        return parent::beforeAction($action);
    }
    /**
     * Candidate Personal Information  for Eligibility Check
     *
     * @return mixed
     */

    public function actionIndex()
    {

        if (!Yii::$app->user->isGuest) {
            $count =  Session::find()->where(['session_id' => Yii::$app->session->getId()])->andWhere(['user_id' => Yii::$app->user->identity->id])->andWhere(['expire' => 2])->count();
            if ($count == 0) {
                Yii::$app->user->logout();
                return $this->goHome();
            }
        }
        $model = new CanEligibilityCheckInfo();
        $model->apply_department_type = Constants::CANDIDATE_SAILOR;
        $model->scenario = $model::SCENARIO_PERSONAL_INFO;
        // Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->validate()) {
                // change height to CM 
                $model->height = StaticMethod::heightChangeFeetInchToCM(inch: $model->height_inch, feet: $model->height_feet);
                $model->dob = date('Y-m-d', strtotime($model->dob));


                if ($model->save()) {
                    // update user table dob 
                    // if(!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->dob) && Yii::$app->user->identity->dob !== $model->dob){
                    //     $user = User::findOne(Yii::$app->user->identity->id); 
                    //     $user->dob = $model->dob;  
                    //     $user->save();
                    // }
                    return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility/academic-info", 'slug' => StaticMethod::encryptPk($model->id)]));
                }
            }
        }

        if (!Yii::$app->user->isGuest && !empty(Yii::$app->user->identity->dob)) {
            $model->dob = Yii::$app->user->identity->dob;
        }

        return $this->render(
            'personal_info',
            [
                'model' => $model,
            ]
        );
    }

    /**
     * Candidate Academic Information  for Eligibility Check
     *
     * @return mixed
     */
    public function actionAcademicInfo($slug)
    {
        $model = $this->findModel($slug);
        $model->scenario = $model::SCENARIO_ACADEMIC_INFO;

        // Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($this->request->isPost) {


            if ($model->load($this->request->post()) && $model->validate()) {


                if ($model->save()) {
                    return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility/eligible-department", 'slug' => StaticMethod::encryptPk($model->id)]));
                }
                echo '<pre>';
                print_r($model->attributes);
                echo '</pre>';
                die();
            }
        }

        return $this->render(
            'academic_info',
            [
                'model' => $model,
            ]
        );
    }
    /**
     * Candidate Eligible Department
     *
     * @return mixed
     */
    public function actionEligibleDepartment($slug)
    {

        // echo $_SERVER['REQUEST_METHOD'];
        // die();
        //$model = $this->findModel($slug);
        $model = CanEligibilityCheckInfo::find()
            ->select(
                [
                    'id',
                    'candidate_type',
                    'gender',
                    'marital_status',
                    'height',
                    'eye_status',
                    'jsc_result',
                    'ssc_equv_result',
                    'ssc_equv_group',
                    'ssc_equv_is_biology_include',
                    'ssc_equv_is_trade_course_complete',
                    'trade_course_subject',
                    'hsc_equv_academic_type',
                    'hsc_equv_result',
                    'hsc_equv_group',
                    'dob',
                    'district',
                    'have_trade_course_experience'
                ]
            )
            ->where(['id' => StaticMethod::decryptPk($slug)])->asArray()->one();
        $eligibility_data_model = [];
        $sql_r = '';
        // $batch_age_count = ['2023-01-01', '2023-06-01'];
        $batch_age_count = [];
        // Active batch candidate types 
        $allow_batch_candidate_type = [];
        $active_batch_ids = [];
        $candidate_age = [];  // candidate age  [17.06,20.00]
        // configuration designation list 
        $config_desig_candidate_type_wise = [];
        if ($model) {
            // batch age date clculate ,can be single or mltiple 
            // if there is no running batch date will be current date        
            $sailor_active_batch = SailorBatchs::find()
                ->select(['id', 'candidate_type', 'name_en', 'circular_date', 'circular_close_date', 'allow_refund'])
                ->isActive()->isActiveBatch()->isCircularCloseDateGraterCurrentDate()->asArray()
                ->all();


            if ($sailor_active_batch) {
                foreach ($sailor_active_batch as $key => $value) {
                    $batch_age_count[] = $value['circular_date'];
                    $allow_batch_candidate_type[] = $value['candidate_type'];
                    $active_batch_ids[] = $value['id'];
                }
            } else
                $batch_age_count[] = date('Y-m-d');




            // dynamic between for age check 
            $dynamic_between = [];
            $dynamic_between[0] = 'or';
            foreach ($batch_age_count as $key => $values) {
                $age_year_month = StaticMethod::getDifferenceBetweenTwoDateYearMonth(maxDate: $values, minDate: $model['dob']);

                $candidate_age[] = $age_year_month;
                // echo $values .'--'.$model->dob.'---'.$age_year_month.'<br/>';
                $max_age  = "max_age>='" . $age_year_month . "'";
                if ($model['candidate_type'] == Constants::ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL)
                    $max_age  = "dept_can_max_age>='" . $age_year_month . "'";

                $dynamic_between[] = [
                    'and',
                    "min_age<='" . $age_year_month . "'",
                    $max_age
                ];
            }

            /// if have age then check eligibility 
            if ($candidate_age) {
                // Age check query 
                // SELECT * FROM `jnavy_eligibility` WHERE '15.00' BETWEEN min_age AND max_age
                // SELECT * FROM `jnavy_eligibility` WHERE (`min_age` <= '15.00') AND (`max_age` >= '5.00')
                // SELECT * FROM `jnavy_eligibility` WHERE (`status`=1) AND (((min_age<=17.00) AND (max_age>=17.00)) OR ((min_age<=19.00) AND (max_age>=19.00)))

                $eligibility_model = Eligibility::find()
                    ->select(['id', 'candidate_designation', 'candidate_type', 'gender', 'marital_status', 'height_male', 'height_female', 'jsc_result', 'ssc_result', 'ssc_ac_group', 'hsc_result', 'diploma_result', 'hsc_ac_group', 'is_required_biology', 'is_allow_trade_course', 'is_allow_diploma', 'diploma_result', 'trade_course_subject', 'hons_diploma_subject','is_required_trade_course_experience'])
                    ->where(['status' => Constants::STATUS_ACTIVE]) // active 
                    ->andWhere(['jsc_result' => $model['jsc_result']]) // jsc_result check 
                    ->andWhere(['REGEXP', 'gender', $model['gender']]) // gender check 
                    ->andWhere(['REGEXP', 'marital_status', $model['marital_status']]); // marital_ status check 

                ////   $query->orWhere(['REGEXP','other_sanction_org', "(^|,)".$orgId."(,|$)" ]);

                if ($model['gender'] == Constants::GENDER_MALE)
                    $eligibility_model->andWhere(['<=', 'height_male', $model['height']]); // height check 
                else
                    $eligibility_model->andWhere(['<=', 'height_female', $model['height']]); // height check  

                /// age check
                $eligibility_model->andWhere($dynamic_between);
                // echo $sql_r = $eligibility_model->createCommand()->getRawSql();
                // die();
                $eligibility_data_model = $eligibility_model->asArray()->all();

                // echo  $eligibility_model->createCommand()->getRawSql();

                // /// manage academic type wise department
                /**
                 * Condition satisfy kora sob eligibility id array er moddhe thakbe then array unique kore nite hobe 
                 */


                // echo '<pre>';
                // print_r($model);
                // echo '<pre>';
                // print_r($eligibility_data_model);
                // die();


                $eligible_ids = [];
                // echo '<hr/>';
                foreach ($eligibility_data_model as $k => $eligible_array) {
                    if (empty($eligible_array['ssc_result']) || empty($eligible_array['ssc_ac_group'])) {  /// for topass 
                        $eligible_ids[] = $eligible_array['id'];
                    } else {
                        // if have ssc result                   
                        if (
                            !empty($model['ssc_equv_result'])
                            && $model['ssc_equv_result'] >= $eligible_array['ssc_result']
                            && !empty($model['ssc_equv_group'])
                            && in_array($model['ssc_equv_group'], explode(',', $eligible_array['ssc_ac_group']))
                        ) {

                            // is_required_biology
                            if ($eligible_array['is_required_biology'] == Constants::YES && $model['ssc_equv_is_biology_include'] != Constants::YES) {
                                continue;
                            }
                            // && $model['have_trade_course_experience'] != Constants::YES
                            if ($eligible_array['is_required_trade_course_experience'] == Constants::YES && !empty($model['have_trade_course_experience']) && $model['have_trade_course_experience'] != Constants::YES) {
                                continue;
                            }
                            if (
                                $eligible_array['is_allow_trade_course'] == Constants::YES
                                && $eligible_array['is_allow_diploma'] == Constants::NO
                            ) {
                                // if must have trade course 
                                if (
                                    $model['ssc_equv_is_trade_course_complete'] == Constants::YES
                                    && in_array($model['trade_course_subject'], explode(',', $eligible_array['trade_course_subject']))
                                ) {
                                    $eligible_ids[] = $eligible_array['id'];
                                }
                                continue;
                            } else if (
                                $eligible_array['is_allow_diploma'] == Constants::YES
                                && $eligible_array['is_allow_trade_course'] == Constants::NO
                            ) {
                                // if must have diploma 
                                if (
                                    !empty($model['hsc_equv_academic_type'])
                                    && $model['hsc_equv_academic_type'] == Constants::NO
                                    && $model['hsc_equv_result'] >= $eligible_array['diploma_result']
                                    && !empty($eligible_array['hons_diploma_subject'])
                                    && in_array($model['hsc_equv_group'], explode(',', $eligible_array['hons_diploma_subject']))
                                ) {
                                    $eligible_ids[] = $eligible_array['id'];
                                }
                                continue;
                            } else if (
                                $eligible_array['is_allow_trade_course'] == Constants::NO
                                && $eligible_array['is_allow_diploma'] == Constants::NO
                            ) {
                                // if trade and diploma optional 
                                // here have additional check ssc science and biology 
                                $eligible_ids[] = $eligible_array['id'];
                                continue;
                            }
                        }
                        // else nothing to do
                    }
                }


               


                $eligibility_data_model = Eligibility::find()
                    ->select(['id', 'candidate_type', 'candidate_designation'])
                    ->where(['in', 'id', $eligible_ids])
                    ->andFilterWhere(['in', 'candidate_type', $allow_batch_candidate_type])
                    ->asArray()->all();               

                if ($active_batch_ids) {

                    $config = SailorBatchConfiguration::find()
                        ->select(['id', 'candidate_designation', 'candidate_type', 'batch_id', 'center_id'])
                        ->isActive()
                        ->andFilterWhere(['in', 'batch_id', $active_batch_ids])
                        ->andWhere(['REGEXP', 'gender', $model['gender']])
                        ->andWhere([
                            'or',
                            ['REGEXP', 'district_slug', "(^|,)" . $model['district'] . "(,|$)"],
                            ['=', 'district_slug', '']
                        ])

                        // echo $config->createCommand()->getRawSql();
                        // die();
                        ///->groupBy('batch_id')
                        ->asArray()->all();

                    foreach ($config as $k => $value_config) {
                        $explode_desig = explode(',', $value_config['candidate_designation']);
                        if (!array_key_exists($value_config['candidate_type'], $config_desig_candidate_type_wise))
                            $config_desig_candidate_type_wise[$value_config['candidate_type']] = $explode_desig;
                        else {
                            $config_desig_candidate_type_wise[$value_config['candidate_type']] = array_unique(array_merge($config_desig_candidate_type_wise[$value_config['candidate_type']], $explode_desig));
                        }
                    }
                    //echo  $sql_r = $config->createCommand()->getRawSql();
                }
                ///echo     $sql_r = $eligibility_model->createCommand()->getRawSql();

            }
        }
        return $this->render(
            'eligible_department',
            [
                'model' => $model,
                'eligibility_data_model' => $eligibility_data_model,
                'candidate_age' => $candidate_age,
                'allow_batch_candidate_type' => $allow_batch_candidate_type,
                'config_desig_candidate_type_wise' => $config_desig_candidate_type_wise,
            ]
        );
    }

    /**
     * Apply department 
     */
    public function actionApplyDepartment($slug = null, $adpt = null)
    {
        if ($slug &&  $adpt) {
            $session = Yii::$app->session;
            if ($session->has('eligible_department')) {
                $adpt = StaticMethod::decryptPk($adpt);

                $eligible_department = $session->get('eligible_department');

                if (in_array($adpt, $eligible_department)) {
                    $model = $this->findModel($slug);
                    $model->eligible_dept = implode(',', $eligible_department);
                    $model->apply_department = $adpt;
                    $model->apply_department_type = CanDesignation::find()->select(['id', 'candidate_type'])->where(['id' => $adpt])->asArray()->one()['candidate_type'];

                    $canTypeSailorOrDeSailor = 'sailor';
                    if ($model->apply_department_type && in_array($model->apply_department_type, [Constants::CANDIDATE_DE_SAILOR, Constants::CANDIDATE_DE_SAILOR_DOCKYARD]))
                        $canTypeSailorOrDeSailor = 'de_sailor';


                    //// get batch , center and configuration id
                    $batch_and_config_id = SailorBatchConfiguration::batchIdAndCenterIdByApplyDistrictAndDepartment(applyDept: $model->apply_department, elgilbe_district: $model->district, gender: $model->gender);


                    if ($batch_and_config_id && $model->save()) {
                        // candidate allow for one application                    
                        if (Yii::$app->user->getIsGuest()) {
                            return $this->redirect(Yii::$app->urlManager->createUrl(["candidate/sign-up", 'ceci' => StaticMethod::encryptPk($model->id)]));
                        } else {

                            $check_already_applyed = 0;
                            if ($canTypeSailorOrDeSailor  == 'sailor')
                                $check_already_applyed = Sailors::numberOfApplication(batch_id: $batch_and_config_id['batch_id']);
                            else if ($canTypeSailorOrDeSailor  == 'de_sailor')
                                $check_already_applyed = DeSailors::numberOfApplication(batch_id: $batch_and_config_id['batch_id']);


                            if ($check_already_applyed === 0) {
                                if ($canTypeSailorOrDeSailor  == 'sailor') {
                                    $sailor = new Sailors();
                                    // $sailor->candidate_type = $model->apply_department_type;
                                } else if ($canTypeSailorOrDeSailor  == 'de_sailor') {
                                    $sailor = new DeSailors();
                                }
                                $sailor->candidate_type = $model->apply_department_type;


                                $sailor->eligibility_info_id = $model->id;
                                $sailor->app_unique_id = date('ymdH') . time();
                                $sailor->candidate_designation = $model->apply_department;
                                $sailor->eligible_district = $model->district;

                                $sailor->center_id = $batch_and_config_id['center_id'];
                                $sailor->batch_id = $batch_and_config_id['batch_id'];
                                ///  $sailor->batch_config_id = $batch_and_config_id['id'];


                                if ($sailor->save()) {
                                    if ($canTypeSailorOrDeSailor  == 'sailor')
                                        return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/payment", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                                    else if ($canTypeSailorOrDeSailor  == 'de_sailor')
                                        return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/payment", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                                } else {
                                    echo '<pre>';
                                    print_r($sailor);
                                    echo '</pre>';
                                    die();
                                }
                            } else {
                                Yii::$app->session->setFlash('error', "Sorry you already applied once this batch");
                                return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility/eligible-department", 'slug' => $slug]));
                            }
                        }
                    } else {
                        Yii::$app->session->setFlash('error', "Please try again after sometime.");
                        return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility/eligible-department", 'slug' => $slug]));
                    }
                } else {
                    Yii::$app->session->setFlash('error', "Sorry.You are not allow to apply your selected department");
                    return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility/eligible-department", 'slug' => $slug]));
                }
            }
            return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility"]));
        }
        return $this->redirect(Yii::$app->urlManager->createUrl(["check-eligibility"]));
    }


    /**
     * get-description  by id for eligibility 
     */
    public function actionGetDescription()
    {
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $can_desig =  CanDesignation::find()->select(['id', 'description', 'name_en'])->where(['id' => $data['id']])->asArray()->one();
            return json_encode($can_desig);
            exit();
        }
    }

    /**
     * Finds the CanEligibilityCheckInfo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return CanEligibilityCheckInfo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CanEligibilityCheckInfo::findOne(['id' => StaticMethod::decryptPk($id)])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
