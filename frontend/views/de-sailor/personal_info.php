

<?php

use common\models\CanDesignation;
use common\static\Constants;
use common\static\StaticMethod;
use frontend\components\StepAndSupport;
use frontend\components\SupportNo;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

$this->title = 'Join Bangladesh Navy';

?>
<div class="data-form-area pb-120 " style="padding-top: 170px; background: #001731;">
    <div class="container">
        <?= SupportNo::widget(['steps' => [1], 'slug' => Yii::$app->getRequest()->getQueryParam('slug')]) ?>
        <div class="user_info__wrap">
            <?php
            echo StepAndSupport::widget(['steps' => [1, 2, 3], 'slug' => Yii::$app->getRequest()->getQueryParam('slug')])
            ?>
            <div class="row justify-content-center text-white" style="margin-top: 20px;">

                <div class="row">
                    <div class="col-lg-6">
                        <strong class="bangla_font"> আবেদনের শাখা : <span style="font-family: auto;"><?= CanDesignation::getAllDesignationSession($model->candidate_designation) ?></span></strong>
                    </div>
                    <div class="col-lg-6" style="text-align:right">
                        <strong class="bangla_font">আবেদন নম্বর : <?= $model->app_unique_id; //StaticMethod::convertToBanglaNumber($model->app_unique_id); 
                                                                    ?></strong>
                    </div>
                </div>

                <div id="preloader" style="position: fixed; top: 0; left: 0; inset: 0; z-index: 9999;  background: rgba(0, 0, 0, .5); display: none; justify-content: center; align-items:center">
                    <div class="spinner-grow" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>

                <div class="col-lg-12">
                    <?php $form = ActiveForm::begin([
                        'id' => 'can-personal-info',
                        // 'enableClientValidation' => true,
                        // 'validateOnBlur' => true,
                        'options' => [
                            'class' => 'data-form',
                            'enctype' => 'multipart/form-data'
                        ],
                        'fieldConfig' => [
                            'errorOptions' => [
                                'class' => 'invalid-feedback bangla_font',
                            ],
                            'options' => ['class' => '52', 'style' => 'margin:0px'],
                            'labelOptions' => ['class' => 'd-inline-block mb-1 bangla_font'],

                        ],
                    ]); ?>


                    <fieldset style="margin-top: 10px;">

                        <div class="block__title">
                            <h5 class="bangla_font">পরীক্ষা কেন্দ্র ও সময় </h5>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'district', ['enableAjaxValidation' => true])
                                        ->dropDownList($district_dropdown, ['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['district']); ?>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'gender', ['enableAjaxValidation' => true])
                                        ->dropDownList(StaticMethod::gender(), ['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['gender']); ?>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'exam_center_name', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0', 'readonly' => true])
                                        ->label($model->attributeLabelBangla()['exam_center_name']); ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font">ব্যক্তিগত তথ্য</h5>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'name', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['name']); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'name_bangla', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['name_bangla']); ?>
                                </div>
                            </div>

                             <div class="col-lg-12">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'permanent_phone', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['permanent_phone']); ?>
                                    <p class='bangla_font' style="color: red;margin: 0px; padding: 0px;font-size: 13px;">
                                        পরবর্তী সকল যোগাযোগের জন্য এই নাম্বর টি সচল রাখুন
                                    </p>
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'father_name', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['father_name']); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'father_name_bangla', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['father_name_bangla']); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'father_phone', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['father_phone']); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'father_nid', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['father_nid']); ?>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'father_occupation', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['father_occupation']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-lg-6">
                                <?= $form->field($model, 'mother_name', ['enableAjaxValidation' => true])
                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                    ->label($model->attributeLabelBangla()['mother_name']); ?>
                            </div>
                            <div class="col-lg-6">
                                <?= $form->field($model, 'mother_name_bangla', ['enableAjaxValidation' => true])
                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                    ->label($model->attributeLabelBangla()['mother_name_bangla']); ?>
                            </div>
                            <div class="col-lg-6">
                                <?= $form->field($model, 'mother_phone', ['enableAjaxValidation' => true])
                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                    ->label($model->attributeLabelBangla()['mother_phone']); ?>
                            </div>
                            <div class="col-lg-6">
                                <?= $form->field($model, 'mother_occupation', ['enableAjaxValidation' => true])
                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                    ->label($model->attributeLabelBangla()['mother_occupation']); ?>
                            </div>
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font">বর্তমান ঠিকানা</h5>
                        </div>
                        <div class="row">

                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'current_district', ['enableAjaxValidation' => true])
                                        ->dropDownList($all_district, ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['current_district']); ?>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'current_thana', ['enableAjaxValidation' => true])
                                        // ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->dropDownList($all_upazilas_current, ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['current_thana']); ?>
                                </div>
                            </div>


                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'current_union', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        // ->dropDownList($all_upazilas_unions_current, ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['current_union']); ?>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'current_post_office', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['current_post_office']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'current_village', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['current_village']); ?>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'current_word_no', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['current_word_no']); ?>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'current_post_code', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['current_post_code']); ?>
                                </div>
                            </div>
                            <!-- <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?php /* $form->field($model, 'current_phone', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['current_phone']); */ ?>
                                </div>
                            </div> -->
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font">স্থায়ী ঠিকানা</h5>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'permanent_district', ['enableAjaxValidation' => true])
                                        ->dropDownList($permanent_district_dropdown, ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['permanent_district']); ?>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'permanent_thana', ['enableAjaxValidation' => true])
                                        // ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->dropDownList($all_upazilas_permanent, ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['permanent_thana']); ?>
                                </div>
                            </div>


                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'permanent_union', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        // ->dropDownList($all_upazilas_unions_permanent, ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['permanent_union']); ?>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'permanent_post_office', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['permanent_post_office']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'permanent_village', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['permanent_village'] . '<span style="color: red;">(স্থায়ী  ঠিকানা)</span>'); ?>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'permanent_word_no', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['permanent_word_no']); ?>
                                </div>
                            </div>

                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'permanent_post_code', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['permanent_post_code']); ?>
                                </div>
                            </div>
                           
                        </div>
                    </fieldset>

                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font">অভিভাবকের তথ্য</h5>
                        </div>
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'guardian_name', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['guardian_name']); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'guardian_phone', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['guardian_phone']); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'guardian_relation', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['guardian_relation']); ?>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'guardian_occupation', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['guardian_occupation']); ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'guardian_address', ['enableAjaxValidation' => true])
                                        ->textarea(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['guardian_address']); ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>


                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font">অন্যান্য তথ্য</h5>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'dob', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['dob']); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- <div class="col-lg-4">
                            <div class="single_input__box">
                                <?php
                                //   $form->field($model, 'age_according_to_circular', ['enableAjaxValidation' => true])
                                //     ->textInput(['class' => 'form-control height_auto_margin_0'])
                                //     ->label($model->attributeLabelBangla()['age_according_to_circular']); 
                                ?>
                            </div>
                        </div> -->
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'religion', ['enableAjaxValidation' => true])
                                        ->dropDownList(StaticMethod::academicReligion(), ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['religion']); ?>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'marital_status', ['enableAjaxValidation' => true])
                                        ->dropDownList(StaticMethod::maritalStatus(), ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['marital_status']); ?>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'nationality', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0', 'style' => 'text-transform:capitalize'])
                                        ->label($model->attributeLabelBangla()['nationality']); ?>
                                </div>
                            </div>
                        </div>

                    </fieldset>


                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font mb-2">শিক্ষাগত যোগ্যতা</h5>
                        </div>
                        <!-- Class 5/8 -->
                        <div class="overflow__table mt-3 min-overflow-table">
                            <table class="table education_qualification__table text-white ">
                                <thead>
                                    <th class="bangla_font">পরীক্ষা/শিক্ষাগত যোগ্যতা </th>
                                    <th class="bangla_font"> শিক্ষা প্রতিষ্ঠানের নাম </th>
                                    <th class="bangla_font">রেজিস্ট্রেশন নং /রোল নং </th>
                                    <th class="bangla_font">পাশের সন </th>
                                    <th class="bangla_font">প্রাপ্ত নম্বর/জিপিএ </th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="bangla_font"> ৮ম শ্রেণী</td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'jsc_institute_name', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'jsc_reg_no', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'jsc_passing_year', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'jsc_gpa', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="overflow__table  min-overflow-table">
                            <table class="table education_qualification__table  text-white" style="min-width: 700px;">
                                <thead>
                                    <th class="bangla_font"> পরীক্ষা /শিক্ষাগত যোগ্যতা </th>
                                    <th class="bangla_font"> শিক্ষা প্রতিষ্ঠানের নাম </th>
                                    <th class="bangla_font"> গ্রুপ </th>
                                    <th class="bangla_font"> শিক্ষা বোর্ড </th>
                                    <th class="bangla_font"> রেজিস্ট্রেশন নং </th>
                                    <th class="bangla_font"> রোল নং </th>
                                    <th class="bangla_font"> পাশের সন </th>
                                    <th class="bangla_font"> ঐচ্ছিক বিষয়সমূহ </th>
                                    <th class="bangla_font"> প্রাপ্ত নম্বর/জিপিএ </th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="bangla_font"> এসএসসি / সমমান </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'ssc_institute', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                // $form->field($model, 'ssc_group', ['enableAjaxValidation' => true])
                                                //     ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                //     ->label(false);
                                                ?>
                                                <?= $form->field($model, 'ssc_group', ['inputTemplate' => strtoupper($model->ssc_group ?? '')])->hiddenInput()->label(false) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                //  $form->field($model, 'ssc_edu_board', ['enableAjaxValidation' => true])
                                                //     ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                //     ->label(false);
                                                ?>
                                                <?= $form->field($model, 'ssc_edu_board', ['inputTemplate' => strtoupper($model->ssc_edu_board ?? '')])->hiddenInput()->label(false) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                //  $form->field($model, 'ssc_reg_no', ['enableAjaxValidation' => true])
                                                //     ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                //     ->label(false); 
                                                ?>
                                                <?= $form->field($model, 'ssc_reg_no', ['inputTemplate' => strtoupper($model->ssc_reg_no ?? '')])->hiddenInput()->label(false) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php /* $form->field($model, 'ssc_roll_no', ['enableAjaxValidation' => true])
                                                ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                ->label(false); */ ?>
                                                <?= $form->field($model, 'ssc_roll_no', ['inputTemplate' => strtoupper($model->ssc_roll_no ?? '')])->hiddenInput()->label(false) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                //  $form->field($model, 'ssc_passing_year', ['enableAjaxValidation' => true])
                                                //     ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                //     ->label(false); 
                                                ?>
                                                <?= $form->field($model, 'ssc_passing_year', ['inputTemplate' => strtoupper($model->ssc_passing_year ?? '')])->hiddenInput()->label(false) ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'ssc_additional_subject', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                //  $form->field($model, 'ssc_gpa', ['enableAjaxValidation' => true])
                                                //     ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                //     ->label(false); 
                                                ?>
                                                <?= $form->field($model, 'ssc_gpa', ['inputTemplate' => strtoupper($model->ssc_gpa ?? '')])->hiddenInput()->label(false) ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="bangla_font"> এইচএসসি / সমমান </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'hsc_dip_institute', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                if (empty($model->hsc_dip_group)) {
                                                    echo $form->field($model, 'hsc_dip_group', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])->label(false);
                                                } else
                                                    echo $form->field($model, 'hsc_dip_group', ['inputTemplate' => strtoupper($model->hsc_dip_group ?? '')])->hiddenInput()->label(false);
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                if (empty($model->hsc_dip_board)) {
                                                    echo $form->field($model, 'hsc_dip_board', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])->label(false);
                                                } else
                                                    echo $form->field($model, 'hsc_dip_board', ['inputTemplate' => strtoupper($model->hsc_dip_board ?? '')])->hiddenInput()->label(false);
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                if (empty($model->hsc_dip_reg_no)) {
                                                    echo $form->field($model, 'hsc_dip_reg_no', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])->label(false);
                                                } else
                                                    echo $form->field($model, 'hsc_dip_reg_no', ['inputTemplate' => strtoupper($model->hsc_dip_reg_no ?? '')])->hiddenInput()->label(false);
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                if (empty($model->hsc_dip_roll_no)) {
                                                    echo $form->field($model, 'hsc_dip_roll_no', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                        ->label(false);
                                                } else
                                                    echo $form->field($model, 'hsc_dip_roll_no', ['inputTemplate' => strtoupper($model->hsc_dip_roll_no ?? '')])->hiddenInput()->label(false);
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                if (empty($model->hsc_dip_passing_year)) {
                                                    echo $form->field($model, 'hsc_dip_passing_year', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                        ->label(false);
                                                } else
                                                    echo $form->field($model, 'hsc_dip_passing_year', ['inputTemplate' => strtoupper($model->hsc_dip_passing_year ?? '')])->hiddenInput()->label(false);
                                                ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'hsc_dip_additional_subject', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?php
                                                if (empty($model->hsc_dip_gpa)) {
                                                    echo  $form->field($model, 'hsc_dip_gpa', ['enableAjaxValidation' => true])
                                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                        ->label(false);
                                                } else
                                                    echo $form->field($model, 'hsc_dip_gpa', ['inputTemplate' => strtoupper($model->hsc_dip_gpa ?? '')])->hiddenInput()->label(false);


                                                ?>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="overflow__table mt-3 min-overflow-table">
                            <table class="table education_qualification__table text-white ">
                                <thead>
                                    <th class="bangla_font">পরীক্ষা/শিক্ষাগত যোগ্যতা </th>
                                    <th class="bangla_font"> শিক্ষা প্রতিষ্ঠানের নাম </th>
                                    <th class="bangla_font"> কোর্স </th>
                                    <th class="bangla_font"> রেজিস্ট্রেশন নং / রোল নং </th>
                                    <th class="bangla_font"> সিজিপিএ/জিপিএ </th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="bangla_font"> <?= $model->candidate_type == 2 ? 'ডিপ্লোমা' : 'ট্রেড' ?> </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'diploma_trade_institute', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'diploma_trade_course', ['enableAjaxValidation' => true])
                                                    ->dropDownList($courses_list, ['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'diploma_trade_registration_roll', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'diploma_trade_gpa', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                    </fieldset>

                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font">খেলাধুলা / সংগীত / অন্য কোন বিষয়ে দক্ষতা (যদি থাকে-সনদপত্র সংযোজন
                                করতে
                                হবে) </h5>
                        </div>
                        <div class="overflow__table min-overflow-table">
                            <table class="table text-white ">
                                <thead>
                                    <th class="bangla_font">ক্রমিক</th>
                                    <th class="bangla_font"> প্রতিষ্ঠানের নাম </th>
                                    <th class="bangla_font">অংশগ্রহনকৃত বিষয়ের নাম </th>
                                    <th class="bangla_font">সন</th>
                                    <th class="bangla_font"> প্রাপ্ত স্থান/প্রশংসাপত্র/পদকের নাম </th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="bangla_font">১ </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'experience_one_institute', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'experience_one_subject', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'experience_one_year', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'experience_one_cert_name', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <td class="bangla_font">২ </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'experience_two_institute', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'experience_two_subject', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'experience_two_year', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="single_input__box">
                                                <?= $form->field($model, 'experience_two_cert_name', ['enableAjaxValidation' => true])
                                                    ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                    ->label(false); ?>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </fieldset>


                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font mb-2">

                                <!-- পিতা মুক্তিযোদ্ধা বা  নৌবাহিনীর কর্মরত / অবঃ / শহীদ /সামরিক
                                /অসামরিক
                                কর্মকর্তা বা কর্মচারী -->

                                মুক্তিযোদ্ধার সন্তান/ ক্ষুদ্র নৃ-গোষ্ঠি <!--গোষ্ঠী --> হলে তার বিবরণ (সংশ্লিষ্ট মূল কাগজপত্র সঙ্গে আনতে হবে)
                            </h5>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <!-- StaticMethod::yesNo() -->
                                    <?= $form->field($model, 'is_freedom_fighter', ['enableAjaxValidation' => true])
                                        ->dropDownList(StaticMethod::yesNoForFreedom(), ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['is_freedom_fighter']); ?>
                                </div>
                            </div>

                            <?php /* <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?php $is_show = ($model->is_freedom_fighter == Constants::YES) ? '' : 'none'; ?>
                                    <div class="row" id="relation_display_none" style="display: <?= $is_show; ?>;">
                                        <?= $form->field($model, 'freedom_fighter_relation', ['enableAjaxValidation' => true])
                                            ->dropDownList(StaticMethod::relationWithFreedomFighter(), ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                            ->label($model->attributeLabelBangla()['freedom_fighter_relation']); ?>
                                    </div>
                                </div>
                            </div> */ ?>

                        </div>



                        <?php /* 
                        <div class="row">
                            <div class="col-lg-3">
                                <div class="single_input__box">
                                    <?php
                                    if ($model->is_child_of_naval_officer == Constants::YES) {
                                        echo $form->field($model, 'is_child_of_naval_officer', ['enableAjaxValidation' => true])
                                            ->dropDownList([Constants::YES => 'Yes'], ['class' => 'form-control height_auto_margin_0'])
                                            ->label($model->attributeLabelBangla()['is_child_of_naval_officer']);
                                    } else {
                                        echo $form->field($model, 'is_child_of_naval_officer', ['enableAjaxValidation' => true])
                                            ->dropDownList([Constants::NO => 'No'], ['class' => 'form-control height_auto_margin_0'])
                                            ->label($model->attributeLabelBangla()['is_child_of_naval_officer']);
                                    }
                                    ?>

                                </div>
                            </div>
                            <div class="col-lg-9">
                                <?php $is_show = ($model->is_child_of_naval_officer == Constants::YES) ? '' : 'none'; ?>
                                <div class="row" id="is_naval_display" style="display: <?= $is_show; ?>;">
                                    <div class="col-lg-3">
                                        <div class="single_input__box">
                                            <?= $form->field($model, 'naval_father_name', ['enableAjaxValidation' => true])
                                                ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                ->label($model->attributeLabelBangla()['naval_father_name']); ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="single_input__box">
                                            <?= $form->field($model, 'naval_uniform_civil', ['enableAjaxValidation' => true])
                                                ->dropDownList(StaticMethod::navyUniformCivil(), ['prompt' => '', 'class' => 'form-control height_auto_margin_0'])
                                                ->label($model->attributeLabelBangla()['naval_uniform_civil']); ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="single_input__box">
                                            <?= $form->field($model, 'naval_office_no', ['enableAjaxValidation' => true])
                                                ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                ->label($model->attributeLabelBangla()['naval_office_no']); ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <div class="single_input__box">
                                            <?= $form->field($model, 'naval_rank', ['enableAjaxValidation' => true])
                                                ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                ->label($model->attributeLabelBangla()['naval_rank']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        */ ?>
                    </fieldset>



                    <?= $form->field($model, 'is_child_of_naval_officer', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput(['value' => 2, 'class' => '-', 'style' => 'padding: 0; margin: 0;']) ?>
                    <?= $form->field($model, 'is_anser_vdp', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput(['value' => 2, 'class' => '-', 'style' => 'padding: 0; margin: 0;']) ?>
                    <?= $form->field($model, 'is_khudro_jati_gosti', ['template' => '{input}', 'options' => ['tag' => null]])->hiddenInput(['value' => 2, 'class' => '-', 'style' => 'padding: 0; margin: 0;']) ?>

                    <?php /*               
                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font"> প্রার্থী আনসার / ভিডিপি'র সদস্য / ক্ষুদ্রজাতি গোষ্ঠী হলে তার
                                বিস্তারিত
                                বিবরণ (সংশ্লিষ্ট মূল কাগজপত্র ভর্তি কেন্দ্রে আনতে হবে)</h5>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'is_anser_vdp', ['enableAjaxValidation' => true])
                                        ->dropDownList(StaticMethod::yesNo(), ['prompt' => 'নির্বাচন করুন ', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['is_anser_vdp']); ?>
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <?php $is_show = ($model->is_anser_vdp == Constants::YES) ? '' : 'none'; ?>
                                <div class="row" id="anser_vdp_display_none" style="display: <?= $is_show; ?>;">
                                    <div class="col-lg-6">
                                        <div class="single_input__box">
                                            <?= $form->field($model, 'anser_vdp_rank', ['enableAjaxValidation' => true])
                                                ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                ->label($model->attributeLabelBangla()['anser_vdp_rank']); ?>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="single_input__box">
                                            <?= $form->field($model, 'anser_vdp_office_no', ['enableAjaxValidation' => true])
                                                ->textInput(['class' => 'form-control height_auto_margin_0'])
                                                ->label($model->attributeLabelBangla()['anser_vdp_office_no']); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'is_khudro_jati_gosti', ['enableAjaxValidation' => true])
                                        ->dropDownList(StaticMethod::yesNo(), ['prompt' => 'নির্বাচন করুন', 'class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['is_khudro_jati_gosti']); ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>

                    */ ?>

                    <fieldset>
                        <div class="block__title">
                            <h5 class="bangla_font"> ছবি সংযুক্ত করুন(প্রার্থীর বর্তমান পাসপোর্ট সাইজের ছবি) </h5>
                        </div>
                        <div class="row">
                            <div class="col-lg-4">
                                <div class="single_input__box">
                                    <?= $form->field($model, 'photo', ['enableAjaxValidation' => false])
                                        ->fileInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['photo']); ?>
                                    <p style="color: red;">Image size must be 300X300</p>
                                    <p style="color: red;"><a href="https://imageresizer.com/" target="_blank">Resize Image</a></p>
                                </div>
                            </div>
                            
                            <div class="holder" style="margin-top: 5px;">                              
                                 <?php 
                                      if ($model->photo && Yii::$app->r2Storage->fileExists($model->photo)) {
                                        echo '<img id="imgPreview" src=' . Yii::$app->r2Storage->fileUrl . $model->photo . ' alt="Image found">';
                                    } else echo '<img id="imgPreview" alt="" />'; 
                                ?>
                            </div>
                        </div>
                    </fieldset>

                    <div class="col-lg-12">
                        <div class="details-circular-btn d-flex justify-content-end">
                            <?= Html::submitButton(Yii::t('app', 'Continue'), ['class' => 'common-btn border-0']) ?>
                        </div>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(() => {

        $('#desailors-is_child_of_naval_officer').change(function() {
            let is_naval = $(this).val();
            if (is_naval == '<?= Constants::YES ?>') {
                $('#is_naval_display').show();
            } else {
                $('#is_naval_display').hide();
                $('#desailors-naval_father_name').val('');
                $('#desailors-naval_uniform_civil').val('');
                $('#desailors-naval_office_no').val('');
                $('#desailors-naval_rank').val('');
            }
        })

        $('#desailors-is_anser_vdp').change(function() {
            let anser_vdp_value = $(this).val();
            if (anser_vdp_value == '<?= Constants::YES ?>') {
                $('#anser_vdp_display_none').show();
            } else {
                $('#anser_vdp_display_none').hide();
                $('#desailors-anser_vdp_rank').val('');
                $('#desailors-anser_vdp_office_no').val('');
            }
        })


        $('#desailors-is_freedom_fighter').change(function() {
            let is_freedom = $(this).val();
            if (is_freedom == '<?= Constants::YES ?>') {
                $('#relation_display_none').show();
            } else {
                $('#relation_display_none').hide();
                $('#desailors-freedom_fighter_relation').val('');

            }
        })

        $("#desailors-photo").change(function() {
            const file = this.files[0];
            if (file) {
                let reader = new FileReader();
                reader.onload = function(event) {
                    $("#imgPreview").attr("src", event.target.result);
                };
                reader.readAsDataURL(file);
            }
        });



        function fetchUpazilasUnionsOptions(dist_thana, thana, data_option_id) {
            let url = '<?php echo Yii::$app->request->baseUrl . '/ajax/upazial-by-district' ?>';
            if (thana == 'union')
                url = '<?php echo Yii::$app->request->baseUrl . '/ajax/union-by-upazial' ?>';

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                data: {
                    dist_thana: dist_thana,
                },
                beforeSend: function() {
                    $('#preloader').show();
                },
                success: function(data) {
                    let html = '<option value="">নির্বাচন করুন</option>';
                    if (data?.status) {
                        $.each(data.data, function(text, value) {
                            html += '<option value="' + value + '">' + text + '</option>';
                        });
                    }
                    $('#' + data_option_id).html(html);
                    $('#preloader').hide();
                },
                error: function(xhr, status, error) {
                    console.error("Error: " + error);
                    $('#preloader').hide();
                }
            });
        }
        $('#desailors-current_district').change(function() {
            let dist = $(this).val();
            if (dist) fetchUpazilasUnionsOptions(dist, 'thana', 'desailors-current_thana');
        })
        
        // $('#sailors-current_thana').change(function() {
        //     let dist = $(this).val();
        //     if (dist) fetchUpazilasUnionsOptions(dist, 'union', 'sailors-current_union');
        // })

        $('#desailors-permanent_district').change(function() {
            let dist = $(this).val();
            if (dist) fetchUpazilasUnionsOptions(dist, 'thana', 'desailors-permanent_thana');
        })

        // $('#sailors-permanent_thana').change(function() {
        //     let dist = $(this).val();
        //     if (dist) fetchUpazilasUnionsOptions(dist, 'union', 'sailors-permanent_union');
        // })

    });
</script>