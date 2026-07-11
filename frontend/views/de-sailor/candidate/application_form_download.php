<?php

use common\models\SailorBatchs;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;

$this->title = 'Join Bangladesh Navy';
?>
<script>
$(document).ready(function() {

    $('#downloaddocuments-download_by').change(function() {
        let this_val = $(this).val();
        $('#download_by_app_id').hide();
        $('#download_by_information').hide();

        $('#downloaddocuments-application_id').val('');
        $('#downloaddocuments-batch').val('');
        $('#downloaddocuments-serial_no').val('');
        $('#downloaddocuments-dob').val('');

        if (this_val == 1)
            $('#download_by_app_id').show();
        else if (this_val == 2)
            $('#download_by_information').show();

    })
})
</script>
<div class="data-form-area pb-120 mt-120 pt-lg-5 pt-3">
    <div class="container">
        <div class="form__body">
            <div class="section-title text-center">
                <h1>Download Documents</h1> <!-- আবেদন ফরম ডাউনলোড করুন-->
            </div>
            <div class="form__content download_form__content">
                <?php $form = ActiveForm::begin([
                'id' => 'download-form',
                // 'layout' => 'horizontal',
                'enableAjaxValidation' => true,
            ]);

            $show_app_id = 'none';
            if ($model->download_by == 1)
                $show_app_id = 'block';

            $show_info = 'none';
            if ($model->download_by == 2)
                $show_info = 'block';
            ?>
                <?php echo $form->field($model, 'download_by')->dropDownList([1 => 'Using Application ID', 2 => 'Using Information'], ['prompt' => 'Select', 'maxlength' => true]) ?>

                <div id="download_by_app_id" style="display: <?= $show_app_id; ?>;">
                    <?= $form->field($model, 'application_id')->textInput(['maxlength' => true]) ?>
                </div>

                <div id="download_by_information" style="display: <?= $show_info; ?>;">
                    <?= $form->field($model, 'batch')->dropDownList(SailorBatchs::getAllBatch(), ['prompt' => 'Select', 'maxlength' => true]) ?>
                    <?= $form->field($model, 'serial_no')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'dob')->widget(DatePicker::classname(), [
                    'options' => [
                        'class' => 'form-control',
                        'readonly' => true,
                    ],
                    'language' => 'en',
                    'dateFormat' => 'dd-MM-yyyy',
                    'clientOptions' => [
                        'changeMonth' => true,
                        'changeYear' => true,
                    ],
                ])->label() ?>
                </div>

                <div class="form-group mt-2">
                    <?= Html::submitButton('Search', ['class' => 'btn btn-success submit__select']) ?>
                </div>
                <?php ActiveForm::end(); ?>
                <div class="row mt-2">
                    <?php if (Yii::$app->session->hasFlash('application_close')) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= Yii::$app->session->getFlash('application_close') ?>
                    </div>
                    <?php endif; ?>

                    <?php if (Yii::$app->session->hasFlash('success_message')) : ?>
                    <div class="alert alert-success" role="alert">
                        <?= Yii::$app->session->getFlash('success_message') ?>
                    </div>
                    <?php endif;  ?>
                    <?php
                if ($sailor) {
                    echo Html::a('Download', $url, ['target' => '_blank', 'class' => 'btn']);
                }
                ?>
                </div>
            </div>
        </div>
    </div>
</div>