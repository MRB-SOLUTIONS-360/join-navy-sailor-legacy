<?php

namespace backend\controllers;

use common\models\CanEligibilityCheckInfo;
use common\models\DeSailors;
use common\models\LoginForm;
use common\models\Sailors;
use common\models\User;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

use common\static\DataEncryption;

/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['logout', 'index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {

        // $sailor = Sailors::find()
        //     ->select(['count(id) as total', 'serial_generate_date'])->where(['batch_id' => 1])
        //     ->andWhere(['not', ['serial_no' => null]])->groupBy('serial_generate_date')->orderBy('serial_generate_date ASC')->asArray()->all();

         
        $sailor = DeSailors::find()
            ->select(['count(id) as total', 'serial_generate_date'])->where(['batch_id' => 2])
            ->andWhere(['not', ['serial_no' => null]])->groupBy('serial_generate_date')->orderBy('serial_generate_date ASC')->asArray()->all();

        $chart_data = [
            'have_value' => 'no',
            'value' => [],
            'date' => [],
        ];
        $total_generate_roll = 0;
        // if ($sailor) {
        //     $valeue =  $date = [];
        //     foreach ($sailor as $key => $va) {
        //         $total_generate_roll += $va['total'];
        //         $valeue[] =   $va['total'];
        //         $date[] =   "'" . date('d M Y', strtotime($va['serial_generate_date'])) . "'";
        //     }
        //     $chart_data['have_value'] = 'yes';
        //     $chart_data['value'] = $valeue;
        //     $chart_data['date'] = $date;
        // }

        //// eligibility 
        $desailor = DeSailors::find()
            ->select(['count(id) as total', 'serial_generate_date'])->where(['batch_id' => 2])
            ->andWhere(['not', ['serial_no' => null]])->groupBy('serial_generate_date')->orderBy('serial_generate_date ASC')->asArray()->all();
        $chart_data_3 = [
            'have_value' => 'no',
            'value' => [],
            'date' => [],
        ];
        if ($desailor) {
            $valeue =  $date = [];
            foreach ($desailor as $key => $va) {
                $total_generate_roll += $va['total'];
                $valeue[] =   $va['total'];
                $date[] =   "'" . date('d M Y', strtotime($va['serial_generate_date'])) . "'";
            }
            $chart_data_3['have_value'] = 'yes';
            $chart_data_3['value'] = $valeue;
            $chart_data_3['date'] = $date;
        }

        // $sailorEligibility = CanEligibilityCheckInfo::find()
        //     ->select(['count(id) as total', 'created_dt'])
        //     ->groupBy(['']); // ->asArray()->all();  DATE_FORMAT(created_dt,'%Y-%m-%d')


        // $s_sql = "SELECT COUNT(id) as total,created_dt  FROM jnavy_can_eligibility_check_info where DATE_FORMAT(created_dt,'%Y-%m-%d')>'2023-02-27'  GROUP BY DATE_FORMAT(created_dt,'%Y-%m-%d')";
        // $sailorEligibility =  CanEligibilityCheckInfo::findBySql($s_sql)->select(['COUNT(id) as total', 'created_dt'])->asArray()->all();
        ///   echo  $sailorEligibility->createCommand()->getRawSql();
        
        $chart_data_2 = [
            'have_value' => 'no',
            'value' => [],
            'date' => [],
        ];
        $s_sql = "SELECT COUNT(id) as total, DATE_FORMAT(created_dt, '%Y-%m-%d') as created_date FROM jnavy_can_eligibility_check_info WHERE DATE_FORMAT(created_dt, '%Y-%m-%d') > '2026-05-02' GROUP BY created_date";
        $sailorEligibility = CanEligibilityCheckInfo::findBySql($s_sql)->asArray()->all();
        if ($sailorEligibility) {
            $valeue2 =  $date2 = [];
            foreach ($sailorEligibility as $key => $va) {
                $valeue2[] =   $va['total'];
                $date2[] =   "'" . date('d M Y', strtotime($va['created_date'])) . "'";
            }
            $chart_data_2['have_value'] = 'yes';
            $chart_data_2['value'] = $valeue2;
            $chart_data_2['date'] = $date2;
        }

        return $this->render('index', ['chart_data' => $chart_data, 'chart_data3' => $chart_data_3, 'sailorEligibility' => $chart_data_2, 'total_generate_roll' => $total_generate_roll]);
        // return $this->render('index', ['chart_data' => $chart_data, 'sailorEligibility' => $chart_data_2, 'total_generate_roll' => $total_generate_roll]);
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
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        $model->user_type = 'admin';
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

            if ($model->login())
                return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
            'captcha' => $this->captureValue(),
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
