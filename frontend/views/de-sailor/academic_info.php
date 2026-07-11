<?php

use common\models\CanDesignation;
use common\static\StaticMethod;
use frontend\components\StepAndSupport;
use frontend\components\SupportNo;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Join Bangladesh Navy';

?>

<style>
    .mb-3.field-desailors-academic_info_already_used_in_ssc>.invalid-feedback {
        display: block;
    }

    .mb-3 .field-desailors-academic_info_already_used_in_jsc>.invalid-feedback {
        display: block;
    }

    .bodder_none td {
        border-bottom: none !important;
    }

    .bodder_none td>div {
        margin-bottom: 0 !important;
    }

    .field-desailors-academic_info_already_used_in_ssc.mb-3 {
        margin-bottom: 0 !important;
    }
</style>


<div class="data-form-area pb-120" style="padding-top: 170px; background: #001731;">
    <div class="container">

        <?= SupportNo::widget(['steps' => [1], 'slug' => Yii::$app->getRequest()->getQueryParam('slug')]) ?>

        <div class="user_info__wrap">

            <?php
            echo StepAndSupport::widget(['steps' => [1, 2], 'slug' => Yii::$app->getRequest()->getQueryParam('slug')])
            ?>
            <div class="row justify-content-center text-white" style="margin-top: 20px;">
                <div class="row gy-1">
                    <div class="col-lg-6">
                        <strong class="bangla_font"> আবেদনের শাখা : <span style="font-family: auto;"><?= CanDesignation::getAllDesignationSession($model->candidate_designation) ?></span></strong>
                    </div>
                    <div class="col-lg-6 text-lg-end">
                        <strong class="bangla_font">আবেদন নম্বর : <?= $model->app_unique_id; ?></strong>
                    </div>
                </div>
                <div class="col-lg-12">
                    <?php if (Yii::$app->session->hasFlash('error')) : ?>
                        <div class="alert alert-danger" role="alert">
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin([
                        'id' => 'can-academic-info',
                        // 'enableClientValidation' => true,
                        // 'validateOnBlur' => true,
                        'options' => [
                            'class' => 'data-form',
                        ],
                        'fieldConfig' => [],
                    ]); ?>
                    <div class="row">
                        <fieldset style="margin-top: 10px;">
                            <div class="block__title mt-3 mb-2" style="background: #001731">
                                <h5 class="bangla_font">শিক্ষাগত যোগ্যতা</h5>
                            </div>

                            <div class="overflow__table mt-3 min-overflow-table">
                                <table class="table education_qualification__table text-white">
                                    <tbody>
                                        <tr>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold;">
                                                পরীক্ষা/শিক্ষাগত যোগ্যতা </td>
                                            <td colspan="<?= $skipTeleTalkValidation ? 3 : 1 ?>" class="bangla_font" style="text-align: center; font-weight: bold;">
                                                শিক্ষা প্রতিষ্ঠানের নাম </td>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold; width: 24%">
                                                রেজিস্ট্রেশন নং /রোল নং </td>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold; width: 18%">
                                                পাশের সন </td>
                                            <td  class="bangla_font" style="text-align: center; font-weight: bold; width: 14%">
                                                প্রাপ্ত নম্বর/জিপিএ </td>
                                        </tr>

                                        <tr class="bodder_none">
                                            <td class="bangla_font">৮ম শ্রেণী</td>
                                            <td colspan="<?= $skipTeleTalkValidation ? 3 : 1 ?>">
                                                <?= $form->field($model, 'jsc_institute_name', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>
                                            <td>
                                                <?= $form->field($model, 'jsc_reg_no', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>
                                            <td>
                                                <?= $form->field($model, 'jsc_passing_year', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>
                                            <td >
                                                <?= $form->field($model, 'jsc_gpa', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>
                                        </tr>


                                        <tr>
                                            <td colspan="<?= $skipTeleTalkValidation ? 7 : 5 ?>" style="text-align: center;">
                                                <?= $form->field($model, 'academic_info_already_used_in_jsc', ['enableAjaxValidation' => true])->hiddenInput()->label(false); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold;">
                                                পরীক্ষা /শিক্ষাগত যোগ্যতা </td>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold;">
                                                শিক্ষা বোর্ড / শিক্ষা প্রতিষ্ঠানের নাম </td>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold;"> রোল
                                                নং </td>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold;">
                                                রেজিস্ট্রেশন নং </td>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold;">
                                                পাশের সন </td>

                                            <?php if ($skipTeleTalkValidation) { ?>
                                                <td class="bangla_font" style="text-align: center; font-weight: bold;">
                                                    প্রাপ্ত জিপিএ </td>
                                                <td class="bangla_font" style="text-align: center; font-weight: bold;">
                                                    বিভাগ </td>
                                            <?php } ?>

                                        </tr>
                                        <tr class="bodder_none">
                                            <td class="bangla_font"> এসএসসি / সমমান <span id="ssc_required"></span></td>
                                            <td>
                                                <?= $form->field($model, 'ssc_edu_board', ['enableAjaxValidation' => true])
                                                    ->dropDownList(StaticMethod::educationBoard(), ['class' => 'form-control height_auto_margin_0', 'prompt' => 'Select'])
                                                    ->label(false); ?>
                                            </td>
                                            <td><?= $form->field($model, 'ssc_roll_no', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?></td>
                                            <td>
                                                <?= $form->field($model, 'ssc_reg_no', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>
                                            <td>
                                                <?= $form->field($model, 'ssc_passing_year', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>

                                            <?php if ($skipTeleTalkValidation) { ?>
                                                <td style="width: 200px;">
                                                    <?= $form->field($model, 'ssc_gpa', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                        ->label(false); ?>
                                                </td>
                                                <td>
                                                    <?= $form->field($model, 'ssc_group', ['enableAjaxValidation' => true])
                                                        ->dropDownList(StaticMethod::academicGroupSsc(), ['prompt' => 'Select', 'class' => 'form-control height_auto_margin_0'])
                                                        ->label(false); ?>
                                                </td>
                                            <?php }  ?>
                                        </tr>


                                        <tr>
                                            <td colspan="<?= $skipTeleTalkValidation ? 7 : 5 ?>" style="text-align: center;">
                                                <?= $form->field($model, 'academic_info_already_used_in_ssc', ['enableAjaxValidation' => true])->hiddenInput()->label(false); ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="bangla_font">এইচএসসি / সমমান</td>
                                            <td> <?= $form->field($model, 'hsc_dip_board', ['enableAjaxValidation' => true])
                                                        ->dropDownList(StaticMethod::educationBoard(), ['class' => 'form-control height_auto_margin_0', 'prompt' => 'Select'])
                                                        ->label(false); ?>
                                            </td>
                                            <td>
                                                <?= $form->field($model, 'hsc_dip_roll_no', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>
                                            <td>
                                                <?= $form->field($model, 'hsc_dip_reg_no', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>

                                            <td>
                                                <?= $form->field($model, 'hsc_dip_passing_year', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>

                                            <?php  if ($skipTeleTalkValidation) { ?>
                                                <td>
                                                    <?= $form->field($model, 'hsc_dip_gpa', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                        ->label(false); ?>
                                                </td>
                                                <td>
                                                    <?= $form->field($model, 'hsc_dip_group', ['enableAjaxValidation' => true])
                                                        ->dropDownList(StaticMethod::academicGroupHsc(), ['prompt' => 'Select', 'class' => 'form-control height_auto_margin_0'])
                                                        ->label(false); ?>
                                                </td>
                                            <?php } ?>
                                        </tr>


                                        <tr>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold;">
                                                পরীক্ষা/শিক্ষাগত যোগ্যতা </td>
                                            <td colspan="<?= $skipTeleTalkValidation ? 3 : 1 ?>" class="bangla_font" style="text-align: center; font-weight: bold;">
                                                শিক্ষা প্রতিষ্ঠানের নাম </td>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold; width: 24%">
                                                কোর্স</td>
                                            <td class="bangla_font" style="text-align: center; font-weight: bold; width: 18%">
                                                রেজিস্ট্রেশন নং / রোল নং </td>
                                            <td  class="bangla_font" style="text-align: center; font-weight: bold; width: 14%">
                                                সিজিপিএ/জিপিএ </td>
                                        </tr>

                                        <tr>
                                            <td class="bangla_font"><?= $model->candidate_type == 2 ? 'ডিপ্লোমা' : 'ট্রেড কোর্স' ?> </td>
                                            <td colspan="<?= $skipTeleTalkValidation ? 3 : 1 ?>"> <?= $form->field($model, 'diploma_trade_institute', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                        ->label(false); ?>
                                            </td>
                                            <td>
                                                <?= $form->field($model, 'diploma_trade_course', ['enableAjaxValidation' => true])
                                                    ->dropDownList($courses_list, ['class' => 'form-control height_auto_margin_0', 'prompt' => 'Select'])
                                                    ->label(false); ?>
                                            </td>
                                            <td>
                                                <?= $form->field($model, 'diploma_trade_registration_roll', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>

                                            <td>
                                                <?= $form->field($model, 'diploma_trade_gpa', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </td>
                                        </tr>
                                        
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    </div>
                    <div class="col-lg-12">
                        <div class="details-circular-btn d-flex justify-content-end">
                            <?= Html::submitButton(Yii::t('app', 'Continue'), ['class' => 'common-btn bg-yellow modified-btn']) ?>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>