<?php

use common\models\CanDesignation;
use common\models\SailorCenters;
use common\static\StaticMethod;
use Mpdf\Tag\Center;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;

/** @var yii\web\View $this */
/** @var common\models\DeSailors $model */
/** @var yii\widgets\ActiveForm $form */
$this->title = 'Update Candidate Information'
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Direct Entry Sailor</a></li>
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
                            <!-- <img id="imgPreview" src="<?php // $image; ?>" alt="pic" /> -->
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
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>
 