<?php

/** @var yii\web\View $this */

use common\models\Districts;
use common\static\StaticMethod;
use frontend\components\SupportNo;
use yii\bootstrap5\ActiveForm;
use yii\jui\DatePicker;
use kartik\select2\Select2;
use yii\bootstrap5\Html;

$this->title = 'Join Bangladesh Navy';
?>
<style>
    .signup-step-container .form-select {
        height: auto;
    }
</style>
<script>
    $(document).ready(function() {



        $('#caneligibilitycheckinfo-candidate_type').change(function() {
            let candidate_type = $(this).val(); // Candidate type      
            let posso_kotha_dept_candidate = [2, 3] // Posso kotha and departmental candidate type       
            if (posso_kotha_dept_candidate.includes(Number(candidate_type))) {
                $("#posso_kota_dept_can_block").show()
            } else {
                $('#caneligibilitycheckinfo-p_o_no').val('')
                $('#caneligibilitycheckinfo-rank').val('')
                $("#posso_kota_dept_can_block").hide();
            }

            $.ajax({
                url: '<?php echo Yii::$app->request->baseUrl . '/ajax/district-by-candidate-type' ?>',
                type: 'POST',
                dataType: 'json',
                data: {
                    candidate_type: candidate_type
                },
                beforeSend: function() {
                    /*$('#loader_batchId').show('slow');*/
                },
                success: function(data) {
                    let district_list = [
                        '<option value>Select <?= $model->getAttributeLabel('district') ?> </option>'
                    ]
                    if (data) {
                        data.map((value, key) => {
                            let option2 = '<option value=' + value.slug + '>' + value
                                .name_en + '</option>';
                            district_list.push(option2)
                        })
                    }


                    /// console.log(typeof data,'dist')
                    /* $('#loader_batchId').hide('slow');*/
                    $('#caneligibilitycheckinfo-district').html(district_list.join(''));
                }
            });
        })

        /// height calculation 
        $('#caneligibilitycheckinfo-height_feet').keyup(function() {
            let feet = parseInt($(this).val());
            if (isNaN(feet)) feet = 0;
            let inch = parseInt($('#caneligibilitycheckinfo-height_inch').val());
            if (isNaN(inch)) inch = 0;
            let return_data = heihtCalculation(feet, inch);
            $('#height_calculated').html(return_data.calculate_result)

        })
        $('#caneligibilitycheckinfo-height_inch').keyup(function() {
            let inch = parseInt($(this).val());
            if (isNaN(inch)) inch = 0;
            let feet = parseInt($('#caneligibilitycheckinfo-height_feet').val());
            if (isNaN(feet)) feet = 0;
            let return_data = heihtCalculation(feet, inch);
            $('#height_calculated').html(return_data.calculate_result);
        })

    })

    function heihtCalculation(feet, inch) {
        let feet_to_inch = 0;
        if (feet > 0)
            feet_to_inch = feet * 12;
        let total_inch = feet_to_inch + inch;
        let height_in_cm = total_inch * 2.54;
        return {
            'feet': feet,
            'inch': inch,
            'total_inch': feet_to_inch,
            'height_in_cm': height_in_cm,
            'calculate_result': `${feet} feet ${inch} inch or <strong>${height_in_cm.toFixed(2)} CM</strong>`
        }
    }
