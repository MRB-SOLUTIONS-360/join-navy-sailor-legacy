<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Sailors $model */
/** @var yii\widgets\ActiveForm $form */
$this->title = 'Add Reference Candidate'
?>
<script>
    $(document).ready(function() {
        $('#desailorsreference-serial_no').blur(function() {
            let roll = $(this).val();
            $("#show_details").hide();
            if (roll) {
                $.ajax({
                    url: '<?php echo Yii::$app->request->baseUrl . '/ajax/get-de-sailor-information-by-roll' ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        roll: roll,
                    },
                    beforeSend: function() {
                        $('#preloader').css('display', 'flex');
                    },
                    success: function(data) {
                        console.log(data);
                        $('#preloader').css('display', 'none');
                        if (data.have_data == 'yes') {
                            $("#show_details").show();
                            $('#can_name').html(data.data.name)
                            $('#el_dist').html(data.data.eligible_district)
                            $('#per_dist').html(data.data.permanent_district)
                        }
                    }
                });
            }
        })
    })
</script>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Sailor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reference Candidate</li>
        </ol>
    </nav>
</div>
<div id="preloader" style="position: fixed; top: 0; left: 0; inset: 0; z-index: 9999;  background: rgba(0, 0, 0, .5); display: none; justify-content: center; align-items:center">
    <div class="spinner-grow" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
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

                    <div class="row" style="display: none;" id="show_details">
                        <h3 style="color: red;">Name : <span id="can_name"> </span></h3>
                        <h3 style="color: #0f43ff;">Eligible District : <span id="el_dist"> </span></h3>
                        <h3 style="color: #ff0fe1d9;">Permanent District : <span id="per_dist"> </span></h3>
                    </div>

                    <?php $form = ActiveForm::begin([]); ?>
                    <div class="row ">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'serial_no', ['enableAjaxValidation' => true])->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'referred_by')->textarea(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'relationship')->textarea(['maxlength' => true]) ?>
                        </div>
                        <div class="col-lg-6">
                            <?= $form->field($model, 'reference_details')->textarea(['maxlength' => true]) ?>
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