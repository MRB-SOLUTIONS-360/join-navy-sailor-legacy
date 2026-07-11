<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Html;
use yii\bootstrap5\ActiveForm;

$this->title = 'Change Password';
?>

<div class="data-form-area pb-120">
    <div class="container">

        <div class="form__body ">
        <div class="section-title text-center">
        <h1>Change Password</h1>
            </div>

           
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <?php if (Yii::$app->session->hasFlash('valid')) : ?>
                        <div class="alert alert-success" role="alert">
                            <?= Yii::$app->session->getFlash('valid') ?>
                        </div>
                    <?php endif; ?>

                    <?php $form = ActiveForm::begin(['id' => 'update-password']); ?>
                    <?= $form->field($model, 'newpassword')->passwordInput(['placeholder' => $model->getAttributeLabel('newpassword')])->label(false) ?>
                    <?= $form->field($model, 'repeatnepassword')->passwordInput(['placeholder' => $model->getAttributeLabel('repeatnepassword')])->label(false) ?>
                    <div class="form-group">
                        <?= Html::submitButton('Update', ['class' => 'btn btn-primary', 'name' => 'update-password']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>