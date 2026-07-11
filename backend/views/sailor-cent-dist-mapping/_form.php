<?php

use common\models\Districts;
use common\models\SailorCenters;
use common\static\StaticMethod;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use kartik\select2\Select2;


/** @var yii\web\View $this */
/** @var common\models\SailorCentDistMapping $model */
/** @var yii\widgets\ActiveForm $form */
$this->title = $model->isNewRecord ? 'Add Center District Mappings' : 'Update Center District Mappings ' . $model->center_id;
?>


<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Center District Mappings</li>
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
                    <?php $form = ActiveForm::begin([
                        'id' => 'center-dist-mapping-form'
                    ]); ?>
                    
                     <?= $form->field($model, 'candidate_type')->dropDownList(StaticMethod::candidateType(), ['prompt' => 'Select ' . $model->getAttributeLabel('candidate_type')]) ?>
                    <?= $form->field($model, 'center_id')->dropDownList(SailorCenters::getAllActiveCenter(), ['prompt' => 'Select ' . $model->getAttributeLabel('center_id')]) ?>
                    <?= $form->field($model, 'district_slug')->widget(Select2::classname(), [
                        'data' => Districts::getAllActiveDistrict(),
                        'value' => $model->district_slug,
                        'language' => 'en',
                        'options' => ['multiple' => true, 'placeholder' => 'Select ' . $model->getAttributeLabel('district_slug')],
                        'pluginOptions' => [
                            'allowClear' => true,
                        ],
                    ]); ?>
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