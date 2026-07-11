<?php

use common\models\CanDesignation;
use common\models\Districts;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\static\StaticMethod;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;
use kartik\select2\Select2;
use common\models\SailorCentDistMapping;

/** @var yii\web\View $this */
/** @var common\models\SailorBatchConfiguration $model */
/** @var yii\widgets\ActiveForm $form */

$this->title = $model->isNewRecord ? 'Add Batch Configuration' : 'Update Batch Configuration ' . $model->candidate_type;
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sailor Batch Configuration</li>
        </ol>
    </nav>
</div>

<!-- Flatpickr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
                        'id' => 'sailor-config-form',
                        'enableAjaxValidation' => true,  // Enable Ajax validation
                    ]); ?>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'candidate_type')->dropDownList(StaticMethod::candidateType(), ['prompt' => 'Select ' . $model->getAttributeLabel('candidate_type')]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'batch_id')->dropDownList(SailorBatchs::getAllActiveBatch(), ['prompt' => 'Select ' . $model->getAttributeLabel('batch_id')]) ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-4">
                            <?= $form->field($model, 'center_id')->dropDownList(SailorCenters::getAllActiveCenter(), ['prompt' => 'Select ' . $model->getAttributeLabel('center_id')]) ?>
                        </div>
                        <div class="col-lg-2">
                            <?= $form->field($model, 'team')->dropDownList(StaticMethod::team(), ['prompt' => 'Select ' . $model->getAttributeLabel('team')]) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'gender')->inline(true)->checkboxList(StaticMethod::gender()) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'marital_status')->inline(true)->checkboxList(StaticMethod::maritalStatus()) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?php
                            echo $form->field($model, 'candidate_designation')->widget(Select2::classname(), [
                                'data' => CanDesignation::getAllActiveDesignation(),
                                'value' => $model->candidate_designation,
                                'language' => 'en',
                                'options' => ['multiple' => true, 'placeholder' => 'Select ' . $model->getAttributeLabel('candidate_designation')],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]); ?>
                        </div>
                        <div class="col-lg-6">
                            <?php
                            echo $form->field($model, 'district_slug')->widget(Select2::classname(), [
                                'data' => $model->center_id ? SailorCentDistMapping::GetAllAssignedDistrictByCenter($model->center_id) : [], //Districts::getAllActiveDistrict(),
                                'value' => $model->district_slug,
                                'language' => 'en',
                                'options' => ['multiple' => true, 'placeholder' => 'Select ' . $model->getAttributeLabel('district_slug')],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]); ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'exam_group')->inline(true)->radioList(StaticMethod::sailorGroup()) ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'roll_swap_in_group')->inline(true)->radioList(StaticMethod::yesNo())->label($model->getAttributeLabel('roll_swap_in_group') . '<span class="text-danger">(if yes then swap roll between group)</span>') ?>
                        </div>
                        <div class="col-lg-3">
                            <?= $form->field($model, 'check_max_candidate')->inline(true)->radioList(StaticMethod::yesNo())->label($model->getAttributeLabel('check_max_candidate') . '<span class="text-danger">(if yes then check max candidate)</span>') ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <button type="button" id="add-exam-date-row" class="btn btn-primary">
                                <i class="uil uil-plus"></i> Add More Exam Dates
                            </button>
                            <div class="row" id="exam-dates-container">
                                <?php
                                $rowCount = 0; // Keep track of the number of rows
                                foreach ($examDates as $index => $examDate) {
                                    // Start a new row every 3 items (adjust to 6 if you want 6 per row)
                                    if ($index % 3 == 0) {
                                        $rowCount++; ?>
                                        <div class="row" id="exam-dates-row-<?= $rowCount ?>">
                                        <?php } ?>

                                        <div class="col-md-4" id="exam-date-row_<?= ($index + 1) ?>">

                                            <div class="row">
                                                <!-- Exam Date field -->
                                                <div class="col-md-7">
                                                    <?= $form->field($examDate, 'exam_date[]', ['enableAjaxValidation' => true])
                                                        ->textInput([
                                                            'maxlength' => true,
                                                            'id' => 'sailorbatchconfigurationexamdate-exam_date-' . ($index + 1),
                                                            'class' => 'dp form-control',
                                                            'style' => ($examDate->status != 1) ? 'color:red;' : '',
                                                            'value' => $examDate->exam_date
                                                        ])->label("Exam Date") ?>
                                                </div>

                                                <!-- Limit field with beside -->
                                                <div class="col-md-5 d-flex align-items-center">
                                                    <?= $form->field($examDate, 'max_candidate_this_date[]')
                                                        ->textInput([
                                                            'type' => 'number',
                                                            'id' => 'max_candidate_this_date_' . ($index + 1),
                                                            'class' => 'form-control',
                                                            'value' => $examDate->max_candidate_this_date ?? '',
                                                            'min' => 1
                                                        ])->label("Max Candidates") ?>

                                                    <?php if ($index > 0): ?>
                                                        <span class="delete-exam-date text-danger ms-2"
                                                            data-id="<?= $examDate->id ?>"
                                                            id="delete-exam-date-<?= ($index + 1) ?>"
                                                            onclick="deleteExamDate(this)"
                                                            style="cursor:pointer; font-size:18px;">
                                                            &times;
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Hidden exam_date id -->
                                            <input type="hidden"
                                                id="exam_date_id_<?= ($index + 1) ?>"
                                                name="SailorBatchConfigurationExamDate[id][]"
                                                value="<?= $examDate->id ?>">
                                        </div>

                                    <?php
                                    // Close row after 3 items or if it’s the last element
                                    if (($index + 1) % 3 == 0 || $index == count($examDates) - 1) {
                                        echo '</div>'; // close row
                                    }
                                }
                                    ?>
                                        </div>

                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <?= $form->field($model, 'group_start_roll', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-lg-6">
                                    <?= $form->field($model, 'group_end_roll', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6">
                                    <?= $form->field($model, 'group_no_of_candidate', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-lg-3">
                                    <?= $form->field($model, 'du_uc_can_total', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-lg-3">
                                    <?= $form->field($model, 'medical_can_total', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-3">
                                    <?= $form->field($model, 'pertol_store_can_total', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-lg-3">
                                    <?= $form->field($model, 'cook_steward_can_total', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-lg-3">
                                    <?= $form->field($model, 'modc_can_total', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                                <div class="col-lg-3">
                                    <?= $form->field($model, 'topass_can_total', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-6">
                                    <?= $form->field($model, 'status')->dropDownList(StaticMethod::statusDropDown(), []) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
                            </div>
                            <?php ActiveForm::end(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Function to handle deletion of exam date rows
            function deleteExamDate(element) {
                const examDateId = element.getAttribute('data-id');
                const rowElement = element.closest('.col-md-4');

                if (!confirm('Are you sure you want to delete this exam date?')) {
                    return;
                }

                if (examDateId && examDateId !== '0') {
                    // Send AJAX request to delete from DB
                    fetch('<?= \yii\helpers\Url::to(['sailor-batch-configuration/delete-exam-date']) ?>', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': '<?= Yii::$app->request->csrfToken ?>'
                            },
                            body: JSON.stringify({
                                id: examDateId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the row from the DOM
                                rowElement.remove();
                            } else {
                                alert(data.message || 'Error deleting exam date.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error deleting exam date.');
                        });
                } else {
                    // Just remove from DOM if no ID
                    rowElement.remove();
                }
            }

            $(document).ready(function() {
                let rowCount = <?= ceil(count($examDates) / 3) ?>; // Number of existing rows
                let colCount = <?= count($examDates) % 3 ?>; // Columns in current row

                // Initialize flatpickr for existing fields
                flatpickr('.dp', {
                    dateFormat: "Y-m-d"
                });

                $(document).on('click', '#add-exam-date-row', function() {
                    // If 3 columns already present in the row, create a new row
                    if (colCount === 3) {
                        rowCount++;
                        colCount = 0;
                        $('#exam-dates-container').append('<div class="row" id="exam-dates-row-' + rowCount + '"></div>');
                    }

                    colCount++;

                    // Build new column with Exam Date + Limit side by side
                    let newRow = `
                <div class="col-md-4" id="exam-date-row_${rowCount}_${colCount}">
                    <div class="row">
                        <!-- Exam Date -->
                        <div class="col-md-7">
                            <div class="mb-3 field-sailorbatchconfigurationexamdate-exam_date-${rowCount}-${colCount} required">
                                <label class="form-label" for="sailorbatchconfigurationexamdate-exam_date-${rowCount}-${colCount}">Exam Date</label>
                                <input type="text" 
                                       id="sailorbatchconfigurationexamdate-exam_date-${rowCount}-${colCount}" 
                                       class="dp form-control flatpickr-input" 
                                       name="SailorBatchConfigurationExamDate[exam_date][]" 
                                       value="" 
                                       aria-required="true" 
                                       readonly="readonly">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Max Candidates This Date -->
                        <div class="col-md-5    d-flex align-items-center">
                            <div class="mb-3 field-sailorbatchconfigurationexamdate-max_candidate_this_date-${rowCount}-${colCount}">
                                <label class="form-label" for="max_candidate_this_date_${rowCount}_${colCount}">Max Candidates</label>
                                <input type="number" 
                                       id="max_candidate_this_date_${rowCount}_${colCount}" 
                                       class="form-control" 
                                       name="SailorBatchConfigurationExamDate[max_candidate_this_date][]" 
                                       value="" 
                                       min="1">
                            </div>

                             <span class="delete-exam-date text-danger ms-2"
                                                            data-id="0"
                                                            id="delete-exam-date-${rowCount}_${colCount}"
                                                            onclick="deleteExamDate(this)"
                                                            style="cursor:pointer; font-size:18px;">
                                                            &times;
                                                        </span>
                        </div>
                    </div>

                    <!-- Hidden ID -->
                    <input type="hidden" 
                           id="exam_date_id_custom_${rowCount}_${colCount}" 
                           name="SailorBatchConfigurationExamDate[id][]" 
                           value="">
                </div>
            `;

                    // Append the new column to the last row
                    $('#exam-dates-container > .row:last').append(newRow);

                    // Re-init flatpickr for new input
                    flatpickr(`#sailorbatchconfigurationexamdate-exam_date-${rowCount}-${colCount}`, {
                        dateFormat: "Y-m-d"
                    });
                });
            });

            $(document).on('change', '#sailorbatchconfiguration-center_id', function() {
                var center_id = $(this).val();
                
                // Reference to the district Select2
                var $districtSelect = $('#sailorbatchconfiguration-district_slug');

                if(center_id){
                    // Clear current Select2 selections and show loading
                    $districtSelect.val(null).trigger('change');
                    $districtSelect.prop('disabled', true);
                    $districtSelect.html('<option value="">Loading...</option>');

                    $.ajax({
                    url: '<?php echo Yii::$app->request->baseUrl . '/ajax/get-all-assigned-district-by-center' ?>',
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        center: center_id
                    },
                    beforeSend: function() {
                        /*'#loader_batchId'*/
                    },
                    success: function(data) {
                        let optionsHTML = `<option value="">Select</option>`;
                        let districts = data?.data || {};
                        for (const [key, value] of Object.entries(districts)) {
                            optionsHTML += `<option value="${key}">${value}</option>`;
                        }
                        $districtSelect.html(optionsHTML);
                        $districtSelect.prop('disabled', false);
                        // Ensure selection is cleared in Select2 UI after repopulation
                        $districtSelect.val(null).trigger('change');
                    },
                    error: function(){
                        // In case of error, reset and re-enable
                        $districtSelect.html('<option value="">Select</option>');
                        $districtSelect.prop('disabled', false);
                        $districtSelect.val(null).trigger('change');
                    }
                });
                } else {
                    // If no center selected, clear and disable the district list
                    $districtSelect.val(null).trigger('change');
                    $districtSelect.html('<option value="">Select</option>');
                    $districtSelect.prop('disabled', true);
                }
            });
        </script>