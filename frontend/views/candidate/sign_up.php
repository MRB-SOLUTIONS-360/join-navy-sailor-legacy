<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;
use yii\jui\DatePicker;

$this->title = 'Signup';
?>
<style>
.birth-form {
    display: block;
}
.signup-form {
    display: none;
}
.loading {
    opacity: 0.6;
    pointer-events: none;
}
</style>
<div class="data-form-area pb-120">
    <div class="container">
        <div class="form__body">
            <div class="section-title text-center" style="margin-bottom: 15px;">
                <h1>SignUp Form</h1>
            </div>
            <div class="form__content">

                <div class="birth-form" id="birth-form">
                <?php $form = ActiveForm::begin([
                    'id' => 'form-birth-validation',
                    'enableAjaxValidation' => false,
                ]); ?>                 
                <?= $form->field($model, 'birth_registration_no')->textInput(['placeholder' => $model->getAttributeLabel('birth_registration_no'), 'id' => 'birth-reg-input'])->label(false)->error(false) ?>
               
                <div class="form-group">
                    <?= Html::button('Validate & Continue', ['class' => 'btn btn-primary w-100 form__btn', 'id' => 'validate-birth-btn']) ?>
                    <div id="birth-validation-error" class="text-danger mt-2" style="display: none;"></div>
                </div>
                <?php ActiveForm::end(); ?>


                </div>

                <div class="signup-form" id="signup-form" >
                    <!-- Bootstrap Icons CDN (if not included already) --> 
                <style>
                .custom-warning {
                    background: linear-gradient(90deg, #fff3cd, #ffeeba); /* soft yellow gradient */
                    border: 1px solid rgb(230, 57, 14); /* border color matching icon */
                    color: rgb(230, 57, 14); /* text color */
                }
                .custom-warning i {
                    color: rgb(230, 57, 14); /* icon color */
                }
                </style>
                <div class="alert d-flex align-items-center shadow-sm rounded-3 custom-warning fs-6 fw-medium" role="alert" style="padding: .5rem">
                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
                    <div>
                        <strong>সতর্কবার্তা:</strong> ইউজারনেম ও পাসওয়ার্ড নিরাপদে রাখুন — প্রতিবার লগইনের সময় দরকার হবে।
                    </div>
                </div>

                <?php $form = ActiveForm::begin([
                    'id' => 'form-signup',
                    'enableAjaxValidation' => true,
                ]); ?>
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
                <?= $form->field($model, 'birth_registration_no')->textInput(['placeholder' => $model->getAttributeLabel('birth_registration_no'), 'readonly' => true])->label(false) ?>
                <?= $form->field($model, 'username')->textInput(['placeholder' => $model->getAttributeLabel('username')])->label(false) ?>
                <?= $form->field($model, 'email')->textInput(['placeholder' => $model->getAttributeLabel('email')])->label(false) ?>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => $model->getAttributeLabel('password')])->label(false) ?>
                <?= $form->field($model, 'confirm_password')->passwordInput(['placeholder' => $model->getAttributeLabel('confirm_password')])->label(false) ?>
                <div class="text-danger">Write down the answer of this equation : <strong><?php echo implode('', $captcha) ?></strong></div>
                <?= $form->field($model, 'captcha')->textInput(['placeholder' => $model->getAttributeLabel('captcha')])->label(false) ?>
                <div class="form-group">
                    <?= Html::submitButton('Signup', ['class' => 'btn btn-primary w-100 form__btn', 'name' => 'signup-button']) ?>
                    <?= Html::a('If you already have account ? <span>Login</span>', ['/candidate/login', 'ceci' => Yii::$app->getRequest()->getQueryParam('ceci')], ['class' => 'forgot__pass', 'name' => 'signup-button']) ?>
                </div>
                <?php ActiveForm::end(); ?>
                </div>


            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs("
$(document).ready(function() {
    $('#validate-birth-btn').on('click', function(e) {
        e.preventDefault();
        
        var birthRegNo = $('#birth-reg-input').val().trim();
        var errorDiv = $('#birth-validation-error');
        var birthForm = $('#birth-form');
        var signupForm = $('#signup-form');
        var validateBtn = $(this);
        
        // Clear previous error
        errorDiv.hide().text('');
        
        // Validate input
        if (!birthRegNo) {
            errorDiv.text('Birth Registration No is required.').show();
            return;
        }
        
        // Show loading state
        birthForm.addClass('loading');
        validateBtn.prop('disabled', true).text('Validating...');
        
        // Send AJAX request
        $.ajax({
            url: '" . Yii::$app->urlManager->createUrl(['candidate/validate-birth-registration']) . "',
            type: 'POST',
            data: {
                birth_registration_no: birthRegNo,
                _csrf: yii.getCsrfToken()
            },
            success: function(response) {
                if (response.success) {
                    // Hide birth form and show signup form
                    birthForm.hide();
                    signupForm.show();
                    
                    // Pre-fill birth registration number in signup form
                    $('#signup-form input[name=\"SignupForm[birth_registration_no]\"]').val(birthRegNo);
                    
                    // Scroll to top of signup form
                    $('html, body').animate({
                        scrollTop: signupForm.offset().top - 50
                    }, 500);
                } else {
                    errorDiv.text(response.message).show();
                }
            },
            complete: function() {
                // Remove loading state
                birthForm.removeClass('loading');
                validateBtn.prop('disabled', false).text('Validate & Continue');
            }
        });
    });
    
    // Allow Enter key to trigger validation
    $('#birth-reg-input').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#validate-birth-btn').click();
        }
    });
});
");
?>