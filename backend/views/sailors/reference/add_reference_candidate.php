<?php
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Sailors $model */
/** @var yii\widgets\ActiveForm $form */
$this->title = 'Add Reference Candidate'
?>
<style>
    .cursor-disabled {
  cursor: not-allowed !important;
}
    </style>
<script>
    $(document).ready(function() {
        $('#sailorsreference-serial_no').blur(function() {
            let roll = $(this).val();
            $("#show_details").hide();
            if (roll) {
                $('#is_cancel_application').html('')
                $('#save_btn').prop('disabled', false);
               

                $.ajax({
                    url: '<?php echo Yii::$app->request->baseUrl . '/ajax/get-sailor-information-by-roll' ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        roll: roll,
                    },
                    beforeSend: function() {
                        $('#preloader').css('display', 'flex');
                    },
                    success: function(data) {
                        $('#preloader').css('display', 'none');
                        if(data.have_data == 'yes'){
                            $("#show_details").show();
                            $('#can_name').html(data.data.name)
                            $('#el_dist').html(data.data.eligible_district)
                            $('#per_dist').html(data.data.permanent_district)                         
                          
                            if(data.data.referred_by){
                                let tableHTML = '<table class="table table-striped table-bordered">';
                                tableHTML += '<thead><tr><th>Referred By</th><th>Relationship</th><th>Reference Details</th><th>Reference Added</th></tr></thead>';
                                tableHTML += '<tbody>';
                                const referred_by = JSON.parse(data.data.referred_by);
                                const relationship = JSON.parse(data.data.relationship);
                                const reference_details = JSON.parse(data.data.reference_details);   
                                const reference_add_on = data.data.reference_add_on ? JSON.parse(data.data.reference_add_on) : [];
                                
                                for (let i = 0; i < referred_by.length; i++) {
                                    const reference_add_on_date = reference_add_on[i] ?  reference_add_on[i] : '';
                                    tableHTML += '<tr><td>' + referred_by[i] + '</td><td>' + relationship[i] + '</td><td>' + reference_details[i] + '</td><td>' + reference_add_on_date + '</td></tr>';
                                }
                                tableHTML += '</tbody></table>';
                                $('#referred_by').html(tableHTML);
                            }

                            if(data.is_cancel_application =='yes'){
                                $('#is_cancel_application').html('It\'s a cancelled application. You can\'t add reference against this Roll No.')
                                $('#save_btn').prop('disabled', true);
                         
                            } 
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
                        <h3 id="is_cancel_application" style="color:rgba(236, 11, 4, 0.98); text-align:center"> </h3>
                    </div>

                    <?php $form = ActiveForm::begin([]); ?>
                    <div class="row ">
                        <div class="col-lg-6">
                            <?= $form->field($model, 'serial_no', ['enableAjaxValidation' => true])->textInput(['maxlength' => true])  ?>
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
                        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success','id'=>'save_btn']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>

                    <div id="referred_by" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>
</div>