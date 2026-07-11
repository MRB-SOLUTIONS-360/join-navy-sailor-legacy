<?php

use yii\bootstrap5\Html;
use yii\helpers\Url;

?>

<div class="leftside-menu">
    <!-- Brand Logo Light -->
    <a href="<?= Url::home(); ?>" class="logo logo-light">
        <span class="logo-lg">
            <img src="<?= Yii::getAlias('@web'); ?>/adminAsset/images/navy_logo.png" alt="logo">
            <h4 style="color: white;">Join Bangladesh Navy</h4>
        </span>
        <span class="logo-sm">
            <img src="<?= Yii::getAlias('@web'); ?>/adminAsset/images/navy_logo.png" alt="small logo">
        </span>
    </a>
    <!-- Sidebar Hover Menu Toggle Button -->
    <div class="button-sm-hover" data-bs-toggle="tooltip" data-bs-placement="right" title="Show Full Sidebar">
        <i class="ri-checkbox-blank-circle-line align-middle"></i>
    </div>
    <!-- Full Sidebar Menu Close Button -->
    <div class="button-close-fullsidebar">
        <i class="ri-close-fill align-middle"></i>
    </div>
    <!-- Sidebar -left -->
    <div class="h-100" id="leftside-menu-container" data-simplebar>
        <!--- Sidemenu -->
        <ul class="side-nav">
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sailor-application" aria-expanded="false" aria-controls="sidebarEcommerce" class="side-nav-link">
                    <i class="uil-store"></i>
                    <span> Sailor</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sailor-application">
                    <ul class="side-nav-second-level">

                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> <span> Sailor  </span>', Url::toRoute(['sailors/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Reference', Url::toRoute(['sailors/reference-candidate']), ['class' => 'side-nav-link']) ?>
                        </li>
                          <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> <span> Cancel Request  </span>', Url::toRoute(['sailors/cancel-request']), ['class' => 'side-nav-link']) ?>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#desailor-application" aria-expanded="false" aria-controls="sidebarEcommerce" class="side-nav-link">
                    <i class="uil-store"></i>
                    <span> DE Sailor</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="desailor-application">
                    <ul class="side-nav-second-level">
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> <span> Sailor  </span>', Url::toRoute(['de-sailors/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Reference', Url::toRoute(['de-sailors/reference-candidate']), ['class' => 'side-nav-link']) ?>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sailor-global-setting" aria-expanded="false" aria-controls="sidebarEcommerce" class="side-nav-link">
                    <i class="uil-store"></i>
                    <span> Sailor Setting </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sailor-global-setting">
                    <ul class="side-nav-second-level">
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> District', Url::toRoute(['districts/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Upozilas', Url::toRoute(['upozilas/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Unions', Url::toRoute(['unions/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Subject', Url::toRoute(['subjects/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Candidate Desig.', Url::toRoute(['can-designation/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Eligibility', Url::toRoute(['eligibility/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Center', Url::toRoute(['sailor-centers/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Dist. Mapping', Url::toRoute(['sailor-cent-dist-mapping/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Batch', Url::toRoute(['sailor-batchs/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Batch Config.', Url::toRoute(['sailor-batch-configuration/index']), ['class' => 'side-nav-link']) ?>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sailor-report" aria-expanded="false" aria-controls="sidebarEcommerce" class="side-nav-link">
                    <i class="uil-store"></i>
                    <span> Sailor Report </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sailor-report">
                    <ul class="side-nav-second-level">
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Candidate', Url::toRoute(['report/candidate-filter']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> District Candidate', Url::toRoute(['report/district-candidate']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Center Candidate', Url::toRoute(['report/center-candidate']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Center & Date Candidate', Url::toRoute(['report/center-date-candidate']), ['class' => 'side-nav-link']) ?>
                        </li>
                       
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Payment', Url::toRoute(['report/payment']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Monitoring', Url::toRoute(['report/monitoring-application']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Date Monitoring', Url::toRoute(['report/exam-date-check']), ['class' => 'side-nav-link']) ?>
                        </li>

                        <?php if (!Yii::$app->user->isGuest && in_array(Yii::$app->user->id, [1])) { ?>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> JSON For LS', Url::toRoute(['report/json-for-ls']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <?php } ?>


                    </ul>
                </div>
            </li>


            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#de-sailor-report" aria-expanded="false" aria-controls="sidebarEcommerce" class="side-nav-link">
                    <i class="uil-store"></i>
                    <span> DE Sailor Report </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="de-sailor-report">
                    <ul class="side-nav-second-level">
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Candidate', Url::toRoute(['de-sailor-report/candidate-filter']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Payment', Url::toRoute(['de-sailor-report/payment']), ['class' => 'side-nav-link']) ?>
                        </li>
                        <li>
                            <?= Html::a(' <i class="uil-comments-alt"></i> Monitoring', Url::toRoute(['de-sailor-report/monitoring-application']), ['class' => 'side-nav-link']) ?>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <?= Html::a(' <i class="uil-comments-alt"></i> <span> User </span>', Url::toRoute(['user/index']), ['class' => 'side-nav-link']) ?>
            </li>
        </ul>
        <!--- End Sidemenu -->
        <div class="clearfix"></div>
    </div>
</div>