</script>
<section class="signup-step-container pt-120 pb-120" style="background-color: #001731;">
    <div class="container">
         <?= SupportNo::widget(['steps' => [1], 'slug' => Yii::$app->getRequest()->getQueryParam('slug'), 'show_form_text'=>false]) ?>
        <div class="signup__wrap">
            
            <div class="section-title section-title-white">
                <h1 style="text-transform:uppercase; text-align: center;">Check Eligibility</h1>
                <p class="mt-1" style="text-align: center;">Joining and Serving In Bangladesh Navy Is not Just a Career. It is a Way of Life.</p>
            </div>
            
           
            <div class="wizard" style="margin-top: 40px;">
                <div class="wizard-inner py-lg-2">
                    <ul class="d-flex flex-wrap flex-md-nowrap">
                        <li class="active single__step flex-grow-1">
                            <div class="index-count">
                                <span>
                                    01
                                </span>
                            </div>
                            <div class="step_disc">
                                <h5>Personal Details</h5>
                            </div>
                        </li>
                        <li class="single__step flex-grow-1">
                            <div class="index-count">
                                <span>
                                    02
                                </span>
                            </div>
                            <div class="step_disc">
                                <h5>Acadamic Details</h5>
                            </div>
                        </li>
                        <li class="single__step flex-grow-1">
                            <div class="index-count">
                                <span>
                                    03
                                </span>
                            </div>
                            <div class="step_disc">
                                <h5>Available Position</h5>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>


            <?php $form = ActiveForm::begin([
                'id' => 'candidate-eligibility-personal-info',
                'enableClientValidation' => false,
                'validateOnBlur' => false,
                'options' => [
                    'class' => 'eligilbe-form-wrap mt-4'
                ],
                'fieldConfig' => [
                    // 'template' => "{label}\n<div class=\"col-lg-8\">\n{input}\n{hint}\n{error}\n</div>",
                    'options' => ['class' => '', 'style' => 'margin:0px'],
                    'horizontalCssClasses' => [
                        // 'label' => 'col-lg-4',
                        'offset' => '',
                        'wrapper' => '',
                        'hint' => '',
                    ],
                ],
            ]); ?>

            <?php /*
            <div class="row">
                <div class="col-md-12">
                    <div class="form-box">
                        <div class="single_input__box">
                            <?= $form->field($model, 'candidate_type')
                                ->dropDownList(StaticMethod::candidateTypeForEligibilityCheck(), [
                                    'id' => 'caneligibilitycheckinfo-candidate_type',
                                    //'prompt' => 'Select ' . $model->getAttributeLabel('candidate_type')
                                ])
                                ->label(false); ?>
                        </div>
                    </div>
                </div>
            </div>
            */ ?>

            <?= $form->field($model, 'candidate_type')->hiddenInput(['value' => 1])->label(false) ?>

            <?php
            /*<div class="row" id="posso_kota_dept_can_block" style="display: none;">
                <div class="col-md-6">
                    <div class="single_input__box">
                        <?= $form->field($model, 'p_o_no', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel('p_o_no')])->label(false) ?>
                        <i class='bx bx-ruler'></i>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="single_input__box">
                        <?= $form->field($model, 'rank', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel('rank')])->label(false) ?>
                        <i class='bx bx-ruler'></i>
                    </div>
                </div>
            </div> 
            */ ?>


            <div class="row">
                <div class="col-md-6">
                    <div class="single_input__box">
                        <?= $form->field($model, 'dob')->widget(DatePicker::classname(), [
                            'options' => [
                                'class' => 'form-control',
                                'placeholder' => $model->getAttributeLabel('dob'),
                                'readonly' => true,
                            ],
                            'language' => 'en',
                            'dateFormat' => 'dd-MM-yyyy',
                            'clientOptions' => [
                                'yearRange' => date('Y', strtotime('-35 year')) . ':' . date('Y', strtotime('-15 year')),
                                'changeMonth' => true,
                                'changeYear' => true,
                                //'todayHighlight' => true,

                            ],
                        ])->label(false) ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="single_input__box">
                        <?= $form->field($model, 'gender')
                            ->dropDownList(StaticMethod::gender(), ['prompt' => 'Select ' . $model->getAttributeLabel('gender')])
                            ->label(false); ?>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="single_input__box">
                        <?= $form->field($model, 'nationality')
                            ->dropDownList(['bangladeshi' => 'Bangladeshi'])
                            ->label(false); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="single_input__box">
                        <?= $form->field($model, 'marital_status')
                            ->dropDownList(StaticMethod::maritalStatus(), ['prompt' => 'Select ' . $model->getAttributeLabel('marital_status')])
                            ->label(false); ?>
                    </div>
                </div>
            </div>


            <!-- Height -->
            <!-- <div class="row">
                    <div class="col-lg-12">
                        <div class="height-count d-flex mt-4 mb-4">
                            <h3 class="me-3 text-white">Height</h3>
                            <ul>
                                <li id="height_calculated">0 feet 0 inch</li>

                            </ul>
                        </div>
                    </div>
                </div> -->
            <div class="row">
                <div class="col-md-6">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="height-count d-flex mt-4 mb-4">
                                <h3 class="me-3 text-white">Height</h3>
                                <ul>
                                    <li id="height_calculated">0 feet 0 inch</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box">
                                <?= $form->field($model, 'height_feet')->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel('height_feet')])->label(false) ?>
                                <i class='bx bx-ruler'></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box">
                                <?= $form->field($model, 'height_inch')->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel('height_inch')])->label(false) ?>
                                <i class='bx bx-ruler'></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="height-count d-flex mt-4 mb-3">
                        <h3 class="text-white">Chest Measurement</h3>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <!-- <div class="row">
                                <div class="col-lg-12">
                                    <div class="height-count d-flex mt-4 mb-4">
                                        <h3 class="me-3 text-white">Chest</h3>
                                            <ul>
                                                <li id="height_calculated">0 feet 0 inch</li>
                                            </ul>
                                    </div>
                                </div>
                            </div> -->
                            <div class="single_input__box mt-0">
                                <?= $form->field($model, 'chest_normal')->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel('chest_normal')])->label(false) ?>
                                <i class='bx bx-ruler'></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- <div class="row">
                                <div class="col-lg-12">
                                    <div class="height-count d-flex mt-4 mb-4">
                                        <h3 class="me-3 text-white">Height</h3>
                                            <ul>
                                                <li id="height_calculated">0 feet 0 inch</li>

                                            </ul>
                                    </div>
                                </div>
                            </div> -->
                            <div class="single_input__box mt-0">
                                <?= $form->field($model, 'chest_expanded')->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel('chest_expanded')])->label(false) ?>
                                <i class='bx bx-ruler'></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="single_input__box">
                        <?= $form->field($model, 'eye_status')
                            ->dropDownList(StaticMethod::candidateEyeStatus(), ['prompt' => 'Select ' . $model->getAttributeLabel('eye_status')])
                            ->label(false); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="single_input__box">
                        <?php
                        //  echo $form->field($model, 'district')
                        //     ->dropDownList(Districts::getAllActiveDistrict(), ['style' => ['height' => 'auto', 'max-height' => '200px', 'overflow-x' => 'hidden'], 'prompt' => 'Select ' . $model->getAttributeLabel('district')])
                        //     ->label(false);
                        ?>
                        <?php echo $form->field($model, 'district')->widget(Select2::classname(), [
                            'data' => Districts::getAllActiveDistrict(),
                            'value' => $model->district,
                            'language' => 'en',
                            'options' => ['multiple' => false, 'placeholder' => 'Select ' . $model->getAttributeLabel('district')],
                            'pluginOptions' => [
                                'allowClear' => true,
                            ],
                        ])->label(false);  ?>
                        <span class="bangla_font" style="color: red; font-size: 18px; margin-top: 10px; display: block;">আবেদনের সময় স্থায়ী জেলা পরিবর্তন
                            করা যাবে না।</span>
                    </div>
                </div>
            </div>

            <!-- Next Button -->
            <div class="col-lg-12">
                <div class="form-check-btn-wrap d-flex justify-content-end">
                    <?= Html::submitButton(Yii::t('app', 'Continue'), ['class' => 'common-btn bg-yellow']) ?>
                </div>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
