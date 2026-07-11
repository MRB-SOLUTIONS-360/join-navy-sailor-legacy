<?php

/** @var yii\web\View $this */

use common\models\Subjects;
use common\static\Constants;
use common\static\StaticMethod;
use frontend\components\SupportNo;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

$this->title = 'Check Eligibility';
?>

<script>
    $(document).ready(function() {
        $('#caneligibilitycheckinfo-ssc_equv_group').change(function() {
            let ac_group = $(this).val();
            let group_list = [
                '<?= Constants::AC_GROUP_SCIENCE; ?>',
                '<?= Constants::AC_GROUP_VOCATIONAL; ?>',
            ];

            if (group_list.includes(ac_group.toString())) {
                $('#biology_include_trade_complete').show();
                if (ac_group.toString() == '<?= Constants::AC_GROUP_VOCATIONAL; ?>') {
                    $('#i2').prop('checked', true).trigger('change');
                    $('#is_trade_course_complete').hide();
                    $('#trade_course_exp').show();
                    $('#diploma_course_visible').show();
                } else {
                    $('#is_trade_course_complete').show();
                    $('#i2, #i3').prop('checked', false).trigger('change');
                    $('#trade_course_exp').hide();
                    $('#diploma_course_visible').hide();
                }

            } else {
                // $( "#x" ).prop( "checked", false );
                $('#is_trade_course_complete').show();
                $('#biology_include_trade_complete').hide()
                $('#trade_course_exp').hide();
                 $('#diploma_course_visible').hide();
                $('#i2,#i3').prop('checked', false).trigger('change');
            }
        })

        // is_trade_course_complete_common_class
        $(".is_trade_course_complete_common_class").click(function() {
            var trade_course_yes = $(".is_trade_course_complete_common_class:checked").val();
            if (trade_course_yes == '<?= Constants::YES ?>') {
                $('#diploma_course_visible').show();
                $('#trade_course_exp').show();
            } else {
                $('#diploma_course_visible').hide();

                $('#trade_course_exp').hide();

                $('#i4, #i5').prop('checked', false).trigger('change');

                $('#caneligibilitycheckinfo-trade_course_subject').val('');

            }
        })

        $(".hsc_eqv_ac_type_common_class").click(function() {
            var ac_type = $(".hsc_eqv_ac_type_common_class:checked").val();

            // if (ac_type == 1) {

            // }
            if (ac_type) {
                $.ajax({
                    url: '<?php echo Yii::$app->request->baseUrl . '/ajax/hsc-diploma-ac-group' ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        ac_type: ac_type
                    },
                    beforeSend: function() {
                        /*$('#loader_batchId').show('slow');*/
                    },
                    success: function(data) {

                        let select_text = '<?= $model->getAttributeLabel('hsc_equv_group') ?>';
                        let hsc_diploma_result = 'HSC Result';

                        if ($(".hsc_eqv_ac_type_common_class:checked").val() == 2) {
                            select_text = 'Diploma Course';
                            hsc_diploma_result = 'Diploma Result';
                        }

                        $('#caneligibilitycheckinfo-hsc_equv_result').attr('placeholder', hsc_diploma_result)


                        let group_list = [
                            '<option value>Select ' + select_text + ' </option>'
                        ]
                        if (data) {
                            for (const [key, value] of Object.entries(data)) {
                                let option2 = '<option value=' + key + '>' + value +
                                    '</option>';
                                group_list.push(option2)
                            }
                        }
                        // /* $('#loader_batchId').hide('slow');*/
                        $('#caneligibilitycheckinfo-hsc_equv_group').html(group_list.join(''));

                    }
                });
            }
        });
    })
</script>


