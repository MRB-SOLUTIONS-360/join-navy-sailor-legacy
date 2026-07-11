<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \common\models\LoginForm $model */

use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Html;

$this->title = 'Join Bangladesh Navy';
?>
<div class="site-login">
    <div class="mt-5 offset-lg-3 col-lg-6" style="padding: 13px;
    background-color: #DDDDDD;    
    border-radius: 9px;">
        <h1 style="text-align: center;"><?= Html::encode('Login Sailor Admin') ?></h1>

        <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

        <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput() ?>

        <div class="text-danger">Write down the answer of this equation : <strong><?php echo implode('', $captcha) ?></strong></div>
        <?= $form->field($model, 'captcha')->textInput(['placeholder' => $model->getAttributeLabel('captcha')])->label(false) ?>



        <div class="form-group">
            <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-block', 'name' => 'login-button', 'style' => 'background:#001731']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>