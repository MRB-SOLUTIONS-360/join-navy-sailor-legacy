<?php

use common\models\CanDesignation;
use common\models\SailorCenters;
use common\static\Constants;
use common\static\StaticMethod;

?>
<style>
 .h2_padding_margin_0 {
     padding: 0px;
     margin: 0px;
 }

 .h2_head_block_common {
     line-height: 15px;
     font-size: 10pt;
     font-weight: bold;
 }

 .font_kp {
     font-family: kalpurush;
 }

 .border_bottom_dotted {
     border-bottom: 1px dotted;
 }

 table,
 th,
 td {
     border: 1px solid black;
     border-collapse: collapse;
 }
</style>

<div class="form-element">
 <div class="form-serial font_kp" style="width: 100%; line-height: 0px; font-size: 10pt; text-align: center; color: red; text-decoration: underline;">
     প্রয়োজনীয় কাগজপত্রসহ পূরণকৃত আবেদনপত্রটি বিজ্ঞাপনে উল্লেখিত তারিখে ভর্তি কেন্দ্রে সঙ্গে আনুন।
     <br />প্রার্থীদের ভর্তি পরীক্ষার ফলাফলের ভিত্তিতে শাখা নির্ধারিত হবে বিধায় আবেদনকৃত শাখাই চূড়ান্ত নয়।
 </div>
</div>
<div style="width: 100%; margin-top: 5px; height: 180px;">
 <div style="width: 22%; float: left;">
     <p class="font_kp" style="font-size: 12pt;   margin: 0px; padding: 0px;">রোল নং: <strong><?php echo $model->serial_no; ?></strong> </p>
     <p class="font_kp" style="font-size: 12pt;  margin: 0px; padding: 0px;">ফরম (নাবিক)-১</p>
     <?php
        if ($model->qr_photo && file_exists(Yii::getAlias('@rootDirFilUpload') . $model->qr_photo)) {
            echo '<img style="height:140px; width:140px;" src=' . Yii::getAlias('@rootDirFilUpload') . $model->qr_photo . ' alt="QR not found">';
        } else echo '&nbsp;';
        ?>
 </div>
 <div style="width: 56%; float: left; text-align: center;">

     <img src="<?= Yii::getAlias('@rootDirFilUpload'); ?>/media/main_logo.png" alt="QR not found" style="width:80px; text-align: center; margin: 0 auto;">


     <h2 class="h2_padding_margin_0 font_kp" style="font-size: 10pt; font-weight: bold; ">বাংলাদেশ নৌবাহিনী</h2>
     <h2 class="h2_padding_margin_0 font_kp" style="line-height: 17px; font-size: 10pt; font-weight: bold; "><!--নাবিক,মহিলা --> নাবিক ও এমওডিসি (নৌ) পদে ভর্তির আবেদনপত্র </h2>
     <h2 class="h2_padding_margin_0 font_kp" style="line-height: 15x; font-size: 10pt; font-weight: bold;">আবেদন নাম্বার: <?= $model->app_unique_id; ?> </h2>
     <h2 class="h2_padding_margin_0 font_kp h2_head_block_common"> কেন্দ্র: <?= SailorCenters::getAllCenterSession($model->center_id) ?></h2>
     <h2 class="h2_padding_margin_0 font_kp h2_head_block_common">আবেদনের শাখা: <?= CanDesignation::getAllDesignationSession($model->candidate_designation) ?> </h2>
     <h2 class="h2_padding_margin_0 font_kp h2_head_block_common">পরীক্ষা সময়: <span style="font-family: auto;"><?= date('d F Y', strtotime($model->exam_date)); ?></span> </h2>
 </div>

 <div style="width: 20%; float: left;">

     <?php
        if ($model->photo && file_exists(Yii::getAlias('@rootDirFilUpload') . $model->photo)) {
            echo '<img src=' . Yii::getAlias('@rootDirFilUpload') . $model->photo . ' alt="Image not found">';
        } else echo '&nbsp;';
        ?>
     <?php
        $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
        echo '<img style="padding-top:5px" src="data:image/png;base64,' . base64_encode($generator->getBarcode($model->serial_no, $generator::TYPE_CODE_128)) . '">';
        ?>
 </div>
</div>

<!-- Candidate Information -->
<div style="width: 100%; margin-top: 5px;">
 <div class="font_kp" style="width: 3%; float: left;">
     ১।
 </div>
 <div class="font_kp" style="width: 13%; float: left;">
     প্রার্থীর পূর্ণ নাম:
 </div>
 <div class="border_bottom_dotted" style="width: 84%; float: left; ">
     <?= strtoupper($model->name); ?>
 </div>
