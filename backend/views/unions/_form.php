<?php

use common\models\Districts;
use common\models\Unions;
use common\models\Upozilas;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var common\models\Unions $model */
/** @var yii\widgets\ActiveForm $form */
$this->title = $model->isNewRecord ? 'Add Union' : 'Update Union ' . $model->name;
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Union</li>
        </ol>
    </nav>
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <?php if (Yii::$app->session->hasFlash('success')): ?>
                    <div class="alert alert-success" role="alert">
                        <?= Yii::$app->session->getFlash('success') ?>
                    </div>
                <?php endif; ?>

                <h4 class="header-title mb-3"><?= $this->title; ?></h4>

                <div class="tab-pane show active" id="custom-styles-preview">
                    <?php $form = ActiveForm::begin(); ?>
                    <?= $form->field($model, 'district_id')->dropDownList(Districts::getDistrictsList(), ['prompt' => 'Select ' . $model->getAttributeLabel('district_id')]) ?>

                    <?= $form->field($model, 'upozila_id')->dropDownList(Upozilas::getUpazilaListAdmin(), ['prompt' => 'Select ' . $model->getAttributeLabel('upozila_id')]) ?>

                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'bn_name')->textInput(['maxlength' => true]) ?>
                    <?= $form->field($model, 'status')->dropDownList(StaticMethod::statusDropDown(), []) ?>
                    <div class="form-group mt-2">
                        <?= Html::submitButton($model->isNewRecord ? Yii::t('app', 'Save') : Yii::t('app', 'Update'), ['class' => 'btn btn-success']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>