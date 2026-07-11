<?php

namespace frontend\controllers;

use common\models\DeSailors;
use common\models\Sailors;
use Yii;

class MyApplicationController extends \yii\web\Controller
{
    public function actionIndex()
    {

        if (Yii::$app->user->isGuest) {
            return $this->redirect(['candidate/login']);
        } else {
            $userIdentity = Yii::$app->user->identity->id;
            $application = Sailors::find()
                ->select(['id', 'candidate_designation', 'eligibility_info_id', 'app_unique_id', 'name', 'serial_no', 'batch_id', 'phase', 'payment_status', 'application_status'])
                ->where(['created_by' => $userIdentity])->all();

            $applicationDe =  DeSailors::find()
                ->select(['id', 'candidate_designation', 'eligibility_info_id', 'app_unique_id', 'name', 'serial_no', 'batch_id', 'phase', 'payment_status', 'application_status'])
                ->where(['created_by' => $userIdentity])->all();

            return $this->render('index', [
                'model' => $application,
                'model_de' => $applicationDe,
            ]);
        }
    }
}