</div>
<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     ২।
 </div>
 <div class="font_kp" style="width: 10%; float: left;">
     পিতার নাম:
 </div>
 <div class="border_bottom_dotted" style="width: 87%; float: left; ">
     <?= strtoupper($model->father_name); ?>

 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 5%; float: left;">
     পেশা:
 </div>
 <div class="border_bottom_dotted" style="width: 50%; float: left; ">
     <?= strtoupper($model->father_occupation); ?>

 </div>
 <div class="font_kp" style="width: 14%; float: left;">
     পরিচয় পত্র নম্বর:
 </div>
 <div class="border_bottom_dotted" style="width: 27.8%; float: left; ">
     <?= ($model->father_nid) ? strtoupper($model->father_nid) : '&nbsp;'; ?>
 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     ৩।
 </div>
 <div class="font_kp" style="width: 10%; float: left;">
     মাতার নাম:
 </div>
 <div class="border_bottom_dotted" style="width: 55%; float: left; ">
     <?= strtoupper($model->mother_name); ?>

 </div>
 <div class="font_kp" style="width: 5%; float: left;">
     পেশা:
 </div>
 <div class="border_bottom_dotted" style="width: 26%; float: left; ">
     <?= strtoupper($model->mother_occupation); ?>
 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     ৪।
 </div>
 <div class="font_kp" style="width: 12%; float: left;">
     বর্তমান ঠিকানা:
 </div>
 <div class="font_kp" style="width: 8%; float: left;">
     গ্রাম/বাসা:
 </div>
 <div class="border_bottom_dotted" style="width: 40%; float: left; ">
     <?= strtoupper($model->current_village); ?>
 </div>
 <div class="font_kp" style="width: 8%; float: left;">
     ওয়ার্ড নং:
 </div>
 <div class="border_bottom_dotted" style="width: 29%; float: left; ">
     <?= ($model->current_word_no) ? strtoupper($model->current_word_no) : '&nbsp;'; ?>

 </div>
</div>
<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 15%; float: left;">
     ইউনিয়ন/রোড নং:
 </div>
 <div class="border_bottom_dotted" style="width: 40%; float: left; ">
     <?= strtoupper($model->current_union); ?>
 </div>
 <div class="font_kp" style="width: 12%; float: left;">
     পোস্ট অফিস:
 </div>
 <div class="border_bottom_dotted" style="width: 30%; float: left; ">
     <?= strtoupper($model->current_post_office);  ?>
 </div>
</div>
<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 12%; float: left;">
     থানা/উপজেলা:
 </div>
 <div class="border_bottom_dotted" style="width: 40%; float: left; ">
     <?= strtoupper($model->current_thana); ?>
 </div>
 <div class="font_kp" style="width: 6%; float: left;">
     জেলা:
 </div>
 <div class="border_bottom_dotted" style="width: 39%; float: left; ">
     <?= strtoupper($model->current_district); ?>
 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 12%; float: left;">
     পোস্ট কোড:
 </div>
 <div class="border_bottom_dotted" style="width: 40%; float: left; ">
     <?= strtoupper($model->current_post_code); ?>
 </div>
 <div class="font_kp" style="width: 15%; float: left;">
     ফোন / মোবাইল:
 </div>
 <div class="border_bottom_dotted" style="width: 29.5%; float: left; ">
     <?= $model->current_phone; ?>
 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     ৫।
 </div>
 <div class="font_kp" style="width: 12%; float: left;">
     স্থায়ী ঠিকানা:
 </div>
 <div class="font_kp" style="width: 8%; float: left;">
     গ্রাম/বাসা:
 </div>
 <div class="border_bottom_dotted" style="width: 40%; float: left; ">
     <?= strtoupper($model->permanent_village); ?>
 </div>
 <div class="font_kp" style="width: 8%; float: left;">
     ওয়ার্ড নং:
 </div>
 <div class="border_bottom_dotted" style="width: 29%; float: left; ">
     <?= ($model->permanent_word_no) ? strtoupper($model->permanent_word_no) : '&nbsp;'; ?>
 </div>
