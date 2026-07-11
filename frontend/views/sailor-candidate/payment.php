<?php

use common\models\CanDesignation;
use common\static\Constants;
use common\static\StaticMethod;
use frontend\components\StepAndSupport;
use frontend\components\SupportNo;
use yii\bootstrap5\ActiveForm;
use yii\bootstrap5\Modal;
use yii\helpers\Html;

$this->title = 'Join Bangladesh Navy';

?>

<script>
    var secretKeyPrompt = true;
</script>

<div class="data-form-area pb-120" style="padding-top: 170px; background: #001731;">
    <div class="container">

        <?= SupportNo::widget(['steps' => [1], 'slug' => Yii::$app->getRequest()->getQueryParam('slug')]) ?>

        <div class="user_info__wrap">
            <?php
            echo StepAndSupport::widget(['steps' => [1], 'slug' => Yii::$app->getRequest()->getQueryParam('slug')])
            ?>
            <?php
            if ($batch_setting_info && $batch_setting_info['is_batch_live_mode'] == Constants::NO) {
            ?>
                <script type="text/javascript">
                    var stgPin = '<?= $batch_setting_info['secrate_key']; ?>';
                    $(document).ready(function() {
                        $('#form_submit').click(function() {
                            var person = prompt("Please Provide Secret Key  ");
                            if (person == stgPin) {
                                secretKeyPrompt = true;
                                return true;
                            } else {
                                secretKeyPrompt = false;
                                return false;

                            }
                        });
                    })
                </script>
            <?php } ?>

            <?php
            if ($batch_setting_info && $batch_setting_info['allow_refund'] == Constants::YES) {
            ?>
                <script type="text/javascript">
                    $(document).ready(function() {
                        $('#form_submit').click(function(e) {
                            e.preventDefault();
                            if (secretKeyPrompt)
                                $('#confirmModal').modal('show');
                        });
                        // Handle the modal confirmation
                        $('#modalSubmit').click(function(e) {
                            $("#refund-phone-error").html('')
                            $('.invalid-feedback').hide();

                            e.preventDefault();

                            $.ajax({
                                url: $('#modal-form').attr('action'), // Get the action URL of the modal form
                                type: 'POST',
                                dataType: 'json',
                                data: $('#modal-form').serialize(), // Serialize the form data
                                success: function(res) {
                                    if (res.success) {
                                        $("#refund-phone-error").html('')
                                        $('.invalid-feedback').hide();

                                        // Optionally, you can handle any response here, such as showing a success message
                                        // console.log('Modal form submitted successfully');

                                        // // Close the modal
                                        $('#confirmModal').modal('hide');

                                        // // Submit the main form after modal form submission
                                        $('#sailor-can-payment').submit();
                                    } else {
                                        // Handle errors if needed
                                        $("#refund-phone-error").html(res?.errors?.refund_phone || 'Validation error')
                                        $('.invalid-feedback').show();
                                    }

                                },
                                error: function(xhr, status, error) {
                                    alert('There was an error submitting the form.');
                                }

                            })
                            // Close the modal
                            // $('#confirmModal').modal('hide');
                            // // Submit the form after confirmation
                            // $('#my-form').submit();
                        });

                    })
                </script>

                <?php
                // Modal Definition
                Modal::begin([
                    'title' => '<span class="text-danger">বিশেষ বার্তা </span>',
                    'id' => 'confirmModal',
                    'size' => 'modal-lg',
                    'footer' => Html::button('Cancel', ['class' => 'btn btn-secondary', 'data-dismiss' => 'modal'])
                        . Html::button('Confirm', ['class' => 'btn btn-primary', 'id' => 'modalSubmit']),
                ]);

                echo "<p class='text-danger mt-3 mb-3'>যদি আবেদন সম্পূর্ণ না করা হয়, তাহলে আপনার প্রদত্ত অর্থ রিফান্ড দেওয়া হবে, তবে প্রসেসিং ফি কেটে রাখা হবে। যে মাধ্যমে পেমেন্ট প্রদান করেছেন, সেই একই মাধ্যমে রিফান্ড করা হবে।</p>";
                echo "<p class='text-danger mt-3 mb-3'>পরবর্তী যোগাযোগ এর জন্য আপনার ফোন নাম্বার দিন।</p>";
                // Modal's inner form (form inside the modal)
                $formModal = ActiveForm::begin([
                    'id' => 'modal-form', // Form inside the modal
                    'action' => ['/sailor-candidate/refund-phone'], // Action for modal form
                    'options' => ['method' => 'post'],
                ]);
                // echo $formModal->field($model, 'refund_phone');
                echo $formModal->field($model, 'refund_phone', [
                    'errorOptions' => ['id' => 'refund-phone-error']
                ])->label(false);
                echo $formModal->field($model, 'id')->hiddenInput()->label(false);
                ActiveForm::end();
                Modal::end();
                ?>
            <?php } ?>





            <div class="row justify-content-center text-white" style="margin-top: 40px;">
                <div class="row gy-2">
                    <div class="col-lg-6">
                        <strong class="bangla_font" style=""> আবেদনের শাখা : <span style="font-family: auto;"><?= CanDesignation::getAllDesignationSession($model->candidate_designation) ?></span></strong>
                    </div>
                    <div class="col-lg-6 text-lg-end">
                        <strong class="bangla_font">আবেদন নম্বর : <span style="font-family: auto;"><?= $model->app_unique_id; ?></span></strong>
                    </div>
                </div>

                <div class="col-lg-12">
                    <?php if (Yii::$app->session->hasFlash('error')): ?>
                        <div class="alert alert-danger">
                            <?= Yii::$app->session->getFlash('error') ?>
                        </div>
                    <?php endif; ?>
                    <div class="block__title mt-3 mb-2">
                        <h5 class="bangla_font mb-2">পেমেন্টের ধরন এবং জন্ম নিবন্ধন নম্বর </h5>
                    </div>
                    <?php $form = ActiveForm::begin([
                        'id' => 'sailor-can-payment',
                        // 'enableClientValidation' => true,
                        // 'validateOnBlur' => true,
                        'options' => [
                            'class' => 'data-form'
                        ],
                        'fieldConfig' => [
                            // 'template' => "{label}\n<div class=\"col-lg-8\">\n{input}\n{hint}\n{error}\n</div>",
                            'options' => ['class' => '52', 'style' => 'margin:0px'],
                            'labelOptions' => ['class' => 'd-inline-block mb-1'],
                            'horizontalCssClasses' => [
                                'label' => 'd-inline-block mb-1',
                                'offset' => '',
                                'wrapper' => '',
                                'hint' => '',
                            ],
                        ],
                    ]); ?>


                    <div class="mt-3 single_input__box_">
                        
                        <?= $form->field($model, 'birth_registration_no', ['enableAjaxValidation' => true])
                                        ->textInput(['class' => 'form-control height_auto_margin_0'])
                                        ->label($model->attributeLabelBangla()['birth_registration_no']); ?>
                    </div>


                    <div class="mt-3 single_input__box_">
                        <?= $form->field($model, 'payment_type', ['enableAjaxValidation' => true])
                            ->radioList(StaticMethod::paymentType())
                            ->label(); ?>
                    </div>

                    <p class="mt-4" style="color: #EC3C3D; font-weight: bold; font-family: auto;">
                        <span class="bangla_font">
                            পেমেন্ট সংক্রান্ত কোন সমস্যা অথবা কোন কারিগরি সমস্যার সমাধানের জন্য
                            নিজের ইউজার নেম ও সাকসেসফুল পেমেন্ট এর সম্পূর্ণ তথ্য সহ মেইল করুনঃ
                        </span>
                        joinnavy.help@gmail.com
                    </p>
                    <p class="bangla_font mt-1" style="color: #EC3C3D; font-weight: bold; margin-bottom: 0px; ">
                        আবেদন করার পর ফি এর টাকা অফেরৎযোগ্য এবং একজন ক্যান্ডিডেট চলমান ব্যাচ এ
                        শুধুমাত্র একবার আবেদন করতে পারবে।
                    </p>

                    <div class="mt-2">
                        <?= $form->field($model, 'agree_payment_terms')
                            ->radioList([1 => $model->getAttributeLabel('agree_payment_terms')])
                            ->label(false); ?>
                        <?php
                        // echo $form->field($model, 'agree_payment_terms')
                        //     ->radio(['style'=>'width:16px; height:16px; padding:0px'])
                        //     ->label();
                        ?>
                    </div>

                    <?php
                    if ($batch_setting_info && $batch_setting_info['is_batch_live_mode'] == Constants::NO) {
                    ?>
                        <div class="mt-2 text-center">
                            <p style="color: #EC3C3D; font-weight: bold; font-family: auto; font-size:28px;">
                                <span class="bangla_font">
                                    এটা টেস্ট ব্যাচ আপনি এখন আবেদন/পেমেন্ট করতে পারবেন না।
                                </span>
                            </p>
                        </div>
                    <?php } ?>

                    <div class="row form-check-btn-wrap">
                        <div class="col-lg-9" style="text-align: left;">
                            <?= Html::a('Terms and Condition', Yii::getAlias('@baseUrl') . '/media/policy/terms_and_condition.pdf', ['target' => '_blank', 'class' => 'common-btn bg-yellow']) ?> &nbsp; &nbsp; &nbsp;
                            <?= Html::a('Refund and Return Policy', Yii::getAlias('@baseUrl') . '/media/policy/refund_and_return_policy.pdf', ['target' => '_blank', 'class' => 'common-btn bg-yellow']) ?> &nbsp; &nbsp; &nbsp;
                            <?= Html::a('Privacy Policy', Yii::getAlias('@baseUrl') . '/media/policy/privacy_policy.pdf', ['target' => '_blank', 'class' => 'common-btn bg-yellow']) ?>
                        </div>
                        <div class="col-lg-3">
                            <div class="d-flex justify-content-end">
                                <?= Html::submitButton(Yii::t('app', 'Continue'), ['id' => 'form_submit', 'class' => 'common-btn bg-yellow']) ?>
                            </div>
                        </div>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>