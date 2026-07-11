<?php

use common\models\CanDesignation;
use common\models\SailorCenters;
use common\static\StaticMethod;
use Mpdf\Tag\Center;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\jui\DatePicker;

/** @var yii\web\View $this */
/** @var common\models\Sailors $model */
/** @var yii\widgets\ActiveForm $form */
$this->title = 'Update Reference Candidate'
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Sailor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reference Candidate</li>
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
                    <?php $form = ActiveForm::begin([]); ?>
                    <div class="row ">
                        <div class="col-lg-12">
                            <?= $form->field($model, 'serial_no', ['enableAjaxValidation' => true])->textInput(['readonly' => true]) ?>
                        </div>
                        <div class="col-lg-12">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sl</th>
                                        <th>Referred By</th>
                                        <th>Relationship</th>
                                        <th>Reference Details</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($model->referred_by) {
                                        $referred_by = json_decode($model->referred_by, true);
                                        $relationship = json_decode($model->relationship, true);
                                        $reference_details = json_decode($model->reference_details, true);
                                        $j = 1;
                                        foreach ($referred_by as $k => $val) {
                                    ?>
                                            <tr id="data_<?= ($k + 1) ?>">
                                                <td> <?= $j ?></td>
                                                <td><?= Html::textarea("referred_by[$k]", $val, ['class' => 'form-control']) ?></td>
                                                <td><?= Html::textarea("relationship[$k]", $relationship[$k], ['class' => 'form-control']) ?> </td>
                                                <td> <?= Html::textarea("reference_details[$k]", $reference_details[$k], ['class' => 'form-control']) ?></td>
                                                <td>
                                                    <span class="delete_row" data-id="<?= ($k + 1) ?>">
                                                        <svg aria-hidden="true" style="cursor: pointer;color:red;display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:.875em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                                            <path fill="currentColor" d="M32 464a48 48 0 0048 48h288a48 48 0 0048-48V128H32zm272-256a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zm-96 0a16 16 0 0132 0v224a16 16 0 01-32 0zM432 32H312l-9-19a24 24 0 00-22-13H167a24 24 0 00-22 13l-9 19H16A16 16 0 000 48v32a16 16 0 0016 16h416a16 16 0 0016-16V48a16 16 0 00-16-16z"></path>
                                                        </svg>
                                                    </span>
                                                </td>
                                            </tr>
                                    <?php  $j++; }
                                    } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Update'), ['class' => 'btn btn-success']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.delete_row').click(function() {
            let this_row = $(this).attr('data-id');
            if(confirm('Are you sure to remove this? ') === true){
                $("#data_"+this_row).remove();
            } 
        })
    })
</script>