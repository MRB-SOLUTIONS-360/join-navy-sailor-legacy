<?php

use common\static\StaticMethod;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;


/** @var yii\web\View $this */
/** @var common\models\User $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = 'Update User'
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">User</li>
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
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'user_group')->dropDownList(['super_admin' => 'Super admin', 'admin' => 'Admin', 'register' => 'Register',], ['prompt' => '']) ?>

                    <?= $form->field($model, 'user_type')->dropDownList(['admin' => 'Admin', 'candidate' => 'Candidate',], ['prompt' => '']) ?>

                    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'email')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'phone_no')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'password_hash')->textInput(['maxlength' => true]) ?>

                    <?= $form->field($model, 'status')->dropDownList(StaticMethod::statusDropDown(), ['prompt' => 'Select']) ?>

                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>