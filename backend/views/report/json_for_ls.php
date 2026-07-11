<?php

use common\models\CanDesignation;
use common\models\Districts;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\static\Constants;
use common\static\DataEncryption;
use common\static\StaticMethod;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;

use kartik\select2\Select2;


/** @var yii\web\View $this */
/** @var common\models\SailorBatchs $model */
/** @var yii\widgets\ActiveForm $form */

$this->title =  'Json for LS';
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Sailor Report</a></li>
            <li class="breadcrumb-item active" aria-current="page">Json for LS</li>
        </ol>
    </nav>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <?php if (Yii::$app->session->hasFlash('success')) : ?>
                    <div class="alert alert-success" role="alert">
                        <?= Yii::$app->session->getFlash('success') ?>
                    </div>
                <?php endif; ?>
                <h4 class="header-title mb-3"><?= $this->title; ?></h4>
                <div class="tab-pane show active" id="custom-styles-preview">
                    <?php $form = ActiveForm::begin([
                        'id' => 'candidate_filter',
                        //'enableAjaxValidation' => true,
                    ]); ?>

                    <div class="row">
                        <div class="col-lg-2">
                            <?= $form->field($model, 'batch')->dropDownList(SailorBatchs::getAllBatch(Constants::CANDIDATE_SAILOR), ['prompt' => 'Select ' . $model->getAttributeLabel('candidate_type')]) ?>
                        </div>
                        <div class="col-lg-4">
                            <?php
                            echo $form->field($model, 'district')->widget(Select2::classname(), [
                                'data' => Districts::getAllDistrict(),
                                'value' => $model->district,
                                'language' => 'en',
                                'options' => ['multiple' => true, 'placeholder' => 'Select ' . $model->getAttributeLabel('district')],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]); ?>
                        </div>
                        

                        <div class="col-lg-2">
                            <div class="form-group mt-3">
                                <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
                <?php if ($sailor) {   ?>
                            <div class="col-lg-2" style="text-align: left;">
                               
                                    <?php
                                   $svgContent = '
                                   <svg fill="#000000" version="1.1" id="Capa_1" height="16" width="16" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" 
                                       viewBox="0 0 31.519 31.519" xml:space="preserve">
                                       <g>
                                           <path d="M11.183,0L3.021,8.619v22.9h25.477V0H11.183z M21.132,24.505c-0.06,0.096-0.201,0.315-0.834,0.321
                                               c-0.635-0.006-0.777-0.227-0.836-0.321c-0.535-0.866,0.027-3.132,0.791-5.104h0.088C21.105,21.373,21.666,23.639,21.132,24.505z
                                                M10.464,3.625v3.818H6.847L10.464,3.625z M26.527,29.55H4.99V9.413h7.443V1.971h4.598v1.595h2.178v1.681h-2.178v1.857h2.178v1.857
                                               h-2.178v1.857h2.178v1.761h-2.178v1.825H16.36v4.995h1.397c-0.715,2.07-1.276,4.707-0.28,6.327
                                               c0.397,0.646,1.208,1.411,2.794,1.429v0.004c0.009,0,0.018-0.002,0.025-0.002c0.009,0,0.017,0.002,0.025,0.002v-0.004
                                               c1.585-0.018,2.395-0.783,2.793-1.429c0.996-1.62,0.436-4.257-0.281-6.327h1.401v-4.995h-2.851v-1.825h2.179v-1.856h-2.177V8.961
                                               h2.179V7.104h-2.179V5.327h2.179V3.47h-2.179V1.971h5.142L26.527,29.55L26.527,29.55z"/>
                                       </g>
                                   </svg>';                                  
                                    
                                    echo Html::a($svgContent . ' ZIP', ['/report/download-json-for-ls'], ['class' => 'btn', 'target' => '_blank', 'style' => 'color: white;background-color: rebeccapurple; font-weight: bold;']) ?>
                                    
                                  
                              
                            </div>
                        <?php }  ?>


                <div class="row">
                    <table class="table table-striped table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>District</th>
                                <th>Total</th>                              
                               
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="3">No data available</td>
                            </tr>                           


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>