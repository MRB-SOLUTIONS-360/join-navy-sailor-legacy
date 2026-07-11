<?php

use common\models\CanDesignation;
use common\models\SailorBatchs;
use common\static\Constants;
use common\static\StaticMethod;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;


/** @var yii\web\View $this */
/** @var common\models\SailorBatchs $model */
/** @var yii\widgets\ActiveForm $form */

$this->title =  'Candidate Monitoring';
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Sailor Report</a></li>
            <li class="breadcrumb-item active" aria-current="page">Candidate Monitoring</li>
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
                        'id' => 'payment_report',
                        //'enableAjaxValidation' => true,
                    ]); ?>

                    <div class="row">
                        <div class="col-lg-3">
                            <?= $form->field($model, 'batch')->dropDownList(SailorBatchs::getAllBatch(Constants::CANDIDATE_DE_SAILOR), ['prompt' => 'Select ' . $model->getAttributeLabel('batch')])->label(false) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'monitor_by')->dropDownList(StaticMethod::candidateMonitorBy(), ['prompt' => 'Select ' . $model->getAttributeLabel('monitor_by')])->label(false) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'create_date')->widget(DatePicker::classname(), [
                                'options' => ['class' => 'form-control', 'placeholder' => $model->getAttributeLabel('create_date') . ' applicable for image missing'],
                                'language' => 'en',
                                'dateFormat' => 'yyyy-MM-dd',
                                'clientOptions' => [
                                    'changeMonth' => true,
                                    'changeYear' => true,
                                ],
                            ])->label(false) ?>

                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <?= Html::submitButton('Search', ['class' => 'btn btn-success']) ?>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>

                <div class="row">
                    <?php if ($model->monitor_by) {
                        echo '<h3 style="text-align:center;color:black;">Candidate List of ' . StaticMethod::candidateMonitorBy($model->monitor_by) . '</h3>';
                    }  ?>
                    <table class="table table-striped table-bordered mt-2">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Application ID</th>
                                <th>Designation</th>
                                <th>Name</th>
                                <th>Serial No</th>
                                <th>Exam Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($sailor) {
                                foreach ($sailor as $k => $value) {
                                    $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
                            ?>
                                    <tr>
                                        <td><?= ($k + 1) ?></td>
                                        <td><?= $value['app_unique_id'] ?></td>
                                        <td><?= $desig; ?></td>
                                        <td><?= $value['name'] ?></td>
                                        <td><?= $value['serial_no'] ?></td>
                                        <td><?= $value['exam_date'] ?></td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="5"> No record found</td>
                                </tr>
                            <?php }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>