</div>
<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 15%; float: left;">
     ইউনিয়ন/রোড নং:
 </div>
 <div class="border_bottom_dotted" style="width: 40%; float: left; ">
     <?= strtoupper($model->permanent_union); ?>
 </div>
 <div class="font_kp" style="width: 12%; float: left;">
     পোস্ট অফিস:
 </div>
 <div class="border_bottom_dotted" style="width: 30%; float: left; ">
     <?= strtoupper($model->permanent_post_office);  ?>
 </div>
</div>
<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 12%; float: left;">
     থানা/উপজেলা:
 </div>
 <div class="border_bottom_dotted" style="width: 40%; float: left; ">
     <?= strtoupper($model->permanent_thana); ?>
 </div>
 <div class="font_kp" style="width: 6%; float: left;">
     জেলা:
 </div>
 <div class="border_bottom_dotted" style="width: 39%; float: left; ">
     <?= strtoupper($model->permanent_district); ?>
 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 12%; float: left;">
     পোস্ট কোড:
 </div>
 <div class="border_bottom_dotted" style="width: 40%; float: left; ">
     <?= strtoupper($model->permanent_post_code);   ?>
 </div>
 <div class="font_kp" style="width: 15%; float: left;">
     ফোন / মোবাইল:
 </div>
 <div class="border_bottom_dotted" style="width: 29.5%; float: left; ">
     <?= $model->permanent_phone; ?>
 </div>
</div>


<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     ৬।
 </div>
 <div class="font_kp" style="width: 35%; float: left;">
     অভিভাবকের নাম (পিতা জীবিত না থাকলে):
 </div>
 <div class="border_bottom_dotted" style="width: 61%; float: left;">
     <?= ($model->guardian_name) ? strtoupper($model->guardian_name) : '&nbsp; ';  ?>

 </div>
</div>
<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 6%; float: left;">
     সম্পর্ক:
 </div>
 <div class="border_bottom_dotted" style="width: 43%; float: left;">
     <?= ($model->guardian_relation) ? strtoupper($model->guardian_relation) : '&nbsp;';  ?>

 </div>
 <div class="font_kp" style="width: 6%; float: left;">
     পেশা:
 </div>
 <div class="border_bottom_dotted" style="width: 42%; float: left;">
     <?= ($model->guardian_occupation) ? strtoupper($model->guardian_occupation) : '&nbsp;'; ?>

 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 6%; float: left;">
     ঠিকানা:
 </div>
 <div class="border_bottom_dotted" style="width: 91%; float: left;">
     <?= ($model->guardian_address) ? strtoupper($model->guardian_address) : '&nbsp;';  ?>
 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     ৭।
 </div>
 <div class="font_kp" style="width: 97%; float: left;">
     জন্ম তারিখ (মাধ্যমিক সনদপত্র/ নবম শ্রেণীর রেজিস্ট্রেশন কার্ড / টোপাস প্রার্থীদের ক্ষেত্রে জন্ম নিবন্ধন বা ভোটার আইডি কার্ড
 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 8%; float: left;">
     অনুযায়ী):
 </div>
 <div class="border_bottom_dotted" style="width: 22%; float: left;">
     <?= date('d F Y', strtotime($model->dob)); ?>
 </div>
 <div class="font_kp" style="width: 24%; float: left;">
     বিজ্ঞাপনে বর্ণিত তারিখে বয়স:
     <?php $age_by_circular = explode('-', $model->age_according_to_circular); ?>
 </div>
 <div class="border_bottom_dotted" style="width: 5%; float: left;">
     <?= $age_by_circular[0] ?? ''; ?>
 </div>
 <div class="font_kp" style="width: 6%; float: left;">
     বৎসর
 </div>
 <div class="border_bottom_dotted" style="width: 5%; float: left;">
     <?= $age_by_circular[1] ?? ''; ?>
 </div>
 <div class="font_kp" style="width: 5%; float: left;">
     মাস
 </div>
 <div class="border_bottom_dotted" style="width: 5%; float: left;">
     <?= $age_by_circular[2] ?? ''; ?>
 </div>
 <div class="font_kp" style="width: 15%; float: left;">
     দিন
 </div>
</div>


