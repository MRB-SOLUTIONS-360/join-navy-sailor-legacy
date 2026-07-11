<?php

namespace frontend\controllers;

use common\models\CanEligibilityCheckInfo;
use common\models\ChangePassword;
use common\models\DeSailors;
use common\models\DownloadDocuments;
use common\models\LoginForm;
use common\models\ResetPassword;
use common\models\SailorBatchConfiguration;
use common\models\Sailors;
use common\models\Session;
use common\models\User;
use common\static\Constants;
use common\static\StaticMethod;
use frontend\models\SignupForm;
use Yii;
use yii\base\DynamicModel;

class CandidateController extends \yii\web\Controller
{

    /**
     * Validate birth registration number via AJAX
     */
    public function actionValidateBirthRegistration()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        
        $birth_reg_no = Yii::$app->request->post('birth_registration_no');
        
        if (empty($birth_reg_no)) {
            return ['success' => false, 'message' => 'Birth Registration No is required.'];
        }
        
        // Create a temporary model to validate
        $model = new SignupForm();
        $model->birth_registration_no = $birth_reg_no;
        $model->validateBirthRegistration('birth_registration_no', []);
        
        if ($model->hasErrors('birth_registration_no')) {
            return ['success' => false, 'message' => $model->getFirstError('birth_registration_no')];
        }
        
