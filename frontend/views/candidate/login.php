<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Login';
?>
<div class="data-form-area pb-120">
    <div class="container">
        <div class="form__body">
            <div class="section-title text-center" style="margin-bottom: 15px;">
                <h1>Login Form</h1>
            </div>
           
            <div class="form__content">
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
                <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>
                <?= $form->field($model, 'username')->textInput(['autofocus' => true, 'placeholder' => $model->getAttributeLabel('username')])->label(false) ?>
                <?= $form->field($model, 'password')->passwordInput(['placeholder' => $model->getAttributeLabel('password')])->label(false) ?>
                <div class="text-danger">Write down the answer of this equation : <strong><?php echo implode('', $captcha) ?></strong></div>
                <?= $form->field($model, 'captcha')->textInput(['placeholder' => $model->getAttributeLabel('captcha')])->label(false) ?>
                <!-- <div class="my-1 mx-0" style="color:#999;">
                    If you forgot your password you can <span style="color: red;"><?php // Html::a('reset it', ['candidate/request-password-reset']) 
                                                                                    ?></span>.
                </div>  -->
                <div class="form-group">
                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary w-100 form__btn', 'name' => 'login-button']) ?>
                </div>
                <div class="forgot__pass">
                    Forgot your password?
                    <?= Html::a('reset it', ['candidate/request-password-reset']) ?>.
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>