<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 3%; float: left;">
     ৮।
 </div>
 <div class="font_kp" style="width: 5%; float: left;">
     লিঙ্গ:
 </div>
 <div class="border_bottom_dotted" style="width: 10%; float: left;">
     <?= StaticMethod::gender($model->gender) ?>
 </div>
 <div class="font_kp" style="width: 7%; float: left;">
     ৯। ধর্ম:
 </div>
 <div class="border_bottom_dotted" style="width: 13%; float: left;">
     <?= StaticMethod::academicReligion($model->religion) ?>
 </div>
 <div class="font_kp" style="width: 17%; float: left;">
     ১০। বৈবাহিক অবস্থা:
 </div>
 <div class="border_bottom_dotted" style="width: 14%; float: left;">
     <?= StaticMethod::maritalStatus($model->marital_status) ?>
 </div>
 <div class="font_kp" style="width: 11%; float: left;">
     ১১। জাতীয়তা:
 </div>
 <div class="border_bottom_dotted" style="width: 19%; float: left;">
     <?= ucfirst($model->nationality); ?>
 </div>
</div>

<?php /*
<div style="width: 100%; margin-top: 2px;">
<div class="font_kp" style="width: 4%; float: left;">
    ১০।
</div>
<div class="font_kp" style="width: 13%; float: left;">
    বৈবাহিক অবস্থা:
</div>
<div class="border_bottom_dotted" style="width: 30%; float: left;">
    <?= StaticMethod::maritalStatus($model->marital_status) ?>
</div>
<div class="font_kp" style="width: 20%; float: left;">
    ১১। জাতীয়তা (জন্মসূত্রে):
</div>
<div class="border_bottom_dotted" style="width: 33%; float: left;">
    <?= ucfirst($model->nationality); ?>
</div>
</div> */ ?>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 4%; float: left;">
     ১২।
 </div>
 <div class="font_kp" style="width: 20%; float: left;">
     শিক্ষাগত যোগ্যতা:
 </div>
</div>
<div style="width: 100%; margin-top: 2px;">
 <div style="width: 100%; float: left;">
     <table style="width: 100%;">
         <thead>
             <tr>
                 <th class="font_kp" style="width: 90px;">পরীক্ষা/শিক্ষাগত<br /> যোগ্যতা</th>
                 <th class="font_kp">শিক্ষা প্রতিষ্ঠানের নাম</th>
                 <th class="font_kp">গ্রুপ</th>
                 <th class="font_kp">শিক্ষা বোর্ড</th>
                 <th class="font_kp">রেজিস্ট্রেশন নং</th>
                 <th class="font_kp">রোল নং</th>
                 <th class="font_kp">পাশের সন</th>
                 <th class="font_kp">ঐচ্ছিক বিষয়সমূহ</th>
                 <th class="font_kp" style="width: 60px;">প্রাপ্ত নম্বর/জিপিএ</th>
             </tr>
         </thead>
         <tbody>
             <tr>
                 <td class="font_kp">৮ম শ্রেণী</td>
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
                 <td class="font_kp">মাধ্যমিক / সমমান</td>
                 <td><?= ($model->ssc_institute) ? $model->ssc_institute : 'XX'; ?></td>
                 <td><?= ($model->ssc_group) ? $model->ssc_group : 'XX'; ?></td>
                 <td><?= ($model->ssc_edu_board) ? StaticMethod::educationBoard($model->ssc_edu_board) : 'XX'; ?></td>
                 <td><?= ($model->ssc_reg_no) ? $model->ssc_reg_no : 'XX'; ?></td>
                 <td><?= ($model->ssc_roll_no) ? $model->ssc_roll_no : 'XX'; ?></td>
                 <td><?= ($model->ssc_passing_year) ? $model->ssc_passing_year : 'XX'; ?></td>
                 <td><?= ($model->ssc_additional_subject) ? $model->ssc_additional_subject : 'XX'; ?></td>
                 <td><?= ($model->ssc_gpa) ? $model->ssc_gpa : 'XX'; ?></td>
             </tr>
             <tr>
                 <td class="font_kp">এইচএসসি / সমমান</td>
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
</div>

<div style="width: 100%; margin-top: 2px;">
 <div class="font_kp" style="width: 4%; float: left;">
     ১৩।
 </div>
 <div class="font_kp" style="width: 80%; float: left;">
     খেলাধুলা / সংগীত / অন্য কোন বিষয়ে দক্ষতা (যদি থাকে-সনদপত্র সংযোজন করতে হবে):
 </div>
</div>