        return ['success' => true, 'message' => 'Birth Registration No is valid.'];
    }

    /**
     * Candidate Signup
     */
    public function actionSignUp()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }


        $model = new SignupForm();

        // Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }


        if ($model->load(Yii::$app->request->post())) {

            $session_captcha = Yii::$app->session['captcha'];
            if ($session_captcha['capture_value_result'] && time() > $session_captcha['capture_value_result_exp']) {
                $model->addError('captcha', 'Please refresh page and try again');
                return $this->render('sign_up',  ['model' => $model, 'captcha' => $this->captureValue()]);
            }
            if ($session_captcha['capture_value_result'] != $model->captcha) {
                $model->addError('captcha', 'Wrong result.');
                return $this->render('sign_up',  ['model' => $model, 'captcha' => $this->captureValue()]);
            }


            if ($model->signup()) {
                $identity = User::findOne(['username' => $model['username']]);
                // logs in the user
                Yii::$app->user->login($identity);

                if (Yii::$app->getRequest()->getQueryParam('ceci')) {
                    $return = self::haveCeci(Yii::$app->getRequest()->getQueryParam('ceci'));
                    if ($return['page'] == 'payment') {
                        if ($return['sailor_or_de_sailor'] == 'sailor')
                            return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/payment", 'slug' => StaticMethod::encryptPk($return['id'])]));
                        else
                            return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/payment", 'slug' => StaticMethod::encryptPk($return['id'])]));
                    }
                    // return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/payment", 'slug' => StaticMethod::encryptPk($return['id'])]));
                    else if ($return['page'] == 'application-list')
                        return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                    else if ($return['page'] == 'go-home') {
                        Yii::$app->session->setFlash('success_message', "Please check eligibility again for apply");
                        return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                    }
                    return $this->goHome();
                }

                // Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
                // return $this->goHome();
                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
            }
        }

        if (Yii::$app->getRequest()->getQueryParam('ceci')) {
            $ceci = Yii::$app->getRequest()->getQueryParam('ceci');
            $can_eligibility_info = CanEligibilityCheckInfo::find()->select(['id', 'dob'])->where(['id' => StaticMethod::decryptPk($ceci)])->asArray()->one();
            $model->dob = $can_eligibility_info['dob'];
        }

        return $this->render('sign_up', [
            'model' => $model,
            'captcha' => $this->captureValue(),
        ]);
    }

    protected function captureValue()
    {
        $arithmetic_operator = [1 => '+', 2 => '-', 3 => '*'];
        $rand_operator = rand(1, 3);
        $operator =  $arithmetic_operator[$rand_operator];
        $val_one = rand(1, 9);
        $val_two = rand(1, 9);
        $array = [$val_one, $operator,  $val_two];

        $result = 0;
        if (count($array) === 3) {
            $operand1 = $array[0];
            $operator = $array[1];
            $operand2 = $array[2];

            switch ($operator) {
                case '+':
                    $result = $operand1 + $operand2;
                    break;
                case '-':
                    $result = $operand1 - $operand2;
                    break;
                case '*':
                    $result = $operand1 * $operand2;
                    break;
            }
        }
        $session = Yii::$app->session;
        // the following code works:
        $session['captcha'] = [
            'capture_value_result' => $result,
            'capture_value_result_exp' => time() + (1 * 60),  //  // (1 * 60) 1 min
        ];
        return $array;
    }


    /**
     * Candidate Login
     */
    public function actionLogin()
    {

        // echo Yii::$app->getRequest()->getQueryParam('ceci');
        // die();
        // http://localhost/joinnavyV2/candidate/login?ceci=RFFJL




        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        $model->user_type = 'candidate';

        if ($model->load(Yii::$app->request->post())) {



            $session_captcha = Yii::$app->session['captcha'];
            if ($session_captcha['capture_value_result'] && time() > $session_captcha['capture_value_result_exp']) {
                $model->addError('captcha', 'Please refresh page and try again');
                return $this->render('login',  ['model' => $model, 'captcha' => $this->captureValue()]);
            }
            if ($session_captcha['capture_value_result'] != $model->captcha) {
                $model->addError('captcha', 'Wrong result.');
                return $this->render('login',  ['model' => $model, 'captcha' => $this->captureValue()]);
            }

            if ($model->login()) {
                // inset session in db
                $create_session = new Session();
                $create_session->user_id = Yii::$app->user->identity->id;
                $create_session->session_id = Yii::$app->session->getId();
                $create_session->expire = 2;
                $create_session->save();

                if (Yii::$app->getRequest()->getQueryParam('ceci')) {
                    $return = self::haveCeci(Yii::$app->getRequest()->getQueryParam('ceci'));
                    if ($return['page'] == 'payment') {
                        if ($return['sailor_or_de_sailor'] == 'sailor')
                            return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/payment", 'slug' => StaticMethod::encryptPk($return['id'])]));
                        else
                            return $this->redirect(Yii::$app->urlManager->createUrl(["de-sailor/payment", 'slug' => StaticMethod::encryptPk($return['id'])]));
                    } else if ($return['page'] == 'application-list')
                        return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                    else if ($return['page'] == 'go-home') {
                        Yii::$app->session->setFlash('success_message', "Please check eligibility again for apply");
                        return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                    }
                }
                return $this->redirect(Yii::$app->urlManager->createUrl(["/my-application"]));
                //return $this->goBack();
            }
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
            'captcha' => $this->captureValue(),
        ]);
    }


    public function haveCeci($ceci)
    {
        $return['page'] = '';
        $return['id'] = '';
        $return['sailor_or_de_sailor'] = 'sailor';
        // check number of application by using same eligibility id 
        // if have then need check eligibility again 

        $can_eligibility_info = CanEligibilityCheckInfo::find()->where(['id' => StaticMethod::decryptPk($ceci)])->one();

        if ($can_eligibility_info['apply_department_type'] && in_array($can_eligibility_info['apply_department_type'], [Constants::CANDIDATE_DE_SAILOR, Constants::CANDIDATE_DE_SAILOR_DOCKYARD]))
            $return['sailor_or_de_sailor'] = 'de_sailor';

        $no_application = 0;
        if ($return['sailor_or_de_sailor'] == 'sailor')
            $no_application = Sailors::find()->select(['id'])->where(['eligibility_info_id' => StaticMethod::decryptPk($ceci)])->asArray()->count();
        else if ($return['sailor_or_de_sailor'] == 'de_sailor')
            $no_application = DeSailors::find()->select(['id'])->where(['eligibility_info_id' => StaticMethod::decryptPk($ceci)])->asArray()->count();


        if ($no_application > 0)
            $return['page'] = 'go-home';
        else {
            //// get batch , center and configuration id
            //$batch_and_config_id = SailorBatchConfiguration::batchIdAndCenterIdByApplyDistrictAndDepartment(applyDept: $can_eligibility_info->apply_department, elgilbe_district: $can_eligibility_info->district);
            $batch_and_config_id = SailorBatchConfiguration::batchIdAndCenterIdByApplyDistrictAndDepartment(applyDept: $can_eligibility_info->apply_department, elgilbe_district: $can_eligibility_info->district, gender: $can_eligibility_info->gender);

            $check_already_applyed = 0;
            if ($return['sailor_or_de_sailor'] == 'sailor')
                $check_already_applyed = Sailors::numberOfApplication(batch_id: $batch_and_config_id['batch_id']);
            else if ($return['sailor_or_de_sailor'] == 'de_sailor')
                $check_already_applyed = DeSailors::numberOfApplication(batch_id: $batch_and_config_id['batch_id']);

            if ($check_already_applyed === 0) {
                // if ($batch_and_config_id['candidate_type'] == Constants::CANDIDATE_SAILOR) {
                if ($return['sailor_or_de_sailor'] == 'sailor')
                    $sailor = new Sailors();
                else if ($return['sailor_or_de_sailor'] == 'de_sailor') {
                    $sailor = new DeSailors();
                    $sailor->trade_course_experience =   $can_eligibility_info['have_trade_course_experience'];
                }
                $sailor->candidate_type = $can_eligibility_info['apply_department_type'];
                $sailor->eligibility_info_id = $can_eligibility_info->id;
                $sailor->app_unique_id = date('ymdH') . time();
                $sailor->candidate_designation = $can_eligibility_info->apply_department;
                $sailor->eligible_district = $can_eligibility_info->district;
                $sailor->center_id = $batch_and_config_id['center_id'];
                $sailor->batch_id = $batch_and_config_id['batch_id'];
                /// posso kotha 
                if ($can_eligibility_info->candidate_type == Constants::ELIGIBILITY_CANDIDATE_TYPE_POSSO_KOTA) {
                    $sailor->is_child_of_naval_officer = Constants::YES;
                    $sailor->naval_office_no = $can_eligibility_info->p_o_no;
                    $sailor->naval_rank =  $can_eligibility_info->rank;
                } else if ($can_eligibility_info->candidate_type == Constants::ELIGIBILITY_CANDIDATE_TYPE_DEPARTMENTAL) {
                    $sailor->is_departmental_candidate = Constants::YES;
                    $sailor->naval_office_no = $can_eligibility_info->p_o_no;
                    $sailor->naval_rank =  $can_eligibility_info->rank;
                }
                // $sailor->batch_config_id = $batch_and_config_id['id'];
                if ($sailor->save()) {
                    $return['page'] = 'payment';
                    $return['id'] = $sailor->id;
                }
                //   return 'payment';
                //return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/payment", 'slug' => StaticMethod::encryptPk($sailor->id)]));
                //}
            } else {
                $return['page'] = 'application-list';
                // return 'application-list';
                // return $this->redirect(Yii::$app->urlManager->createUrl(["sailor-candidate/my-application"]));
            }
        }
        return $return;
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Session::updateAll(['expire' => '1'], ['user_id' => Yii::$app->user->identity->id]);
        Yii::$app->user->logout();
        return $this->goHome();
    }

 

    /**
     * download application form 
     */
    public function actionDownloadForm()
    {

        $model = new DownloadDocuments();
        $sailor = [];

        $url = '';
        /// Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->download_by == 1) {
                $sailor = Sailors::find()
                    ->select(['id'])->where(['app_unique_id' => $model->application_id])
                    ->andWhere(['not', ['serial_no' => null]])->asArray()->one();
                if (empty($sailor)) {
                    Yii::$app->session->setFlash('application_close', "No record found");
                    /// have de sailor table again 
                } else {
                    $url = Yii::getAlias('@baseUrl') . '/sailor-candidate/download-form/?slug=' . StaticMethod::encryptPk($sailor['id']);
                    Yii::$app->session->setFlash('success_message', "Download your document");
                }
            } else {
                $dob = date('Y-m-d', strtotime($model->dob));
                $sailor = Sailors::find()
                    ->select(['id'])->where(['batch_id' => $model->batch])
                    ->andWhere(['serial_no' => $model->serial_no])
                    ->andWhere(['dob' => $dob])->asArray()->one();
                if (empty($sailor)) {
                    Yii::$app->session->setFlash('application_close', "No record found");
                    /// have de sailor table again 
                } else {
                    $url = Yii::getAlias('@baseUrl') . '/sailor-candidate/download-form/?slug=' . StaticMethod::encryptPk($sailor['id']);
                    Yii::$app->session->setFlash('success_message', "Download your document");
                }
            }
        }


        return $this->render('/sailor-candidate/candidate/application_form_download', [
            'model' => $model,
            'sailor' => $sailor,
            'url' => $url
        ]);
    }


    /**
     * Change password 
     */
    public function actionChangePassword()
    {
        if (Yii::$app->user->isGuest)
            return $this->goHome();

        $model = new ChangePassword();

        // Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // load user Data
            $user_info = User::find()->where('id =:id', [':id' => Yii::$app->user->identity->id])->one();
            $user_info->password_hash = Yii::$app->security->generatePasswordHash($model->newpassword);;
            if ($user_info->save()) {
                Yii::$app->session->setFlash('valid', 'Your password has been changed successfully');
            }
        }
        $model->newpassword = '';
        $model->repeatnepassword = '';
        return $this->render('change_password', [
            'model' => $model
        ]);
    }


    /**
     * forgot password 
     */
    public function actionRequestPasswordReset()
    {
        if (!Yii::$app->user->isGuest)
            return $this->goHome();

        $model = new ResetPassword();
        // Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {

            $user_info = User::find()
                ->where('username =:username', [':username' => $model->username])
                ->andWhere('dob =:dob', [':dob' => date('Y-m-d', strtotime($model->dob))])
                ->one();

            if ($user_info) {
                $new_pass = rand(11111, 99999);
                $user_info->password_hash = Yii::$app->security->generatePasswordHash($new_pass);
                // $user_info->updated_at = $user_info->id;
                if ($user_info->save()) {
                    Yii::$app->session->setFlash('valid', 'Your password has been changed successfully.Your new Password is :<strong> <span style="color:red">' . $new_pass . '</span><strong>');
                } else {
                    Yii::$app->session->setFlash('invalid', 'Sorry! internal server error. Try aganin after some time.');
                }
            } else {
                Yii::$app->session->setFlash('invalid', 'Sorry! no record found.');
            }
        }
        return $this->render('request_password_reset', [
            'model' => $model
        ]);
    }
}
