<?php

namespace backend\controllers;

use common\models\SailorBatchs;
use backend\models\SailorBatchsSearch;
use Yii;
use yii\bootstrap5\ActiveForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * SailorBatchsController implements the CRUD actions for SailorBatchs model.
 */
class SailorBatchsController extends Controller
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
     * Lists all SailorBatchs models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new SailorBatchsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single SailorBatchs model.
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
     * Creates a new SailorBatchs model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new SailorBatchs();

        // if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
        //     Yii::$app->response->format = Response::FORMAT_JSON;
        //     return ActiveForm::validate($model);
        // }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->validate()) {

                $fileImage = UploadedFile::getInstance($model, 'circular_media');
                if ($fileImage) {
                    $uploadBasePath =  Yii::getAlias('@rootDirFilUpload');
                    $uniqueName = date('Ymdhms');
                    $model->circular_media = '/media/sailor_circular/' . $uniqueName . '.' . $fileImage->extension;
                    $path  = $uploadBasePath . $model->circular_media;
                    // Unlink  file
                    if (!empty($prevImage) &&  file_exists(Yii::getAlias('@rootDirFilUpload') . $prevImage))
                        unlink(Yii::getAlias('@rootDirFilUpload') . $prevImage);
                }

                if ($model->save()) {
                    if ($fileImage) $fileImage->saveAs($path);
                    Yii::$app->session->setFlash('success', "Batch created successfully.");
                    $model = new SailorBatchs();
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('_form', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing SailorBatchs model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        $prevImage = $model->circular_media;

        if ($this->request->isPost && $model->load($this->request->post())) {
            $fileImage = UploadedFile::getInstance($model, 'circular_media');
            if ($fileImage) {
                $uploadBasePath =  Yii::getAlias('@rootDirFilUpload');
                $uniqueName = date('Ymdhms');
                $model->circular_media = '/media/sailor_circular/' . $uniqueName . '.' . $fileImage->extension;
                $path  = $uploadBasePath . $model->circular_media;
                // Unlink  file
                if (!empty($prevImage) &&  file_exists(Yii::getAlias('@rootDirFilUpload') . $prevImage))
                    unlink(Yii::getAlias('@rootDirFilUpload') . $prevImage);
            } else $model->circular_media = $prevImage;

            if ($model->save()) {
                if ($fileImage) $fileImage->saveAs($path);
                Yii::$app->session->setFlash('success', "Batch updated successfully.");
                $model = $this->findModel($id);
            }
        }

        if ($model->circular_close_date && !empty($model->circular_close_date))
            $model->circular_close_date  = date('Y-m-d H:i', strtotime($model->circular_close_date));
        if ($model->circular_start_date && !empty($model->circular_start_date))
            $model->circular_start_date  = date('Y-m-d H:i', strtotime($model->circular_start_date));



        return $this->render('_form', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing SailorBatchs model.
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
     * Finds the SailorBatchs model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return SailorBatchs the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SailorBatchs::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
