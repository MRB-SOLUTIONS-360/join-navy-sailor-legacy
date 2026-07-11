<?php

use common\models\CanDesignation;
use common\models\SailorCenters;
use common\static\Constants;
use common\static\StaticMethod;

$this->title = 'Join Bangladesh Navy ';
?>

<div class="data-form-area pb-120" style="padding-top: 100px; background: #001731;">
    <div class="container">
        <div class="row justify-content-center text-white">
            <?php
            if ($model->application_status == Constants::YES) {
            ?>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-2">
                            <?php
                            // if ($model->qr_photo && file_exists(Yii::getAlias('@rootDirFilUpload') . $model->qr_photo)) {
                            //     echo '<img style="height:184px; width:184px;" src=' . Yii::getAlias('@rootMediaShow') . $model->qr_photo . ' alt="QR not found">';
                            // } else echo '&nbsp;'

                            // if ($model->qr_photo && Yii::$app->r2Storage->fileExists($model->qr_photo)) {
                            //     echo '<img style="height:140px; width:140px;" src=' . Yii::$app->r2Storage->fileUrl . $model->qr_photo . ' alt="QR not found">';
                            // } else  echo '&nbsp;<span style="color:red;">QR Code Missing</span>';
                            ?>
                        </div>
                        <div class="col-lg-8 text-center">
                            <img src="<?php echo Yii::getAlias('@rootMediaShow') . '/media/main_logo.png'; ?>" style=" width:110px; text-align: center; margin: 0 auto;">
                            <h2 class="bangla_font" style="font-size: 11pt; font-weight: bold;">বাংলাদেশ নৌবাহিনী</h2>
                            <h2 class="bangla_font" style="line-height: 20px; font-size: 11pt; font-weight: bold;">
                                <!-- নাবিক,মহিলা  --> নাবিক ও এমওডিসি (নৌ) পদে ভর্তির আবেদনপত্র
                            </h2>
                            <h2 class="bangla_font" style="line-height: 20px; font-size: 12pt; font-weight: bold;">আবেদন
                                নাম্বার: <?= $model->app_unique_id; ?></h2>
                            <h2 class="bangla_font" style="line-height: 20px; font-size: 12pt; font-weight: bold;">আবেদনের শাখা:
                                <?= CanDesignation::getAllDesignationSession($model->candidate_designation);  ?> </h2>
                            <h2 class="bangla_font" style="line-height: 20px; font-size: 12pt; font-weight: bold;">
                                কেন্দ্র: <?= SailorCenters::getAllCenterSession($model->center_id) ?> </h2>

                            <h2 data-roll='<?= $model->serial_no; ?>' class="bangla_font data_roll" style="line-height: 20px; font-size: 12pt; font-weight: bold; color: red;">
                                রোল নং: <?= $model->serial_no  ?> </h2>
                        </div>
                        <div class="col-lg-2">
                            <?php
                            if ($model->photo && Yii::$app->r2Storage->fileExists($model->photo)) {
                                echo '<img src=' . Yii::$app->r2Storage->fileUrl . $model->photo . ' alt="Image not found">';
                            } else echo '&nbsp;<span style="color:red;">Photo Missing</span>';
                            ?>
                        </div>
                    </div>
                </div>

                <div class="main-form-wrap overflow-hidden mt-4">
                    <div class="d-flex">
                        <div class="bangla_font flex-shrink-0" style="width: 33px">
                            ০১।
                        </div>
                        <div class="d-flex flex-grow-1">
                            <div class="flex-shrink-0">
                                <p class="bangla_font">প্রার্থীর পূর্ণ নাম:</p>
                            </div>
                            <div class="flex-grow-1 border-bottom-dashed mx-2">
                                <?= $model->name; ?>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex  mt-3 ">
                        <div class="bangla_font flex-shrink-0 mt-2" style="width: 33px ">
                            ০২।
                        </div>
                        <div class="d-flex flex-wrap flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-lg-50 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পিতার নাম:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->father_name; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-50 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পেশা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->father_occupation; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-50 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পিতার জাতীয় পরিচয় পত্র নম্বর:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->father_nid; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0 mt-2" style="width: 33px">
                            ০৩।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-lg-50 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">মাতার নাম:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->mother_name; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-50 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পেশা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->mother_occupation; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0 mt-2" style="width: 33px">
                            ০৪।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-100 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">বর্তমান ঠিকানা:</p>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">গ্রাম/বাসা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->current_village; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">ওয়ার্ড নং:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->current_word_no; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">ইউনিয়ন:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->current_union; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পোস্ট অফিস:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->current_post_office; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">থানা/উপজেলা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->current_thana; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">জেলা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= ucfirst($model->current_district); ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পোস্ট কোড:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->current_post_code; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">ফোন/মোবাইল:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->current_phone; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0 mt-2" style="width: 33px">
                            ০৫।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-100 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">স্থায়ী ঠিকানা:</p>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">গ্রাম/বাসা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->permanent_village; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">ওয়ার্ড নং:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->permanent_word_no; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">ইউনিয়ন:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->permanent_union; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পোস্ট অফিস:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->permanent_post_office; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">থানা/উপজেলা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->permanent_thana; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">জেলা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= ucfirst($model->permanent_district); ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পোস্ট কোড:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->permanent_post_code; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-48 mt-2 ms-3">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">ফোন/মোবাইল:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->permanent_phone; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0 mt-2" style="width: 33px">
                            ০৬।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-wrap flex-grow-1 w-100 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">অভিভাবকের নাম (পিতা জীবিত না থাকলে):</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->guardian_name; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-50 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">সম্পর্ক:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->guardian_relation; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-50 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">পেশা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->guardian_occupation; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-100 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">ঠিকানা:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->guardian_address; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0 mt-2" style="width: 33px">
                            ০৭।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-wrap w-100 mt-2 flex-wrap">
                                <div class="flex-shrink-lg-0">
                                    <p class="bangla_font">জন্ম তারিখ (মাধ্যমিক সনদপত্র/ নবম শ্রেণীর রেজিস্ট্রেশন কার্ড / টোপাস প্রার্থীদের ক্ষেত্রে জন্ম নিবন্ধন বা ভোটার আইডি কার্ড অনুযায়ী):</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $model->dob; ?>
                                </div>
                            </div>
                            <div class="d-flex mt-2 w-25">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">লিঙ্গ:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= StaticMethod::gender($model->gender) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0 mt-2" style="width: 33px">
                            ০৮।
                        </div>
                        <div class="d-flex flex-wrap flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-lg-25 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">বিজ্ঞাপনে বর্ণিত তারিখ বয়স:</p>
                                    <?php $age_by_circular = explode('-', $model->age_according_to_circular); ?>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= $age_by_circular[0] ?? ''; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-25 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">বৎসর:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2"> <?= $age_by_circular[1] ?? ''; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-25 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">মাস:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2"><?= $age_by_circular[2] ?? ''; ?>
                                </div>
                            </div>
                            <div class="d-flex flex-grow-1 w-lg-25 mt-2">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">দিন (নাবিক ও এমওডিসি)</p>
                                </div>
                                <!-- <div class="flex-grow-1 border-bottom-dashed mx-2">
                            </div> -->
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0" style="width: 33px">
                            ০৯।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-50">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">ধর্ম:</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= StaticMethod::academicReligion($model->religion) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0" style="width: 33px">
                            ১০।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-50">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">বৈবাহিক অবস্থা: </p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= StaticMethod::maritalStatus($model->marital_status) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0" style="width: 33px">
                            ১১।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-50">
                                <div class="flex-shrink-0">
                                    <p class="bangla_font">জাতীয়তা (জন্মসূত্রে):</p>
                                </div>
                                <div class="flex-grow-1 border-bottom-dashed mx-2">
                                    <?= ucfirst($model->nationality); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex mt-4">
                        <div class="bangla_font flex-shrink-0" style="width: 33px">
                            ১২।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-50">
                                <div class="">
                                    <p class="bangla_font">শিক্ষাগত যোগ্যতা:</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="common__table mt-3">
                        <table class="table table-bordered text-white">
                            <thead>
                                <tr class="text-center bangla_font">
                                    <th>পরীক্ষা / শিক্ষাগত <br> যোগ্যতা</th>
                                    <th>শিক্ষা প্রতিষ্ঠানের নাম </th>
                                    <th>গ্রুপ </th>
                                    <th>শিক্ষা বোর্ড </th>
                                    <th>রেজিস্ট্রেশন নং</th>
                                    <th>রোল নং </th>
                                    <th>পাশের সন</th>
                                    <th>ঐচ্ছিক বিষয়সমূহ</th>
                                    <th>প্রাপ্ত নম্বর/জিপিএ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>৮ম শ্রেণী</td>
                                    <td><?= $model->jsc_institute_name ?> </td>
                                    <td>XX</td>
                                    <td>XX</td>
                                    <td><?= $model->jsc_reg_no ?></td>
                                    <td>XX</td>
                                    <td><?= $model->jsc_passing_year ?></td>
                                    <td>XX</td>
                                    <td><?= $model->jsc_gpa ?></td>
                                </tr>
                                <tr>
                                    <td>মধ্যমিক / সমমান </td>
                                    <td><?= ($model->ssc_institute) ? $model->ssc_institute : 'XX'; ?> </td>
                                    <td><?= ($model->ssc_group) ? $model->ssc_group : 'XX'; ?></td>
                                    <td><?= ($model->ssc_edu_board) ? StaticMethod::educationBoard($model->ssc_edu_board) : 'XX'; ?></td>
                                    <td><?= ($model->ssc_reg_no) ? $model->ssc_reg_no : 'XX'; ?></td>
                                    <td><?= ($model->ssc_roll_no) ? $model->ssc_roll_no : 'XX'; ?></td>
                                    <td><?= ($model->ssc_passing_year) ? $model->ssc_passing_year : 'XX'; ?></td>
                                    <td><?= ($model->ssc_additional_subject) ? $model->ssc_additional_subject : 'XX'; ?></td>
                                    <td><?= ($model->ssc_gpa) ? $model->ssc_gpa : 'XX'; ?></td>
                                </tr>
                                <tr>
                                    <td>এইচএসসি / সমমান</td>
                                    <td><?= ($model->hsc_dip_institute) ? $model->hsc_dip_institute : 'XX'; ?></td>
                                    <td><?= ($model->hsc_dip_group) ? $model->hsc_dip_group : 'XX'; ?></td>
                                    <td><?= ($model->hsc_dip_board) ? StaticMethod::educationBoard($model->hsc_dip_board) : 'XX'; ?></td>
                                    <td><?= ($model->hsc_dip_reg_no) ? $model->hsc_dip_reg_no : 'XX'; ?></td>
                                    <td><?= ($model->hsc_dip_roll_no) ? $model->hsc_dip_roll_no : 'XX'; ?></td>
                                    <td><?= ($model->hsc_dip_passing_year) ? $model->hsc_dip_passing_year : 'XX'; ?></td>
                                    <td><?= ($model->hsc_dip_additional_subject) ? $model->hsc_dip_additional_subject : 'XX'; ?></td>
                                    <td><?= ($model->hsc_dip_gpa) ? $model->hsc_dip_gpa : 'XX'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex mt-4">
                        <div class="bangla_font flex-shrink-0" style="width: 33px">
                            ১৩।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-50">
                                <div class="">
                                    <p class="bangla_font">খেলাধুলা / সংগীত / অন্য কোন বিষয়ে দক্ষতা (যদি থাকে-সনদপত্র সংযোজন করতে হবে):</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="common__table mt-3">
                        <table class="table text-white table-bordered">
                            <thead>
                                <tr class="text-center">
                                    <th style="width: 5%">ক্রমিক</th>
                                    <th>প্রতিষ্ঠানের নাম</th>
                                    <th>অংশগ্রহনকৃত বিষয়ের নাম</th>
                                    <th>সন</th>
                                    <th>প্রাপ্ত স্থান/প্রশংসাপত্র/পদকের নাম</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>১।</td>
                                    <td><?= $model->experience_one_institute; ?></td>
                                    <td><?= $model->experience_one_subject; ?></td>
                                    <td><?= $model->experience_one_year; ?></td>
                                    <td><?= $model->experience_one_cert_name; ?></td>
                                </tr>
                                <tr>
                                    <td>২।</td>
                                    <td><?= $model->experience_two_institute; ?></td>
                                    <td><?= $model->experience_two_subject; ?></td>
                                    <td><?= $model->experience_two_year; ?></td>
                                    <td><?= $model->experience_two_cert_name; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="bangla_font flex-shrink-0" style="width: 33px">
                            ১৪।
                        </div>
                        <div class="d-flex flex-wrap flex-grow-1">
                            <div class="d-flex flex-grow-1 w-100">
                                <div class="">
                                    <p class="bangla_font">
                                        <!-- পিতা মুক্তিযোদ্ধা বা নৌবাহিনীর কর্মরত / অবঃ/ মৃত সামরিক / বেসামরিক কর্মকর্তা বা কর্মচারী হলে তার বিবরণ (সংশ্লিষ্ট মূল কাগজপত্র সঙ্গে আনতে হবে) -->
                                        মুক্তিযোদ্ধার সন্তান/ ক্ষুদ্র নৃ-গোষ্ঠি <!--গোষ্ঠী --> হলে তার বিবরণ (সংশ্লিষ্ট মূল কাগজপত্র সঙ্গে আনতে হবে)
                                    </p>
                                </div>
                            </div>

                            <div class=" w-100 border-bottom-dashed mx-2">
                                <!-- ক ) মুক্তিযোদ্ধার সন্তান/ ক্ষুদ্র নৃ-গোষ্ঠি : -->
                                <?php


                                echo StaticMethod::yesNoForFreedom($model->is_freedom_fighter);

                                // if ($model->is_freedom_fighter == Constants::YES)
                                //     echo  'Yes';
                                // else
                                //     echo 'No';

                                // if ($model->is_freedom_fighter == Constants::YES && $model->freedom_fighter_relation)
                                //     echo StaticMethod::yesNo($model->is_freedom_fighter) . ', ' . StaticMethod::relationWithFreedomFighter($model->freedom_fighter_relation);
                                // else
                                //     echo 'XX';
                                ?>
                            </div>
                            <?php /*
                        <div class=" w-50 border-bottom-dashed mx-2">
                            <!-- খ ) -->
                            ক )
                            <?php
                            if ($model->is_child_of_naval_officer == Constants::YES) :
                                echo $model->naval_father_name . '(' . StaticMethod::navyUniformCivil($model->naval_uniform_civil) . ') , Offical No:' . $model->naval_office_no . ', Rank : ' . $model->naval_rank;
                            else :
                                echo 'XX';
                            endif;
                            ?>
                        </div> 
                        */ ?>
                        </div>
                    </div>
                    <?php /* 
                <div class="d-flex mt-3">
                    <div class="bangla_font flex-shrink-0" style="width: 33px">
                        ১৫।
                    </div>
                    <div class="d-flex flex-wrap flex-grow-1">
                        <div class="d-flex flex-grow-1 w-100">
                            <div class="">
                                <p class="bangla_font">প্রার্থী আনসার / ভিডিপি'র সদস্য / ক্ষুদ্রজাতি গোষ্ঠী হলে তার বিস্তারিত বিবরণ (সংশ্লিষ্ট মূল কাগজপত্র ভর্তি কেন্দ্রে আনতে হবে):</p>
                            </div>
                        </div>
                        <div class=" w-50 border-bottom-dashed mx-2">
                            ক ) প্রার্থী আনসার / ভিডিপি'র:
                            <?php
                            // $model->attributeLabelBangla()['anser_vdp_rank'].
                            if ($model->is_anser_vdp == Constants::YES)
                                echo 'পদবী: ' . strtoupper($model->anser_vdp_rank) . ', অফিস নম্বর : ' . strtoupper($model->anser_vdp_office_no);
                            else
                                echo 'XX';
                            ?>
                        </div>
                        <div class="w-50 border-bottom-dashed mx-2">
                            খ ) <?php
                                if ($model->is_khudro_jati_gosti == Constants::YES)
                                    echo 'ক্ষুদ্রজাতি গোষ্ঠী : Yes';
                                else
                                    echo 'ক্ষুদ্রজাতি গোষ্ঠী : XX';
                                ?>
                        </div>
                    </div>
                </div>  */ ?>
                </div>
            <?php } else {  ?>
                <div class="col-lg-12">
                    <div class="row mt-5 text-center">
                        <div class="alert alert-danger" role="alert">
                            Application ID <strong><?= $model->app_unique_id; ?></strong> is cancelled Application
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>