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

$this->title =  'Center  candidate';
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Sailor Report</a></li>
            <li class="breadcrumb-item active" aria-current="page">Center candidate</li>
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
                            <div class="form-group mt-3">
                                <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>

                 

                <?php
                if ($sailor) {
                ?>
                    <div class="row" style="text-align: center;">
                        <div class="col-lg-2"> </div>
                        <div class="col-lg-8">
                            <img src="<?= Yii::getAlias('@rootMediaShow'); ?>/media/main_logo.png" alt="QR not found" style="width:80px; text-align: center; margin: 0 auto;">
                            <h2 class="h2_padding_margin_0 font_kp" style="font-size: 10pt; font-weight: bold; margin: 0px; ">বাংলাদেশ নৌবাহিনী</h2>
                            <h2 class="h2_padding_margin_0 font_kp" style="line-height: 17px; font-size: 10pt; font-weight: bold; margin: 0px"><!--নাবিক,মহিলা --> নাবিক ও এমওডিসি (নৌ) পদে ভর্তির আবেদনপত্র </h2>
                            <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> ব্যাচ: <?= SailorBatchs::getAllBatchSession($model->batch) ?></h4>
                            <!-- <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> <? // $model->center ? 'কেন্দ্র: ' . SailorCenters::getAllCenterSession($model->center) : '' 
                                                                                                                    ?></h4> -->
                            
                            
                        </div>
                        <div class="col-lg-2"></div>
                    </div>
                <?php }  ?>

                 
                <div class="row">
                    <table class="table table-striped table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Name</th>

                                <th>Designation</th>
                                <th>SSC Board</th>
                                <th>SSC Reg No</th>
                                <th>SSC Roll No</th>
                                <th>SSC Passing Year</th>
                                <th>Serial No</th>
                                <th>App Unique Id</th>
                                
                                <th>Application Status</th>
                                <th>Eligible District</th>
                               
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            if ($sailor) {
                                foreach ($sailor as $k => $value) {
                                    $total  += 1;
                                    $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
                            ?>
                                    <tr>
                                        <td><?= ($k + 1) ?></td>
                                        <td><?= ($value['name']) ?></td>
                                        <td><?= ($desig) ?></td>
                                        <td><?= ($value['ssc_edu_board']) ?></td>
                                        <td><?= ($value['ssc_reg_no']) ?></td>
                                        <td><?= ($value['ssc_roll_no']) ?></td>
                                        <td><?= ($value['ssc_passing_year']) ?></td>
                                        <td><?= ($value['serial_no']) ?></td>
                                        <td><?= ($value['app_unique_id']) ?></td>                                      
                                        <td><?= ($value['application_status'] ==1 ? '<span style="color: green;">Active</span>' : '<span style="color: red;">Inactive</span>') ?></td>
                                        <td><?= ($value['eligible_district']) ?></td>
                                     
                                    </tr>
                                <?php } ?>
                                
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