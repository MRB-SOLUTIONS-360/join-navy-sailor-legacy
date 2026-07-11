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

                                            if ($value['application_status'] == Constants::YES) {
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
        <div class="row- mt-2" style="text-align: center;">
            <?php
            echo Html::a('Check Eligibility', ['/'], ['class' => 'action__btn success',]);
            ?>
        </div>
    </div>
</div>