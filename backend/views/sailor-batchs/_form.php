<?php

use common\static\StaticMethod;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var common\models\SailorBatchs $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = $model->isNewRecord ? 'Add Batch' : 'Update Batch ' . $model->name_en;
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Candidate Batch</li>
        </ol>
    </nav>
</div>
<script>
    $(function() {
        flatpickr('.circular_date_time', {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
        })
    })
</script>


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
                        'id' => 'sailor-batch-form',
                        //'enableAjaxValidation' => true,
                    ]); ?>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'candidate_type')->dropDownList(StaticMethod::candidateType(), ['prompt' => 'Select ' . $model->getAttributeLabel('candidate_type')]) ?>
                            <?= $form->field($model, 'name_en')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'name_bn')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'description')->textarea(['maxlength' => true]) ?>

                            <?= $form->field($model, 'circular_date')->widget(DatePicker::classname(), [
                                'options' => ['class' => 'form-control'],
                                'language' => 'en',
                                'dateFormat' => 'yyyy-MM-dd',
                                'clientOptions' => [
                                    'changeMonth' => true,
                                    'changeYear' => true,
                                ],
                            ]) ?>

                            <?= $form->field($model, 'circular_start_date')->textInput(['class' => 'circular_date_time form-control']) ?>
                            <?= $form->field($model, 'circular_close_date')->textInput(['class' => 'circular_date_time form-control']) ?>

                            <?php /* 
                            <?= $form->field($model, 'circular_media')->inline(true)->fileInput()->label($model->getAttributeLabel('circular_media')) ?>
                            <?php
                            if (!$model->isNewRecord && $model->circular_media && file_exists(Yii::getAlias('@rootDirFilUpload') . $model->circular_media)) {
                            ?>
                                <a target="_blank" class="btn btn-primary mb-1" href="<?= Yii::getAlias('@rootMediaShow') . $model->circular_media ?>">View</a>

                            <?php } ?>

                            <?= $form->field($model, 'media_for_api')->inline(true)->radioList(StaticMethod::yesNo())->label($model->getAttributeLabel('media_for_api') . '<span class="text-danger">(If ' . strtolower($model->getAttributeLabel('media_for_api')) . ' is Yes then send media for API)</span>') ?>
                                */ ?>
                            <?= $form->field($model, 'roll_from')->inline(true)->radioList(StaticMethod::getRollFrom())->label($model->getAttributeLabel('roll_from') . '<span class="text-danger">(If ' . strtolower($model->getAttributeLabel('roll_from')) . ' is batch then candidate get roll from here else  <strong>batch configuration</strong>)</span>') ?>
                        </div>
                        <div class="col-lg-6">
                            <!-- ,['enableAjaxValidation' => true]  -->

                            <?= $form->field($model, 'start_roll')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'next_start_roll_after')->textInput(['maxlength' => true])->label($model->getAttributeLabel('next_start_roll_after') . '<span class="text-danger">(After which roll do you want to start new roll)</span>') ?>
                            <?= $form->field($model, 'next_start_roll')->textInput(['maxlength' => true])->label($model->getAttributeLabel('next_start_roll') . '<span class="text-danger">(New roll will start )</span>') ?>
                            <?= $form->field($model, 'payment_mode')->inline(true)->radioList(StaticMethod::paymentMode()) ?>
                            <?= $form->field($model, 'payment_amount')->dropDownList(StaticMethod::paymentAmount(), ['prompt' => 'Select ' . $model->getAttributeLabel('payment_amount')]) ?>

                            <?= $form->field($model, 'allow_refund')->inline(true)->radioList(StaticMethod::yesNo())->label($model->getAttributeLabel('allow_refund') . '<span class="text-danger">(Is Allow Refund)</span>') ?>
                            <?= $form->field($model, 'is_active_batch')->inline(true)->radioList(StaticMethod::yesNo())->label($model->getAttributeLabel('is_active_batch') . '<span class="text-danger">(Candidate can apply if ' . strtolower($model->getAttributeLabel('is_active_batch')) . ' yes and <strong>status active </strong>)</span>') ?>
                            <?= $form->field($model, 'allow_application_after_close')->inline(true)->radioList(StaticMethod::yesNo())->label($model->getAttributeLabel('allow_application_after_close') . '<span class="text-danger">(Candidate can able to complete application if he paid)</span>') ?>
                            <?= $form->field($model, 'is_batch_live_mode')->inline(true)->radioList(StaticMethod::yesNo())->label($model->getAttributeLabel('is_batch_live_mode') . '<span class="text-danger">(if yes then candidate can apply with out secret key)</span>') ?>
                            <?= $form->field($model, 'secrate_key')->textInput(['maxlength' => true]) ?>
                            <?= $form->field($model, 'status')->dropDownList(StaticMethod::statusDropDown(), []) ?>
                        </div>
                    </div>

                    <div class="form-group mt-2">
                        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Update'), ['class' => 'btn btn-success']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>