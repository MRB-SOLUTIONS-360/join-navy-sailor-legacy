<?php

use common\models\CanDesignation;
use common\models\Subjects;
use common\static\Constants;
use common\static\StaticMethod;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\jui\DatePicker;

/** @var yii\web\View $this */
/** @var common\models\Eligibility $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = $model->isNewRecord ? 'Add Eligibility' : 'Update Eligibility ' . $model->candidate_type;
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Eligibility</li>
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
                        'id' => 'eligibility-form',
                    ]); ?>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'candidate_type')->dropDownList(StaticMethod::candidateType(), ['prompt' => 'Select ' . $model->getAttributeLabel('candidate_type')]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'marital_status')->inline(true)->checkboxList(StaticMethod::maritalStatus()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?php
                            $can_desi = [];
                            if (!$model->isNewRecord)
                                $can_desi = CanDesignation::getAllActiveDesignation(type: $model->candidate_type);
                            ?>
                            <?= $form->field($model, 'candidate_designation')->dropDownList($can_desi, ['prompt' => 'Select ' . $model->getAttributeLabel('candidate_designation')]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'gender')->inline(true)->checkboxList(StaticMethod::gender()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'min_age')->textInput(['maxlength' => true])->label($model->getAttributeLabel('min_age') . '<span class="text-danger"> (17.00.00 # 17 year 00 month 00 days)</span>') ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'jsc_result', ['wrapperOptions' => ['style' => 'display:inline-block']])->inline(true)->radioList(StaticMethod::yesNo()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'max_age')->textInput(['maxlength' => true])->label($model->getAttributeLabel('max_age') . '<span class="text-danger"> (17.06.02 # 17 year 06 month 02 days)</span>') ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'ssc_result')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'dept_can_max_age')->textInput(['maxlength' => true])->label($model->getAttributeLabel('dept_can_max_age') . '<span class="text-danger"> (25.00.15 # 25 year 00 month 15 days)</span>') ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'ssc_ac_group')->inline(true)->checkboxList(StaticMethod::academicGroupSsc()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'height_male')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'hsc_result')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'weight_male')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'hsc_ac_group', ['wrapperOptions' => ['style' => 'display:inline-block']])->inline(true)->checkboxList(StaticMethod::academicGroupHsc()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'height_female')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'is_required_biology', ['wrapperOptions' => ['style' => 'display:inline-block']])->inline(true)->radioList(StaticMethod::yesNo()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'weight_female')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'is_allow_trade_course', ['wrapperOptions' => ['style' => 'display:inline-block']])->inline(true)->radioList(StaticMethod::yesNo()) ?>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'chest_normal_male')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'is_allow_diploma', ['wrapperOptions' => ['style' => 'display:inline-block']])->inline(true)->radioList(StaticMethod::yesNo()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'chest_extended_male')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'diploma_result')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'chest_normal_female')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?php // $form->field($model, 'hons_diploma_subject')->dropDownList(Subjects::getAllActiveSubject(),['prompt'=>'Select']) 
                            ?>
                            <?php
                            echo $form->field($model, 'trade_course_subject')->widget(Select2::classname(), [
                                'data' => ArrayHelper::map(Subjects::getAllActiveSubjectBySubjectType(candidate_type: Constants::CANDIDATE_DE_SAILOR_DOCKYARD, subject_type: Constants::SUBJECT_TYPE_TRADE), 'id', 'name_en'),
                                'value' => $model->trade_course_subject,
                                'language' => 'en',
                                'options' => ['multiple' => true, 'placeholder' => 'Select Trade'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]); ?>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'chest_extended_female')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?php
                            echo $form->field($model, 'hons_diploma_subject')->widget(Select2::classname(), [
                                'data' => ArrayHelper::map(Subjects::getAllActiveSubjectBySubjectType(candidate_type: Constants::CANDIDATE_DE_SAILOR, subject_type: Constants::SUBJECT_TYPE_DIPLOMA), 'id', 'name_en'),
                                'value' => $model->hons_diploma_subject,
                                'language' => 'en',
                                'options' => ['multiple' => true, 'placeholder' => 'Select Subject'],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]); ?>

                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'status')->dropDownList(StaticMethod::statusDropDown(), []) ?>
                        </div>
                        <div class="col-lg-6"> 
                            <?= $form->field($model, 'is_required_trade_course_experience', ['wrapperOptions' => ['style' => 'display:inline-block']])->inline(true)->radioList(StaticMethod::yesNo()) ?>
                        </div>
                    </div>

                    <?php
                    /* <?= $form->field($model, 'is_allow_hons_appeared')->textInput() ?>
                    <?= $form->field($model, 'hons_result')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'is_allow_masters_appeared')->textInput() ?>
                    <?= $form->field($model, 'masters_result')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'masters_subject')->textInput(['maxlength' => true]) ?>
                    */ ?>
                    <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Update'), ['class' => 'btn btn-success']) ?>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#eligibility-candidate_type').change(function() {
            let candidate_type = $(this).val();


            if (candidate_type) {
                $.ajax({
                    url: '<?php echo Yii::$app->request->baseUrl . '/ajax/get-candesignation-by-cantype' ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        candidate_type: candidate_type,
                    },
                    beforeSend: function() {
                        // $('#loader_courseId').show('slow');
                    },
                    success: function(data) {


                        let options = '<option value=""> Select <?= $model->getAttributeLabel('candidate_designation') ?> </option>';
                        Object.entries(data?.data).map((value, ind) => {
                            options += `<option value="${value[0]}"> ${value[1]}  </option>`;
                        })

                        $('#eligibility-candidate_designation').html(options)



                        // $('#loader_batchId').hide('slow');
                        // $('#longcoursestudents-courseid').html(data);
                    }
                });
            }
        })
    })
</script>