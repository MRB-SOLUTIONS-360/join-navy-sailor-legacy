<?php

use common\models\CanDesignation;
use common\models\SailorCenters;
use common\static\StaticMethod;
use Mpdf\Tag\Center;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;

/** @var yii\web\View $this */
/** @var common\models\Sailors $model */
/** @var yii\widgets\ActiveForm $form */
$this->title = 'Update Candidate Information'
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Sailor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Candidate List</li>
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

                    <?php $form = ActiveForm::begin([]); ?>

                    <div class="row">
                        <div class="col-lg-6">
                            <strong style="color: red;">
                                Application ID :
                                <?= $model->app_unique_id; ?>

                            </strong>                           
                        </div>
                        <div class="col-lg-6">
                            <strong style="color: red;">
                            Payment Check ID :<?= $model->validation_id; ?>
                            </strong>
                        </div>
                    </div>
                    <br />
                    <div class="row">
                        <div class="col-lg-6">
                            <strong>
                                Application Designation :
                                <?= $model['candidate_designation'] ? CanDesignation::getAllDesignationSession($model['candidate_designation']) : '' ?>

                            </strong>                           
                        </div>
                        <div class="col-lg-6">
                            <strong>
                                Center : <?= SailorCenters::findOne($model->center_id)->name_en; ?>
                            </strong>
                        </div>
                    </div>
                    <br />
                    <div class="row ">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'father_name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'father_nid')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'father_occupation')->textInput(['maxlength' => true]) ?>
                        </div>

                        <div class="col-lg-6">
                            <?= $form->field($model, 'mother_name')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'mother_occupation')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'permanent_phone')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>


                    <div class="row">
                        <div class="col-lg-12">
                            <h3 style="color: red;">
                                Photo
                            </h3>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <?= $form->field($model, 'photo')->fileInput(['maxlength' => true]) ?>
                            <?php
                            // $image = '';
                            // if ($model->photo)
                            //     $image = Yii::getAlias('@rootMediaShow') . $model->photo;

                            if ($model->photo && Yii::$app->r2Storage->fileExists($model->photo)) {
                                echo '<img src=' . Yii::$app->r2Storage->fileUrl . $model->photo . ' alt="Image not found">';
                            } else echo '&nbsp;'
                            ?>                      
                        </div>
                       
                    </div>



                    <div class="row">
                        <div class="col-lg-12">
                            <h3 style="color: red;">
                                Payment Block
                            </h3>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-3">
                            <?= $form->field($model, 'payment_type')->dropDownList(StaticMethod::paymentTypeAdmin(), ['prompt' => '']) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'is_manula_paid')->dropDownList(StaticMethod::yesNo()) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'payment_status')->dropDownList(StaticMethod::yesNo()) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'ref_id')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'validation_id')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'card_type')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'card_no')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'trans_date')->widget(DatePicker::classname(), [
                                'options' => [
                                    'class' => 'form-control',
                                    'readonly' => true,
                                ],
                                'language' => 'en',
                                'dateFormat' => 'yyyy-MM-dd',
                                'clientOptions' => [
                                    'changeMonth' => true,
                                    'changeYear' => true,
                                    //'todayHighlight' => true,
                                ],
                            ])->label() ?>
                        </div>
                    </div>
                    <?= $form->field($model, 'application_status')->dropDownList(StaticMethod::isCanselApplication()) ?>

                    <?php 
                        if($model->request_for_cancel){                    ?>
                     <div class="col-lg-3 mb-3"> <span style='color:red'> Cancel Request Reason : </span>   <?= $model->reason?> </div>
                     <?= $form->field($model, 'cancel_application_view')->dropDownList([1 => 'Mark', 2 => 'Not Mark'],['prompt' => 'Select' ]) ?>
                     <?php } ?>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
/*             
    <?= $form->field($model, 'referred_by')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'reference_details')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'have_reference')->textInput() ?>
    <?= $form->field($model, 'relationship')->textarea(['rows' => 6]) ?>
    <?= $form->field($model, 'is_online_manual')->textInput() ?>
    <?= $form->field($model, 'current_village')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'current_word_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'current_union')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'current_post_office')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'current_thana')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'current_post_code')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'current_district')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'current_phone')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permanent_village')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permanent_union')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permanent_word_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permanent_post_office')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permanent_thana')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permanent_district')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permanent_post_code')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'permanent_phone')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'guardian_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'guardian_relation')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'guardian_occupation')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'guardian_address')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'dob')->textInput() ?>
    <?= $form->field($model, 'age_according_to_circular')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'religion')->textInput() ?>
    <?= $form->field($model, 'gender')->textInput() ?>
    <?= $form->field($model, 'marital_status')->textInput() ?>
    <?= $form->field($model, 'nationality')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'photo')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'qr_photo')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'jsc_reg_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'jsc_institute_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'jsc_passing_year')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'jsc_gpa')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ac_type_ssc')->textInput() ?>
    <?= $form->field($model, 'ssc_institute')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ssc_group')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ssc_edu_board')->textInput() ?>
    <?= $form->field($model, 'ssc_reg_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ssc_roll_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ssc_passing_year')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ssc_additional_subject')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'ssc_gpa')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'hsc_or_diploma')->textInput() ?>
    <?= $form->field($model, 'hsc_dip_institute')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'hsc_dip_group')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'hsc_dip_board')->textInput() ?>
    <?= $form->field($model, 'hsc_dip_reg_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'hsc_dip_roll_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'hsc_dip_passing_year')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'hsc_dip_additional_subject')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'hsc_dip_gpa')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_one_institute')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_one_subject')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_one_year')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_one_cert_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_two_institute')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_two_subject')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_two_year')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_two_cert_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_three_institute')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_three_subject')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_three_year')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_three_cert_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_four_institute')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_four_subject')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_four_year')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'experience_four_cert_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'is_freedom_fighter')->textInput() ?>
    <?= $form->field($model, 'freedom_fighter_relation')->textInput() ?>
    <?= $form->field($model, 'is_child_of_naval_officer')->textInput() ?>
    <?= $form->field($model, 'naval_father_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'naval_office_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'naval_rank')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'navy_ship_etbd_retired')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'naval_uniform_civil')->textInput() ?>
    <?= $form->field($model, 'is_anser_vdp')->textInput() ?>
    <?= $form->field($model, 'anser_vdp_rank')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'anser_vdp_office_no')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'is_khudro_jati_gosti')->textInput() ?>
    <?= $form->field($model, 'phase')->textInput() ?>
*/ ?>