<section class="signup-step-container pt-120 pb-120" style="background-color: #001731;">
    <div class="container">
        <?= SupportNo::widget(['steps' => [1], 'slug' => Yii::$app->getRequest()->getQueryParam('slug'), 'show_form_text'=>false]) ?>
        <div class="signup__wrap">
            <div class="">
                   
                <div class="section-title section-title-white">
                    <h1 style="text-transform:uppercase; text-align: center;">Check Eligibility</h1>
                    <p class="mt-1" style="text-align: center;">Joining and Serving In Bangladesh Navy Is not Just a
                        Career. It is a Way
                        of Life.</p>
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
                            <li class="active single__step flex-grow-1">
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
                  
                <?php

                // $subject_diploma =  Subjects::getAllActiveSubjectBySubjectType(subject_type: Constants::SUBJECT_TYPE_TRADE, candidate_type: Constants::CANDIDATE_DE_SAILOR_DOCKYARD);
                $subject_diploma =  Subjects::getAllActiveSubjectBySubjectType(subject_type: Constants::SUBJECT_TYPE_TRADE);
                $subject_diploma = ArrayHelper::map($subject_diploma, 'id', 'name_en');

                $form = ActiveForm::begin([
                    'id' => 'candidate-eligibility-academic-info',
                    // 'enableClientValidation' => true,
                    // 'validateOnBlur' => true,
                    'options' => [
                        'class' => 'eligilbe-form-wrap'
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


                <div class="row">
                   
                    <div class="col-md-12">
                        <div class="form-box-radio mb-0 mt-5">
                            <label class="form-label d-inline-block text-white" for="">JSC Result</label>
                            <ul class="radio-wrap d-flex">
                                <?php /*
                                 <li>
                                    <input type="radio" id="f-option" name="selector">
                                    <label for="f-option">Passed</label>
                                </li>
                                <li>
                                    <input type="radio" id="s-option" name="selector">
                                    <label for="s-option">Failed</label>
                                </li>   */ ?>

                                <?php
                                echo $form->field($model, 'jsc_result')->inline(true)
                                    ->radioList(StaticMethod::passFail(), ['class' => 'fff'])
                                    ->label(false);
                                ?>
                            </ul>
                        </div>
                    </div>

                    <div style='margin: 20px 10px; margin-top: 22px; border-bottom: 1px solid #80808066;' }></div>

                    <div class="col-md-12 ">
                        <label class="form-label d-inline-block text-white mb-0" for="">SSC/Equivalent</label>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mt-3">
                            <div class="form-box">
                                <?= $form->field($model, 'ssc_equv_group', ['enableAjaxValidation' => true])
                                    ->dropDownList(StaticMethod::academicGroupSsc(), ['prompt' => 'Select ' . $model->getAttributeLabel('ssc_equv_group')])
                                    ->label(false); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <div class="form-box">
                                <?= $form->field($model, 'ssc_equv_result', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel('ssc_equv_result')])->label(false) ?>
                                <i class='bx bx-ruler'></i>
                            </div>
                        </div>
                    </div>

                    <div style='margin: 20px 10px; margin-top: 22px; border-bottom: 1px solid #80808066;' }></div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-box-radio" id="is_trade_course_complete" style="display: block;">
                                        <label class="form-label d-inline-block text-white" for="">Trade Course
                                            Completed(Minimum 6 Months)?</label>
                                        <ul class="radio-wrap d-flex">
                                            <?= $form->field($model, 'ssc_equv_is_trade_course_complete', ['enableAjaxValidation' => true])->inline(true)
                                                ->radioList(StaticMethod::yesNo(), [
                                                    'class' => 'fff',
                                                    'itemOptions' => ['class' => 'is_trade_course_complete_common_class'],
                                                    // 'item' => function ($index, $label, $name, $checked, $value) {
                                                    //         return Html::radio($name, $checked, [
                                                    //             'label' => $label,
                                                    //             'value' => $value,
                                                    //             'id' => 'ssc_equv_is_trade_course_complete_' . $value,
                                                    //             'class' => 'is_trade_course_complete_common_class'
                                                    //         ]);
                                                    //     }
                                                ])
                                                ->label(false); ?>
                                        </ul>
                                    </div>

                                    <div id="diploma_course_visible" class="mt-3" style="display: none;">
                                        <div class="form-box">
                                            <?= $form->field($model, 'trade_course_subject', ['enableAjaxValidation' => true])
                                                ->dropDownList($subject_diploma, ['prompt' => 'Select ' . $model->getAttributeLabel('trade_course_subject')])
                                                ->label(false); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3" id="trade_course_exp" style="display: none;">
                                    <div class="form-box-radio" id="is_trade_course_complete" style="display: block;">
                                        <label class="form-label d-inline-block text-white" for=""> Do You have trade course work experience ? </label>
                                        <ul class="radio-wrap d-flex">
                                            <?= $form->field($model, 'have_trade_course_experience', ['enableAjaxValidation' => true])->inline(true)
                                                ->radioList(StaticMethod::yesNo(), [
                                                    'class' => 'fff',
                                                    'itemOptions' => ['class' => 'is_trade_course_complete_common_class'],

                                                ])
                                                ->label(false); ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="col-md-6" style="display: none;" id="biology_include_trade_complete">
                            <div class="form-box-radio">
                                <label class="form-label d-inline-block text-white" for="">Biology Included</label>

                                <ul class="radio-wrap d-flex">
                                    <?= $form->field($model, 'ssc_equv_is_biology_include', ['enableAjaxValidation' => true])->inline(true)
                                        ->radioList(StaticMethod::yesNo(), ['class' => 'fff'])
                                        ->label(false); ?>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div style='margin: 20px 10px; margin-top: 22px; border-bottom: 1px solid #80808066;' }></div>

                    <div class="col-md-12">
                        <div class="row align-items-end">

                            <div class="col-md-6">
                                <div class="form-box-radio">
                                    <label class="form-label d-inline-block text-white" for="">HSC/Diploma</label>
                                    <ul class="radio-wrap d-flex">
                                        <?= $form->field($model, 'hsc_equv_academic_type')->inline(true)
                                            ->radioList(StaticMethod::academicTypeHscDiploma(), ['class' => 'fff', 'itemOptions' => ['class' => 'hsc_eqv_ac_type_common_class']])
                                            ->label(false); ?>
                                    </ul>
                                </div>

                                <div class="form-box mt-3">
                                    <?= $form->field($model, 'hsc_equv_group')->inline(true)
                                        ->dropDownList(StaticMethod::academicGroupHsc(), ['prompt' => 'Select ' . $model->getAttributeLabel('hsc_equv_group')])
                                        ->label(false); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-box mt-3">
                                    <?= $form->field($model, 'hsc_equv_result', ['enableAjaxValidation' => true])->textInput(['maxlength' => true, 'placeholder' => $model->getAttributeLabel('hsc_equv_result')])->label(false) ?>
                                    <i class='bx bx-ruler'></i>
                                </div>
                            </div>

                            <!-- <div class="col-md-6 mt-3">
                                
                            </div> -->

                            <!-- <div class="col-md-6">
                                    <div class="form-box-radio">
                                        <label class="form-label d-inline-block text-white" for="">Biology Included?</label>

                                        <ul class="radio-wrap d-flex">
                                            <li>
                                                <input type="radio" id="f5-option" name="selector">
                                                <label for="f5-option">Yes</label>
                                            </li>
                                            <li>
                                                <input type="radio" id="s5-option" name="selector">
                                                <label for="s5-option">No</label>
                                            </li>
                                        </ul>
                                    </div>
                                </div> -->
                        </div>
                    </div>


                    <!-- Next Button -->
                    <div class="col-lg-12">
                        <div class="form-check-btn-wrap d-flex justify-content-end">
                            <?= Html::submitButton(Yii::t('app', 'Continue'), ['class' => 'common-btn bg-yellow']) ?>
                        </div>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</section>


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