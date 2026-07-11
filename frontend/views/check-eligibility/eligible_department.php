<?php

use Carbon\Carbon;
use common\models\CanDesignation;
use common\static\StaticMethod;
use frontend\components\SupportNo;
use Symfony\Component\VarDumper\Cloner\Data;
use yii\bootstrap5\Html;

$this->title = 'Join Bangladesh Navy';
?>
<div class="data-form-area pb-120 pt-120" style="background: #001731;">
    <div class="container">
         <?= SupportNo::widget(['steps' => [1], 'slug' => Yii::$app->getRequest()->getQueryParam('slug'), 'show_form_text'=>false]) ?>
        <div class="signup__wrap">
             
            <div class="section-title section-title-white">
                <h1 style="text-transform:uppercase; text-align: center;">Eligible Department</h1>
                <div class="text-center mt-1">
                    <?php
                    echo '<p style="color:yellow">Your date of birth is ' . date('d M Y', strtotime($model['dob'])) . '</p>';
                    if ($candidate_age) {
                        foreach ($candidate_age as $kk => $age) {
                            $exp = explode('.', $age);
                            echo '<p style="color:yellow">Your age is ' . $exp[0] . ' years ' . $exp[1] . ' months ' . $exp[2] . ' days according to circular. </p>';
                        }
                    }
                    ?>
                </div>
            </div>
            <div class="wizard" style="margin-top: 40px;">
                <div class="wizard-inner py-lg-2">
                    <ul class="d-flex flex-wrap flex-md-nowrap">
                        <li class="active single__step flex-grow-1">
                            <div class="index-count">
                                <span>
                                    01
                                </span>
                            </div>
                            <div class="step_disc">
                                <h5>Personal Details</h5>
                            </div>
                        </li>
                        <li class="active single__step flex-grow-1">
                            <div class="index-count">
                                <span>
                                    02
                                </span>
                            </div>
                            <div class="step_disc">
                                <h5>Acadamic Details</h5>
                            </div>
                        </li>
                        <li class="active single__step flex-grow-1">
                            <div class="index-count">
                                <span>
                                    03
                                </span>
                            </div>
                            <div class="step_disc">
                                <h5>Available Position</h5>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            &zwj;
            <div class="row justify-content-center mt-4">
                <?php if (Yii::$app->session->hasFlash('error')) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= Yii::$app->session->getFlash('error') ?>
                    </div>
                <?php endif; ?>
                <?php

                if ($eligibility_data_model) {
                ?>
                    <h6 style="color: #fff; text-align: center">Congratulations. You are eligible for following department.</h6>
                    <div class="table__overflow">
                        <table class="table position_data__table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Department</th>
                                    <th scope="col">Description</th>
                                    <th scope="col" class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                /// $can_designation = CanDesignation::getAllDesignation();
                                $can_designation = CanDesignation::getAllDesignationForEligibilityPage();

                                // echo '<pre>';
                                // print_r($config_desig_candidate_type_wise);
                                // echo '</pre>';


                                $eligible_department = [];
                                foreach ($eligibility_data_model as $k => $val2) {
                                    // $designation = (array_key_exists($val2['candidate_designation'], $can_designation)) ? $can_designation[$val2['candidate_designation']] : '';
                                    // $designation= (array_key_exists($val2['candidate_designation'], $can_designation)) ? $can_designation[$val2['candidate_designation']]['name_en'] : '';
                                    $designation = $description =  '';
                                    if (array_key_exists($val2['candidate_designation'], $can_designation)) {
                                        $designation = $can_designation[$val2['candidate_designation']]['name_en'];
                                        $description = $can_designation[$val2['candidate_designation']]['description'];
                                    }

                                    $allow_dept_for_district = ($config_desig_candidate_type_wise && array_key_exists($val2['candidate_type'], $config_desig_candidate_type_wise)) ? $config_desig_candidate_type_wise[$val2['candidate_type']] : [];
                                ?>
                                    <tr>
                                        <th scope="row"><?= ($k + 1) ?></th>

                                        <td><?php echo $designation; // $val2['id'] . '--' . $val2['candidate_type']  . '--' .
                                            ?></td>
                                        <td>
                                            <?php
                                            if (!empty($description)) {
                                            ?>
                                                <p style="cursor: pointer; color: yellow;" class="show_modal" data-id="<?= $val2['candidate_designation']; ?>">Details</p>
                                            <?php
                                            }
                                            ?>

                                        </td>
                                        <td class="text-end">
                                            <?php
                                            if (!empty($allow_batch_candidate_type)) {
                                                if ($allow_dept_for_district && in_array($val2['candidate_designation'], $allow_dept_for_district)) {
                                                    $eligible_department[] = $val2['candidate_designation'];
                                                    echo Html::a('Apply', ['/check-eligibility/apply-department', 'slug' => Yii::$app->getRequest()->getQueryParam('slug'), 'adpt' => StaticMethod::encryptPk($val2['candidate_designation'])], ['class' => 'common-btn bg-yellow', 'style' => 'padding:1px 3px; display:inline-flex']);

                                                    // if (Yii::$app->user->getIsGuest())
                                                    //     echo 'Apply now r f';
                                                    // else
                                                    //     echo 'App';
                                                } else {
                                                    echo '<span class="district_not__allow">Your district is not allow for this department <br/> or batch setting missing.<span/>';
                                                }
                                            } else echo '<span class="district_not__allow">Sorry. There is no running batch<span/>';
                                            ?>
                                        </td>
                                    </tr>
                                <?php }

                                // set session for eligible_department
                                // it will store in db when click apply button  
                                $session = Yii::$app->session;
                                $session['eligible_department'] = $eligible_department;

                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <h6 style="text-align: center;"><span class="district_not__allow"> Sorry! You are not eligible for any department.</span></h6>
                <?php } ?>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        $('.show_modal').click(function() {
            let data_id = $(this).attr('data-id');
            let desig = $(this).attr('data-desig');
            if (data_id) {
                $.ajax({
                    url: '<?php echo Yii::$app->request->baseUrl . '/check-eligibility/get-description' ?>',
                    cache: false,
                    type: "POST",
                    dataType: "json",
                    data: {
                        id: data_id
                    },
                    success: function(data) {
                        $('#exampleModalLabel').html('Job Description of  ' + data.name_en);
                        $('.modal-body').html(data.description);
                        $("#exampleModal").modal('show');
                    }
                });
            }
        })
    });
</script>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            </div>
        </div>
    </div>
</div>