<div style="width: 100%; margin-top: 2px;">
 <div style="width: 100%; float: left;">
     <table style="width: 100%;">
         <thead>
             <tr>
                 <th class="font_kp" style="width: 50px;">ক্রমিক</th>
                 <th class="font_kp">প্রতিষ্ঠানের নাম</th>
                 <th class="font_kp">অংশগ্রহনকৃত বিষয়ের নাম</th>
                 <th class="font_kp">সন</th>
                 <th class="font_kp">প্রাপ্ত স্থান/প্রশংসাপত্র/পদকের নাম</th>
             </tr>
         </thead>
         <tbody>
             <tr>
                 <td class="font_kp">১।</td>
                 <td style="padding: 3px;"><?= $model->experience_one_institute; ?></td>
                 <td><?= $model->experience_one_subject; ?></td>
                 <td><?= $model->experience_one_year; ?></td>
                 <td><?= $model->experience_one_cert_name; ?></td>
             </tr>
             <tr>
                 <td class="font_kp">২।</td>
                 <td><?= $model->experience_two_institute; ?></td>
                 <td><?= $model->experience_two_subject; ?></td>
                 <td><?= $model->experience_two_year; ?></td>
                 <td><?= $model->experience_two_cert_name; ?></td>
             </tr>
         </tbody>
     </table>
 </div>
</div>

<footer class="font_kp" style="margin: 0 auto; text-align: center; font-size: 14pt;">
 ১
</footer>
<pagebreak />
<br />
<br />
<!-- Naval and freedom  -->
<div style="width: 100%; margin-top: 5px;">
 <div class="font_kp" style="width: 4%; float: left;">
     ১৪।
 </div>
 <div class="font_kp" style="width: 94%; float: left;">
     পিতা মুক্তিযোদ্ধা বা নৌবাহিনীর কর্মরত / অবঃ / শহীদ /সামরিক /অসামরিক কর্মকর্তা বা কর্মচারী হলে তার বিবরণ (সংশ্লিষ্ট মূল
 </div>
</div>
<div style="width: 100%;">
 <div class="font_kp" style="width: 4%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 20%; float: left;">
     কাগজপত্র সঙ্গে আনতে হবে)
 </div>

 <div class="font_kp" style="width: 22%; float: left;">
     &nbsp; (ক ) Freedom Fighter :
 </div>
 <div class="border_bottom_dotted" style="width: 53%; float: left;">
     <?php
        if ($model->is_freedom_fighter == Constants::YES) echo  strtoupper(StaticMethod::relationWithFreedomFighter($model->freedom_fighter_relation));
        else echo 'XX';
        ?>
 </div>
</div>
<div style="width: 100%;">
 <div class="font_kp" style="width: 4%; float: left;">
     &nbsp;
 </div>
 <div class="font_kp" style="width: 20%; float: left;">
     &nbsp;
 </div>

 <div class="font_kp" style="width: 18%; float: left;">
     &nbsp; (খ) Naval Child :
 </div>
 <div class="border_bottom_dotted" style="width: 57%; float: left;">
     <?php
        if ($model->is_child_of_naval_officer == Constants::YES)
            echo strtoupper($model->naval_father_name) . '(' . StaticMethod::navyUniformCivil($model->naval_uniform_civil) . ')' . ', Offical No : ' . strtoupper($model->naval_office_no) . ', Rank : ' . strtoupper($model->naval_rank); //. ', ' . strtoupper($model->navy_ship_etbd_retired);
        else echo 'XX';
        ?>
 </div>
</div>

<!--Anser -->
<div style="width: 100%; margin-top: 5px;">
 <div class="font_kp" style="width: 4%; float: left;">
     ১৫।
 </div>
 <div class="font_kp" style="width: 94%; float: left;">
     প্রার্থী আনসার / ভিডিপি'র সদস্য / ক্ষুদ্রজাতি গোষ্ঠী হলে তার বিস্তারিত বিবরণ (সংশ্লিষ্ট মূল কাগজপত্র ভর্তি কেন্দ্রে আনতে হবে)
 </div>
</div>
<div style="width: 100%;">
 <div class="font_kp" style="width: 4%; float: left;">
     &nbsp;
 </div>
 <div class="border_bottom_dotted" style="width: 94%; float: left;">
     <?php
        if ($model->is_anser_vdp == Constants::YES) {
            echo  'Rank : ' . strtoupper($model->anser_vdp_rank) . ', Official No : ' . strtoupper($model->anser_vdp_office_no);
        } else echo 'XX';

        if ($model->is_khudro_jati_gosti == Constants::YES) {
            echo ',&nbsp;&nbsp;<span class="font_kp" >ক্ষুদ্রজাতি গোষ্ঠী</span> : Yes';
        } else
            echo ',&nbsp;&nbsp;<span class="font_kp" >ক্ষুদ্রজাতি গোষ্ঠী</span> : XX';
        ?>
 </div>
</div>