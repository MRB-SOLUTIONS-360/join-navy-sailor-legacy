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

$this->title =  'Exam center & exam date wise candidate';
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Sailor Report</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= $this->title; ?></li>
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
                   
                        <div class="col-lg-2">
                            <?= $form->field($model, 'center')->dropDownList(SailorCenters::getAllCenter(), ['prompt' => 'Select ' . $model->getAttributeLabel('center')]) ?>
                        </div>
                        <div class="col-lg-2">
                           

                        <?= $form->field($model, 'exam_date')->widget(\yii\jui\DatePicker::classname(), [
                            'language' => 'en',
                            'dateFormat' => 'yyyy-MM-dd',
                            'clientOptions' => [
                                'changeMonth' => true,
                                'changeYear' => true,     
                                'yearRange' => (date('Y') - 3) . ':' . (date('Y') + 3),                                    
                            ], 
                            'options' => [
                                        'class' => 'form-control custom-date-class',
                                    ], 
                        ]) ?>
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
                        <div class="form-group mt-1">
                            <?php
                            $pdf = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-pdf" viewBox="0 0 16 16">
                                    <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                    <path d="M4.603 12.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.701 19.701 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.187-.012.395-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.065.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.716 5.716 0 0 1-.911-.95 11.642 11.642 0 0 0-1.997.406 11.311 11.311 0 0 1-1.021 1.51c-.29.35-.608.655-.926.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.27.27 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.647 12.647 0 0 1 1.01-.193 11.666 11.666 0 0 1-.51-.858 20.741 20.741 0 0 1-.5 1.05zm2.446.45c.15.162.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.881 3.881 0 0 0-.612-.053zM8.078 5.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                  </svg>';
                            $excel = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-excel" viewBox="0 0 16 16">
                                  <path d="M5.18 4.616a.5.5 0 0 1 .704.064L8 7.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 8l2.233 2.68a.5.5 0 0 1-.768.64L8 8.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 8 5.116 5.32a.5.5 0 0 1 .064-.704z"/>
                                  <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                </svg>';
                            echo Html::a($pdf . ' PDF', ['/report/exam-date-center-candidate-pdf'], ['class' => 'btn', 'target' => '_blank', 'style' => 'color: white;background-color: rebeccapurple; font-weight: bold;']) ?>
                            <?= Html::a($excel . ' Excel', ['/report/district-candidate-excel'], ['class' => 'btn', 'style' => 'color: white;background-color: rebeccapurple; font-weight: bold;']) ?>
                            <?php // Html::a('Submit', '#', ['class' => 'btn btn-success']) 
                            ?>
                        </div>
                    </div>
                <?php }  ?>

                <?php
                if ($sailor) {
                ?>
                    <div class="row" style="text-align: center;">
                        <div class="col-lg-2"> </div>
                        <div class="col-lg-8">
                            <img src="<?= Yii::getAlias('@rootMediaShow'); ?>/media/main_logo.png" alt="not found" style="width:80px; text-align: center; margin: 0 auto;">
                            <h2 class="h2_padding_margin_0 font_kp" style="font-size: 10pt; font-weight: bold; margin: 0px; ">বাংলাদেশ নৌবাহিনী</h2>
                            <h2 class="h2_padding_margin_0 font_kp" style="line-height: 17px; font-size: 10pt; font-weight: bold; margin: 0px"><!--নাবিক,মহিলা --> নাবিক ও এমওডিসি (নৌ) পদে ভর্তির আবেদনপত্র </h2>
                            <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> ব্যাচ: <?= SailorBatchs::getAllBatchSession($model->batch) ?></h4>
                            <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> <?= $model->center ? 'কেন্দ্র: ' . SailorCenters::getAllCenterSession($model->center) : '' ?> </h4> 
                            <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> <?= 'পরীক্ষার তারিখ: ' . $model->exam_date ?> </h4> 
              
                        </div>
                        <div class="col-lg-2"></div>
                    </div>
                <?php }  ?>

                <div class="row">
                    <table class="table table-striped table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Designation</th>
                                <th>Total Candidate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            if ($sailor) {
                                foreach ($sailor as $k => $value) {
                                    $total  += $value['candidate_count'];
                                    $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
                            ?>
                                    <tr>
                                        <td><?= ($k + 1) ?></td>
                                        <td><?= ($desig) ?></td>
                                        <td><?= ($value['candidate_count']) ?></td>
                                    </tr>
                                <?php } ?>
                                <tr>
                                    <td colspan="2" style="text-align: end;">Total</td>
                                    <td><?= $total  ?></td>
                                </tr>
                            <?php  } else { ?>
                                <tr>
                                    <td colspan="10"> No record found</td>
                                </tr>
                            <?php }
                            ?>


                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>