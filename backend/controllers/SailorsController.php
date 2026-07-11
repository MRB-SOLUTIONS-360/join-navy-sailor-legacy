<?php

namespace backend\controllers;

use backend\models\SailorsReference;
use common\models\SailorBatchs;
use common\models\Sailors;
use common\models\SailorsSearch;
use common\static\Constants;
use common\static\DataEncryption;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * SailorsController implements the CRUD actions for Sailors model.
 */
class SailorsController extends Controller
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
     * Lists all Sailors models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new SailorsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->orderBy('serial_no DESC');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'is_cancel_request' => false,
        ]);
    }
    /**
     * Lists all Sailors models.
     *
     * @return string
     */
    public function actionCancelRequest()
    {
        $searchModel = new SailorsSearch();

         $params = Yii::$app->request->queryParams;
        
        $dataProvider = $searchModel->search($params);    
        $dataProvider->query->andWhere(['request_for_cancel' => 1]);   
        $dataProvider->query->orderBy('serial_no DESC');

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'is_cancel_request' => true,
        ]);
    }


    /**
     * Lists all reference-candidate Sailors models.
     *
     * @return string
     */
    public function actionReferenceCandidate()
    {
        $searchModel = new SailorsSearch();
        $searchModel->have_reference = Constants::YES;
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->query->orderBy('updated_dt DESC');

        ////$dataProvider->getModels()
        Yii::$app->session->set('reference_candidate_query_param', $this->request->queryParams);

        return $this->render('reference/reference_candidate', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * add-reference-candidate Sailors models.
     *
     * @return string
     */
    public function actionAddReferenceCandidate()
    {
        $model = new SailorsReference();
        $model->scenario = $model::ADD_REFERENCE;
        /// Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->validate()) {

                $candidate = Sailors::find()
                    ->select(['id', 'eligibility_info_id', 'candidate_type', 'app_unique_id', 'referred_by', 'relationship', 'reference_details', 'have_reference','reference_add_on','last_reference_added'])->where(['serial_no' => $model->serial_no])->one();

                if ($candidate) {

                    $reff_by = $relationship = $reference_details =   [];
                    if ($candidate->referred_by) {
                        $json_decode = json_decode($candidate->referred_by, true);
                        $json_decode[] = $model->referred_by;
                        $reff_by = $json_decode;
                    } else
                        $reff_by[] = $model->referred_by;

                    if ($candidate->relationship) {
                        $json_decode = json_decode($candidate->relationship, true);
                        $json_decode[] = $model->relationship;
                        $relationship = $json_decode;
                    } else
                        $relationship[] = $model->relationship;

                    if ($candidate->reference_details) {
                        $json_decode = json_decode($candidate->reference_details, true);
                        $json_decode[] = ($model->reference_details) ? $model->reference_details : 'empty';
                        $reference_details = $json_decode;
                    } else
                        $reference_details[] = ($model->reference_details) ? $model->reference_details : 'empty';

                    if ($candidate->reference_add_on) {
                        $json_decode = json_decode($candidate->reference_add_on, true);
                        $json_decode[] =  date('Y-m-d H:i:s');
                        $reference_add_on = $json_decode;
                    } else
                        $reference_add_on[] = date('Y-m-d H:i:s');

                    $candidate->referred_by  = json_encode($reff_by, true);
                    $candidate->relationship  =  json_encode($relationship, true);
                    $candidate->reference_details  = json_encode($reference_details, true);
                    $candidate->have_reference  = Constants::YES;
                    $candidate->reference_add_on = json_encode($reference_add_on, true);

                    $candidate->last_reference_added = date('Y-m-d H:i:s');
                    if ($candidate->save()) {
                        Yii::$app->session->setFlash('success', "Reference added successfully");
                        $model = new SailorsReference();
                    }
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('reference/add_reference_candidate', [
            'model' => $model,
        ]);
    }

    /**
     * add-reference-candidate Sailors models.
     *
     * @return string
     */
    public function actionReferenceCandidateUpdate($id)
    {
        $model = SailorsReference::find()
            ->select(['id', 'serial_no', 'eligibility_info_id', 'app_unique_id', 'referred_by', 'relationship', 'reference_details', 'have_reference','reference_add_on','last_reference_added'])->where(['id' => $id])->one();

        // $model->scenario = $model::UPDATE_REFERENCE;
        /// Ajax validation set
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return \yii\widgets\ActiveForm::validate($model);
        }

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->validate()) {


                if (isset($_POST['referred_by'])) {
                    $model->referred_by  = json_encode($_POST['referred_by'], true);
                    $model->relationship  =  json_encode($_POST['relationship'], true);
                    $model->reference_details  = json_encode($_POST['reference_details'], true);
                    $model->have_reference  = Constants::YES;
                    // $model->reference_add_on = json_encode(date('Y-m-d H:i:s'), true);
                    $model->last_reference_added = date('Y-m-d H:i:s');
                } else {
                    $model->referred_by  = null;
                    $model->relationship  =  null;
                    $model->reference_details  = null;
                    $model->reference_add_on = null;
                    $model->last_reference_added = null;
                    $model->have_reference  = Constants::NO;
                }


                if ($model->save()) {
                    Yii::$app->session->setFlash('success', "Reference update successfully");
                }
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('reference/update_reference_candidate', [
            'model' => $model,
        ]);
    }



    /**
     * Displays a single Sailors model.
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
     * Creates a new Sailors model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    // public function actionCreate()
    // {
    //     $model = new Sailors();

    //     if ($this->request->isPost) {
    //         if ($model->load($this->request->post()) && $model->save()) {
    //             return $this->redirect(['view', 'id' => $model->id]);
    //         }
    //     } else {
    //         $model->loadDefaultValues();
    //     }

    //     return $this->render('_form', [
    //         'model' => $model,
    //     ]);
    // }

    /**
     * Updates an existing Sailors model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $prevImage = $model->photo;
        // Data Decrypt for view
        foreach ($model->encryption_fields_for_personal_info as $key => $field)
            $model->$field = $model->$field ? DataEncryption::dataDecrypt($model->$field) : '';

        if ($this->request->isPost && $model->load($this->request->post()) && $model->validate()) {

            // if dir not exist make a dir and name is primary key
            if (!is_dir(Yii::getAlias('@rootDirFilUpload') . '/media/sailor_candidate/' . $model->id)) {
                mkdir(Yii::getAlias('@rootDirFilUpload') . '/media/sailor_candidate/' . $model->id, 0777);
            }

            $fileImage = UploadedFile::getInstance($model, 'photo');
            if ($fileImage) {

                $batch_model = SailorBatchs::find()->select(['id', 'name_en'])
                    ->where(['id' => $model->batch_id])->asArray()
                    ->one();
                $batch_name = 'batch_not_found!';
                if ($batch_model)              // Replace all special characters and spaces with underscores
                    $batch_name = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', $batch_model['name_en']), '_'));



                $uploadBasePath =  Yii::getAlias('@rootDirFilUpload');
                $uniqueName = date('Ymdhms');
                $model->photo = '/media/sailor_candidate/' . $model->id . '/' . $uniqueName . '.' . $fileImage->extension;
                $path  = $uploadBasePath . $model->photo;

                $unlink_dir = $uploadBasePath . '/media/sailor_candidate/' . $model->id;

                // Save the uploaded file locally
                $fileImage->saveAs($path);


                $baseName = $fileImage->baseName;
                $cleanBaseName = strtolower(trim(preg_replace('/[^a-zA-Z0-9_-]/', '_', $baseName)));
                $cleanFileName = $cleanBaseName . '.' . $fileImage->extension;
                $fileName =  $batch_name . '/' . $model->id . '_' . time() . '_' . $cleanFileName;

                $r2Storage = Yii::$app->r2Storage;
                $fileUrl = $r2Storage->uploadFile($fileName, $path);
                if ($fileUrl)
                    $model->photo =  $fileName;

                // Unlink  file
                if (!empty($prevImage) &&  file_exists(Yii::getAlias('@rootDirFilUpload') . $prevImage))
                    unlink(Yii::getAlias('@rootDirFilUpload') . $prevImage);

                if (is_dir($unlink_dir)) {          
                    $files = array_diff(scandir($unlink_dir), array('.', '..'));               
                    foreach ($files as $file) {
                        $filePath = $unlink_dir . DIRECTORY_SEPARATOR . $file;
                            unlink($filePath); // Delete the file                   
                    }
                    rmdir($unlink_dir);
                }

                // remove old file 
                $r2Storage->deleteFile($prevImage);
            } else $model->photo = $prevImage;


            // if manual paid yes then update phase 
            if ($model->is_manula_paid == Constants::YES)
                $model->phase = Constants::SAILOR_PHASE_TWO;

            // Data Decrypt for view
            foreach ($model->encryption_fields_for_personal_info as $key => $field)
                $model->$field = $model->$field ? DataEncryption::dataEncrypt($model->$field) : $model->$field;


            if ($model->save(false)) {
                Yii::$app->session->setFlash('success', "Candidate update successfully.");
                if ($fileImage) $fileImage->saveAs($path);
                return $this->redirect(Yii::$app->request->url);
                // $model = $this->findModel($id);
            }
        }

        return $this->render('_form', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Sailors model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    // public function actionDelete($id)
    // {
    //     $this->findModel($id)->delete();

    //     return $this->redirect(['index']);
    // }

    /**
     * Finds the Sailors model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id ID
     * @return Sailors the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Sailors::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
