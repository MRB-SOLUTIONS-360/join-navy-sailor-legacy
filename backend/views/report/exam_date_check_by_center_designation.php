<?php

use common\models\CanDesignation;
use common\models\Districts;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\static\Constants;
use common\static\StaticMethod;
use kartik\select2\Select2;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;


/** @var yii\web\View $this */
/** @var common\models\SailorBatchs $model */
/** @var yii\widgets\ActiveForm $form */

$this->title =  'Candidate Monitoring';

$designations = CanDesignation::getAllActiveDesignation();
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
                        <div class="col-lg-2">
                            <?= $form->field($model, 'batch')->dropDownList(SailorBatchs::getAllBatch(Constants::CANDIDATE_SAILOR), ['prompt' => 'Select ' . $model->getAttributeLabel('batch')])->label(false) ?>
                        </div>
                        <div class="col-lg-2">
                            <?= $form->field($model, 'center_id')->dropDownList(SailorCenters::getAllActiveCenter(), ['prompt' => 'Select ' . $model->getAttributeLabel('center_id')])->label(false) ?>
                        </div>
                        <div class="col-lg-2">
                            <?= $form->field($model, 'create_date')->widget(DatePicker::classname(), [
                                'options' => ['class' => 'form-control', 'placeholder' => 'Roll ' . $model->getAttributeLabel('create_date') . ' '],
                                'language' => 'en',
                                'dateFormat' => 'yyyy-MM-dd',
                                'clientOptions' => [
                                    'changeMonth' => true,
                                    'changeYear' => true,
                                ],
                            ])->label(false) ?>
                        </div>
                        <div class="col-lg-6">
                            <?php
                            echo $form->field($model, 'candidate_designation')->widget(Select2::classname(), [
                                'data' => $designations,
                                'value' => $model->candidate_designation,
                                'language' => 'en',
                                'options' => ['multiple' => true, 'placeholder' => 'Select ' . $model->getAttributeLabel('candidate_designation')],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label(false); ?>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-lg-6">
                            <?php
                            echo $form->field($model, 'district_slug')->widget(Select2::classname(), [
                                // 'data' => Districts::getAllActiveDistrict(),
                                'data' => [],
                                'value' => $model->district_slug,
                                'language' => 'en',
                                'options' => ['multiple' => true, 'placeholder' => 'Select ' . $model->getAttributeLabel('district_slug')],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ])->label(false); ?>
                        </div>
                        <div class="col-lg-1">
                            <div class="form-group">
                                <?= Html::submitButton('Search', ['class' => 'btn btn-success']) ?>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>

                <div class="row">

                <?php
                 
                 foreach($model->candidate_designation ?? [] as $k=>$val){
                        $name = $designations[$val] ?? '';
                        $exam_dates_name = $exam_dates[$val] ?? [];
                        echo '<div class="row mb-1">';
                        echo '<div> <strong> Designation : '.$name.'</strong></div>';                        
                        echo '<div> <strong>Dates </strong> : '.implode(', ',  $exam_dates_name).'</div>';   
                        echo '</div>';                    
                    }
                ?>

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
                                    $exam_dates_list = $exam_dates[$value['candidate_designation']] ?? [];
                            ?>
                                    <tr>
                                        <td><?= ($k + 1) ?></td>
                                        <td><?= $value['app_unique_id'] ?></td>
                                        <td><?= $desig; ?></td>
                                        <td><?= $value['name'] ?></td>
                                        <td><?= $value['serial_no'] ?></td>
                                        <td>
                                            <?= $value['exam_date'] ?>
                                            <?php
                                                if(!in_array($value['exam_date'],$exam_dates_list)) {
                                                    echo '<span style="color:red">Date mismached</span>';
                                                }
                                            ?>
                                        
                                        </td>
                                    </tr>
                                <?php }
                            } else { ?>
                                <tr>
                                    <td colspan="6"> No record found</td>
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


<script>
    $(function() {
        $('#dynamicmodel-center_id').change(function() {
            let thisCenter = $(this).val();
            $.ajax({
                url: '<?php echo Yii::$app->request->baseUrl . '/ajax/get-all-assigned-district-by-center' ?>',
                type: 'GET',
                dataType: 'json',
                data: {
                    center: thisCenter
                },
                beforeSend: function() {
                    /*$('#loader_batchId').show('slow');*/
                },
                success: function(data) {
                    let optionsHTML = `<option value="">Select</option>`;
                    let districts = data?.data || {};
                    for (const [key, value] of Object.entries(districts)) {
                        optionsHTML += `<option value="${key}">${value}</option>`;
                    }
                    $('#dynamicmodel-district_slug').html(optionsHTML);
                }
            });
        })
    })
</script>