</section>






<!-- <div class="circular-area " id="join" style="background-color: #fff; padding-top: 60px; padding-bottom: 60px;">
    <div class="container">
        <div class="row">
            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <div class="section-title-circular text-center" style="background-color: #FCA900;">
                    <h1 style="color: #1E427E;">Current Circular Sailor</h1>
                </div>
            </div>
        </div>
        <div class="single-circular">
            <div class="row mt-5 mb-4">
                <div class="col-lg-12">
                    <div class="circular-title text-center">
                        <h2><u>Sailor & Mode Batch 2023 A</u></h2>
                    </div>
                </div>
            </div>
            <div class="row justify-content-between">
                <div class="col-xxl-3 col-xl-3 col-lg-3 col-sm-12 col-12">
                    <div class="circular-img">
                        <a href="#"><img src="<?= Yii::getAlias('@web'); ?>/navy/images/cercular-details-2.png" alt=""></a>
                        <div class="details-circular-btn mt-3 d-flex justify-content-center">
                            <a class="common-btn" href="circular-details.html">Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-8 col-xl-8 col-lg-8 col-sm-12 col-12 mobt-60">
                    <table class="table table-striped circular-table">
                        <tbody>
                            <tr>
                                <th scope="row" width="100%">Start Date: 22 October 2022</th>
                            </tr>
                            <tr>
                                <th scope="row" width="100%" class="text-danger">End Date: 22 October 2022</th>
                            </tr>
                            <tr>
                                <th scope="row" width="100%">
                                    <div class="course-apply-btn-wrap">
                                        <a class="apply-btn" href="https://joinnavy.navy.mil.bd/site/eligible?id=1"><i class="bx bx-file"></i> Apply Now</a>
                                    </div>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> -->

<!-- Circular Mobile End -->
<!-- Join Area Start -->
<div class="join-area" style="background:#FFE500;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                <div class="section-title">
                    <h1>Confused about Your Eligibility?</h1>
                    <p class="mt-4">You can check your eligibility right here before applying to any position</p>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12 mobt-24">
                <div class="join-btn-wrap text-lg-end wow fadeIn" data-wow-duration="2s" data-wow-delay=".6s">
                    <button type="button" class="common-btn bg-black" style="border:none;" data-bs-toggle="modal" data-bs-target="#staticBackdrop-eligibility">
                        Check Eligibility
                    </button>
                    <!-- Modal -->
                    <div class="modal fade text-center" id="staticBackdrop-eligibility" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog eligibility">
                            <div class="modal-content">
                                <div class="modal-header position-relative">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; color:#EF3F2E; opacity:1; font-size:23px;"><i class="bi bi-x-lg"></i></button>
                                    <span class="position-absolute">Close & Check Eligibility</span>
                                </div>
                                <div class="modal-body">
                                    <img src="<?= Yii::getAlias('@web'); ?>/navy/images/eligibility.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>