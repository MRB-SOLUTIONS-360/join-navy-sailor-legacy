<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\jui\DatePicker;

$this->title = 'Reset Password';
?>

<div class="data-form-area pb-120">
    <div class="container">

        <div class="form__body row justify-content-center">
            <div class="section-title text-center">
                <h1>Reset Password</h1>
            </div>
            <div class="col-lg-10">        
                <?php if (Yii::$app->session->hasFlash('valid')) : ?>
                    <div class="alert alert-success" role="alert">
                        <?= Yii::$app->session->getFlash('valid') ?>
                    </div>
                <?php endif; ?>

                <?php if (Yii::$app->session->hasFlash('invalid')) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= Yii::$app->session->getFlash('invalid') ?>
                    </div>
                <?php endif; ?>
                
                <?php $form = ActiveForm::begin(['id' => 'update-password']); ?>
                <?= $form->field($model, 'username')->textInput(['placeholder' => $model->getAttributeLabel('username')])->label(false) ?>
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

                <div class="form-group">
                    <?= Html::submitButton('Update', ['class' => 'btn btn-primary', 'name' => 'update-password']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>        
        </div>
    </div>
</div>