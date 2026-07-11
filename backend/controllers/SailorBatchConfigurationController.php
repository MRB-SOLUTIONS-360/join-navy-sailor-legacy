<?php

namespace backend\controllers;

use common\models\SailorBatchConfiguration;
use backend\models\SailorBatchConfigurationSearch;
use common\models\SailorBatchConfigurationExamDate;
 
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;

/**
 * SailorBatchConfigurationController implements the CRUD actions for SailorBatchConfiguration model.
 */
class SailorBatchConfigurationController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all SailorBatchConfiguration models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new SailorBatchConfigurationSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SailorBatchConfiguration model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new SailorBatchConfiguration model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new SailorBatchConfiguration();
        // Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $post = $this->request->post()['SailorBatchConfigurationExamDate'] ?: [];
                $model->exam_date = $post['exam_date'][0] ?? '';
                if ($model->save()) {
                    // // exam date process 
                    $this->batchConfigurationExamDate(post: $post, batch_configuration_id: $model->id);
                    Yii::$app->session->setFlash('success', "Sailor batch configuration created successfully.");
                    $model = new SailorBatchConfiguration();
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        $examDates = [new SailorBatchConfigurationExamDate()];

        return $this->render('_form', [
            'model' => $model,
            'examDates' => $examDates,
        ]);
    }

    /**
     * Updates an existing SailorBatchConfiguration model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        // Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }


        if ($this->request->isPost && $model->load($this->request->post())) {
            $post = $this->request->post()['SailorBatchConfigurationExamDate'] ?: [];
            $model->exam_date = $post['exam_date'][0] ?? '';

            if ($model->save()) {
                // // exam date process 
                $this->batchConfigurationExamDate(post: $post, batch_configuration_id: $model->id);
                Yii::$app->session->setFlash('success', "Sailor batch configuration created successfully.");
                $model = $this->findModel($id);
            }
        }

        /// fields explode for form seleted 
        foreach ($model::inplodeFields()  as $key => $value) {
            if ($model->$value)
                $model->$value = explode(',', $model->$value);
        }
        $examDates = $model->examDates ?: [new SailorBatchConfigurationExamDate()];
        return $this->render('_form', [
            'model' => $model,
            'examDates' => $examDates,

        ]);
    }


    protected function batchConfigurationExamDate($post = array(), int $batch_configuration_id)
    {
        $examDates = $post['exam_date'] ?? [];
        $maxCandidates = $post['max_candidate_this_date'] ?? [];
        $ids = $post['id'] ?? [];
        // Process each exam date
        foreach ($examDates as $key => $value) {
            if ($value) {
                $dataModel = SailorBatchConfigurationExamDate::findOne($ids[$key]) ?: new SailorBatchConfigurationExamDate();
                // Assign values
                $dataModel->exam_date = $value;
                $dataModel->batch_configuration_id = $batch_configuration_id;
                $dataModel->max_candidate_this_date = $maxCandidates[$key] ?? 0;
                $dataModel->save();
            } else if (empty($value) && !empty($ids[$key])) {
                SailorBatchConfigurationExamDate::findOne($ids[$key])->delete();
            }
        }
        return true;
    }


    /**
     * Deletes an existing SailorBatchConfiguration model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Delete an existing SailorBatchConfigurationExamDate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     */
   public function actionDeleteExamDate()
{
    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

    // Decode JSON from request body
    $requestBody = Yii::$app->request->getRawBody();
    $data = json_decode($requestBody, true);

    $id = $data['id'] ?? null;

    if ($id && ($model = SailorBatchConfigurationExamDate::findOne($id))) {
        
        $model->delete();
        return [
            'success' => true,
            'message' => "Exam date deleted successfully."
        ];
    }

    return [
        'success' => false,       
        'message' => "The requested exam date does not exist."
    ];
}

    /**
     * Finds the SailorBatchConfiguration model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return SailorBatchConfiguration the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SailorBatchConfiguration::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
