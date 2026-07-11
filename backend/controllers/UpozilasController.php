<?php

namespace backend\controllers;

use common\models\Upozilas;
use common\models\UpozilasSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * UpozilasController implements the CRUD actions for Upozilas model.
 */
class UpozilasController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => \yii\filters\AccessControl::className(),
                    'rules' => [
                        [
                            'allow' => true,
                            // 'actions' => ['delete'],  // Specify the actions you want to restrict
                            'roles' => ['@'],  // Only authenticated users (@) can access this action
                        ],
                        [
                            'allow' => false,  // Deny access to other users
                            // 'actions' => ['delete'],
                        ],
                    ],
                ],

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
     * Lists all Upozilas models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new UpozilasSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Upozilas model.
     * @param string $id ID
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
     * Creates a new Upozilas model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Upozilas();
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                $model = new Upozilas();
                Yii::$app->session->setFlash('success', "Upazila created successfully.");
            }
        } else {
            $model->loadDefaultValues();
        }
        return $this->render('_form', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Upozilas model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            $model = $this->findModel($id);
            Yii::$app->session->setFlash('success', "Upazila update successfully.");
        }
        return $this->render('_form', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Upozilas model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Upozilas model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id ID
     * @return Upozilas the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Upozilas::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
