<?php

use common\models\CanDesignation;
use common\models\SailorBatchs;
use common\static\Constants;
use common\static\StaticMethod;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Join Bangladesh Navy';

?>

<div class="data-form-area pb-120 pt-120" style="background: #001731;">
    <div class="container">
        <div class="signup__wrap text-white" style="max-width:unset">

            <div class="text-center">
                <h3>My Application List</h3>
            </div>
            <div class="text-center" style="margin-top: 10px;">
                <h3 class="bangla_font" style="color:red;">স্লিপ ডাউনলোড করতে সমস্যা হলে কিছু সময় পরে আবার চেষ্টা করুন</h3>
            </div>



            <?php if (Yii::$app->session->hasFlash('application_close')) : ?>
                <div class="alert alert-danger" role="alert">
                    <?= Yii::$app->session->getFlash('application_close') ?>
                </div>
            <?php endif; ?>

            <?php if (Yii::$app->session->hasFlash('success_message')) : ?>
                <div class="alert alert-success" role="alert">
                    <?= Yii::$app->session->getFlash('success_message') ?>
                </div>
            <?php endif; ?>

            <div class="table__scroller mt-2">
                <table class="table table-responsive application_list__table text-white" style="overflow-x: scroll;">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Application Id</th>
                            <th>Designation</th>
                            <th>Name</th>
                            <th>Batch</th>
                            <th>Roll</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($model) {
                            foreach ($model as $k => $value) {   
                                                           
                                /// $batch = SailorBatchs::isBatchActiveAndRunning($value['batch_id']);  
                                $can_apply = SailorBatchs::isCandidateContinueApplication(batch_id: $value['batch_id'], isPaid: $value['payment_status']);

                        ?>
                                <tr>
                                    <td><?= ($k + 1) ?></td>
                                    <td><?= $value['app_unique_id']; ?></td>
                                    <td><?= $value['candidate_designation'] ? CanDesignation::getAllDesignationSession($value['candidate_designation']) : ''; ?>
                                    </td>
                                    <td><?= $value['name']; ?></td>
                                    <td><?= $can_apply['name_en'] ?? ''; ?></td>
                                    <td><?= $value['serial_no']; ?></td>
                                    <td>
                                        <?php
                                        if ($can_apply['can_apply'] ==  Constants::TEXT_NO) {
                                            echo '<p>Application date is over</p>';
                                        } else {

                                            if ($value['application_status'] == Constants::YES && $value['request_for_cancel'] != Constants::YES) {
                                                $encrypt_pk = StaticMethod::encryptPk($value['id']);
                                                if ($value['phase'] === Constants::SAILOR_PHASE_ONE || $value['payment_status'] == Constants::PAYMENT_UNPAID) // go payment page 
                                                    echo Html::a('Continue Payment', ['/sailor-candidate/payment/', 'slug' => $encrypt_pk], ['class' => 'action__btn', 'style' => 'background:#8500febf']);
                                                elseif ($value['payment_status'] == Constants::PAYMENT_PAID) {
                                                    if ($value['serial_no'])
                                                        echo Html::a('Download Slip', ['/sailor-candidate/download/', 'slug' => $encrypt_pk], ['class' => 'action__btn success', 'style' => 'background:#8500febf', 'target' => '_blank']);
                                                    else {
                                                        if ($value['phase'] == Constants::SAILOR_PHASE_TWO)
                                                            echo Html::a('Continue', ['/sailor-candidate/academic-info/', 'slug' => $encrypt_pk], ['class' => 'action__btn', 'style' => 'background:#8500febf']);
                                                        else if ($value['phase'] == Constants::SAILOR_PHASE_THREE)
                                                            echo Html::a('Continue', ['/sailor-candidate/personal-info/', 'slug' => $encrypt_pk], [
                                                                'class' => 'action__btn',
                                                                'style' => 'background:#8500febf',
                                                                'data' => [
                                                                    'method' => 'post',
                                                                ],
                                                            ]);
                                                        else if ($value['phase'] == Constants::SAILOR_PHASE_FOUR || ($value['phase'] == Constants::SAILOR_PHASE_FIVE && empty($value['serial_no'])))
                                                            echo Html::a('Continue', ['/sailor-candidate/application-preview/', 'slug' => $encrypt_pk], ['class' => 'action__btn', 'style' => 'background:#8500febf']);
                                                        else if ($value['phase'] == Constants::SAILOR_PHASE_FIVE && $value['serial_no'])
                                                            echo Html::a('Download Slip', ['/sailor-candidate/download/', 'slug' => $encrypt_pk], ['class' => 'action__btn success', 'style' => 'background:#8500febf', 'target' => '_blank']);
                                                    }
                                             
                                                    echo '&nbsp;<button type="button" class="action__btn success" style="background:red;border:0; color:white" onclick="openCancelModal(\''.$encrypt_pk.'\')">Cancel Application </button>';
                                                  
                                                   
                                                }
                                            } else {
                                                if ($value['request_for_cancel'] == Constants::YES && $value['application_status'] == Constants::YES ) {
                                                        // echo '&nbsp;<button type="button" class="action__btn success" style="background:red;color:white" disabled>Cancel Request Sent</button>';
                                                          echo Html::a('Cancel Request Sent', '#', ['class' => 'action__btn success', 'style' => 'background:red']); 
                                                    }else {
                                                        echo Html::a('Canceled Application', '#', ['class' => 'action__btn success', 'style' => 'background:red']); 
                                                    }
                                            
                                            }
                                            // echo '<br/>orig ' . $value['id'] . '--enc-' . $encrypt_pk . ', dec ' . StaticMethod::decryptPk($encrypt_pk);
                                        }
                                        ?>

                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7">No Record Found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <table class="table table-responsive application_list__table text-white" style="overflow-x: scroll;">
                    <thead>
                        <tr>
                            <th>Sl</th>
                            <th>Application Id</th>
                            <th>Designation</th>
                            <th>Name</th>
                            <th>Batch</th>
                            <th>Roll</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($model_de) {
                            foreach ($model_de as $k => $value) {
                                /// $batch = SailorBatchs::isBatchActiveAndRunning($value['batch_id']);  
                                $can_apply = SailorBatchs::isCandidateContinueApplication(batch_id: $value['batch_id'], isPaid: $value['payment_status']);

                        ?>
                                <tr>
                                    <td><?= ($k + 1) ?></td>
                                    <td><?= $value['app_unique_id']; ?></td>
                                    <td><?= $value['candidate_designation'] ? CanDesignation::getAllDesignationSession($value['candidate_designation']) : ''; ?>
                                    </td>
                                    <td><?= $value['name']; ?></td>
                                    <td><?= $can_apply['name_en'] ?? ''; ?></td>
                                    <td><?= $value['serial_no']; ?></td>
                                    <td>
                                        <?php
                                        if ($can_apply['can_apply'] ==  Constants::TEXT_NO) {
                                            echo '<p>Application date is over</p>';
                                        } else {

                                            if ($value['application_status'] == Constants::YES) {
                                                $encrypt_pk = StaticMethod::encryptPk($value['id']);
                                                if ($value['phase'] === Constants::SAILOR_PHASE_ONE || $value['payment_status'] == Constants::PAYMENT_UNPAID) // go payment page 
                                                    echo Html::a('Continue Payment', ['/de-sailor/payment/', 'slug' => $encrypt_pk], ['class' => 'action__btn', 'style' => 'background:#8500febf']);
                                                elseif ($value['payment_status'] == Constants::PAYMENT_PAID) {
                                                    if ($value['serial_no'])
                                                        echo Html::a('Download Slip', ['/de-sailor/download/', 'slug' => $encrypt_pk], ['class' => 'action__btn success', 'style' => 'background:#8500febf', 'target' => '_blank']);
                                                    else {
                                                        if ($value['phase'] == Constants::SAILOR_PHASE_TWO)
                                                            echo Html::a('Continue', ['/de-sailor/academic-info/', 'slug' => $encrypt_pk], ['class' => 'action__btn', 'style' => 'background:#8500febf']);
                                                        else if ($value['phase'] == Constants::SAILOR_PHASE_THREE)
                                                            echo Html::a('Continue', ['/de-sailor/personal-info/', 'slug' => $encrypt_pk], [
                                                                'class' => 'action__btn',
                                                                'style' => 'background:#8500febf',
                                                                'data' => [
                                                                    'method' => 'post',
                                                                ],
                                                            ]);
                                                        else if ($value['phase'] == Constants::SAILOR_PHASE_FOUR || ($value['phase'] == Constants::SAILOR_PHASE_FIVE && empty($value['serial_no'])))
                                                            echo Html::a('Continue', ['/de-sailor/application-preview/', 'slug' => $encrypt_pk], ['class' => 'action__btn', 'style' => 'background:#8500febf']);
                                                        else if ($value['phase'] == Constants::SAILOR_PHASE_FIVE && $value['serial_no'])
                                                            echo Html::a('Download Slip', ['/de-sailor/download/', 'slug' => $encrypt_pk], ['class' => 'action__btn success', 'style' => 'background:#8500febf', 'target' => '_blank']);
                                                    }
                                                }
                                            } else {
                                                echo Html::a('Canceled Application', '#', ['class' => 'action__btn success', 'style' => 'background:red']);
                                            }
                                            // echo '<br/>orig ' . $value['id'] . '--enc-' . $encrypt_pk . ', dec ' . StaticMethod::decryptPk($encrypt_pk);
                                        }
                                        ?>

                                    </td>
                                </tr>
                            <?php }
                        } else { ?>
                            <tr>
                                <td colspan="7">No Record Found</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>






            </div>
        </div>
        <!-- Cancel Application Confirmation Modal -->
        <div class="modal fade" id="cancelApplicationModal" tabindex="-1" aria-labelledby="cancelApplicationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cancelApplicationModalLabel">Cancel Application Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">                      
                        <div class="mb-3">
                            <label for="cancelReason" class="form-label" style="color:black">Please enter the reason for cancellation:</label>
                            <textarea class="form-control" id="cancelReason" rows="3" placeholder="Enter reason here..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-danger" onclick="confirmCancelApplication()">Confirm Cancel</button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Success Modal -->
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">                    
                    <div class="modal-body">
                        <div class="text-center">
                            <div class="mb-3">
                                <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745;"></i>
                            </div>
                            <p>Your request has been submitted successfully. Wait for confirmation.</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" data-bs-dismiss="modal" onclick="window.location.reload()">OK</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        let currentApplicationId = '';
        
        function openCancelModal(applicationId) {
            currentApplicationId = applicationId;
            document.getElementById('cancelReason').value = '';
            var modal = new bootstrap.Modal(document.getElementById('cancelApplicationModal'));
            modal.show();
        }
        
        function confirmCancelApplication() {
            const reason = document.getElementById('cancelReason').value.trim();
            
            
            
            // Show loading state
            const confirmBtn = event.target;
            const originalText = confirmBtn.textContent;
            confirmBtn.disabled = true;
            confirmBtn.textContent = 'Processing...';
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            // Prepare data for AJAX
            // const formData = new FormData();
            // formData.append('slug', currentApplicationId);
            // formData.append('reason', reason);
            // if (csrfToken) {
            //     formData.append('_csrf', csrfToken);
            // }
            
            // Send AJAX request
            fetch('/sailor-candidate/cancel-application', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    slug: currentApplicationId,
                    reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
              
                if (data.success) {
                    // Close cancel modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('cancelApplicationModal'));
                    modal.hide();
                    
                    // Show success modal
                    setTimeout(() => {
                        const successModal = new bootstrap.Modal(document.getElementById('successModal'));
                        successModal.show();
                    }, 300);
                } else {
                    if (data.errors && data.errors.reason) {
                        // Add error styling and message to the textarea
                        const textarea = document.getElementById('cancelReason');
                        textarea.style.borderColor = 'red';
                        textarea.style.borderWidth = '2px';
                        
                        // Create or update error message element
                        let errorDiv = document.getElementById('cancelReasonError');
                        if (!errorDiv) {
                            errorDiv = document.createElement('div');
                            errorDiv.id = 'cancelReasonError';
                            errorDiv.style.color = 'red';
                            errorDiv.style.fontSize = '14px';
                            errorDiv.style.marginTop = '5px';
                            textarea.parentNode.appendChild(errorDiv);
                        }
                        errorDiv.textContent = data.errors.reason;
                        
                        // Focus on the textarea
                        textarea.focus();
                    } else {
                        alert(data.message || 'Failed to cancel application. Please try again.');
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            })
            .finally(() => {
                // Restore button state
                confirmBtn.disabled = false;
                confirmBtn.textContent = originalText;
            });
        }
        </script>
        
        <div class="row- mt-2" style="text-align: center;">
            <?php
            echo Html::a('Check Eligibility', ['/'], ['class' => 'action__btn success',]);
            ?>
        </div>
    </div>
</div>