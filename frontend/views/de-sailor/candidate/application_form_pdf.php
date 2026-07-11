 <?php

    use common\models\CanDesignation;
    use common\models\Districts;
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



 <?php
    if ($model->application_status == Constants::NO) { ?>
     <div class="form-element">
         <div class="form-serial font_kp" style="width: 100%; line-height: 0px; font-size: 20pt; text-align: center; color: red; margin-top: 50;">
             Application ID <strong><?= $model->app_unique_id; ?></strong> is cancelled Application
         </div>
     </div>
 <?php
    } else {
    ?>
     <div class="form-element">
         <div class="form-serial font_kp" style="width: 100%; line-height: 0px; font-size: 13pt; margin-top: 5px; text-align: center; color: red; text-decoration: underline;">
             <!-- প্রয়োজনীয় কাগজপত্রসহ পূরণকৃত আবেদনপত্রটি বিজ্ঞাপনে উল্লেখিত তারিখে ভর্তি কেন্দ্রে সঙ্গে আনুন।
             <br /> -->
             প্রার্থীদের ভর্তি পরীক্ষার ফলাফলের ভিত্তিতে শাখা নির্ধারিত হবে বিধায় আবেদনকৃত শাখাই চূড়ান্ত নয়।
         </div>
     </div>

     <div style="width: 100%; margin-top: 5px; height: 160px;">
         <div style="width: 22%; float: left;">

             <p class="font_kp" style="font-size: 12pt;  margin: 0px; padding: 0px;">ফরম (নাবিক)-২</p>
             <?php
                // if ($model->qr_photo && file_exists(Yii::getAlias('@rootDirFilUpload') . $model->qr_photo)) {
                //     echo '<img style="height:140px; width:140px;" src=' . Yii::getAlias('@rootDirFilUpload') . $model->qr_photo . ' alt="QR not found">';
                // } else echo '&nbsp;<span style="color:red;">QR Code Missing</span>';

                // if ($model->qr_photo && Yii::$app->r2Storage->fileExists($model->qr_photo)) {
                //     echo '<img style="height:130px; width:130px;" src=' . Yii::$app->r2Storage->fileUrl . $model->qr_photo . ' alt="QR not found">';
                // } else  echo '&nbsp;<span style="color:red;">QR Code Missing</span>';
                ?>
         </div>
         <div style="width: 56%; float: left; text-align: center;">

             <img src="<?= Yii::getAlias('@rootDirFilUpload') ?>/media/main_logo.png" alt="QR not found" style="width:80px; text-align: center; margin: 0 auto;">




             <h2 class="h2_padding_margin_0 font_kp" style="font-size: 12pt; font-weight: bold; ">বাংলাদেশ নৌবাহিনী</h2>
             <h2 class="h2_padding_margin_0 font_kp" style="line-height: 17px; font-size: 12pt; font-weight: bold; ">  ডাইরেক্ট এন্ট্রি সেইলর ফর ডকইয়ার্ড  পদে ভর্তির আবেদনপত্র</h2>
             <!-- <h2 class="h2_padding_margin_0 font_kp" style="line-height: 15x; font-size: 12pt; font-weight: bold;">আবেদন নাম্বার: <?php // $model->app_unique_id; ?> </h2> -->
             <h2 class="h2_padding_margin_0 font_kp h2_head_block_common" style="line-height: 17x; font-size: 12pt; font-weight: bold;"> কেন্দ্র: <?= SailorCenters::getAllCenterSession($model->center_id) ?></h2>
             <h2 class="h2_padding_margin_0 font_kp h2_head_block_common" style="line-height: 17x; font-size: 12pt; font-weight: bold;">আবেদনের শাখা: <?= CanDesignation::getAllDesignationSession($model->candidate_designation) ?> </h2>
             <?php /*
             <h2 class="h2_padding_margin_0 font_kp h2_head_block_common">পরীক্ষা সময়: <span style="font-family: auto;"><?= date('d F Y', strtotime($model->exam_date)); ?></span> </h2>
             */ ?>
         </div>

         <div style="width: 20%; float: left;">
            &nbsp;

             <?php
                // if ($model->photo && file_exists(Yii::getAlias('@rootDirFilUpload') . $model->photo)) {
                //     echo '<img src=' . Yii::getAlias('@rootDirFilUpload') . $model->photo . ' alt="Image not found">';
                // } else echo '&nbsp;<span style="color:red;">Photo Missing</span>';

                if ($model->photo && Yii::$app->r2Storage->fileExists($model->photo)) {
                    echo '<img src=' . Yii::$app->r2Storage->fileUrl . $model->photo . ' alt="Image not found">';
                } else echo '&nbsp;<span style="color:red;">Photo Missing</span>';

                ?>

             <!-- <h2 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin-top: 5px;">পরীক্ষা সময়: <span style="font-family: auto;"><? // date('d F Y', strtotime($model->exam_date)); 
                                                                                                                                                        ?> 08:00 AM</span> </h2> -->
         </div>
     </div>


     <div style="width: 100%; margin-top: 5px;">
         <div class="font_kp" style="width: 75%; float: left;">

             <div style="width: 100%; margin-top: 5px;">
                 <div class="font_kp" style="width: 3%; float: left;">
                     ১।
                 </div>
                 <div class="font_kp" style="width: 26%; float: left; margin-left: 2px;">
                     প্রার্থীর পূর্ণ নাম (ইংরেজী) :
                 </div>
                 <div class="border_bottom_dotted" style="width: 70%; float: left; font-size:14px ">
                     <?= strtoupper($model->name); ?>
                 </div>
             </div>
             <div style="width: 100%; margin-top: 5px;">
                 <div class="font_kp" style="width: 3%; float: left;">
                     &nbsp;
                 </div>
                 <div class="font_kp" style="width: 26%; float: left;  margin-left: 2px;">
                     প্রার্থীর পূর্ণ নাম (বাংলায়) :
                 </div>
                 <div class="border_bottom_dotted font_kp" style="width: 70%; float: left; font-size:14px ">
                     <?= $model->name_bangla; ?> &nbsp;
                 </div>
             </div>

             <div style="width: 100%; margin-top: 2px;">
                 <div class="font_kp" style="width: 3%; float: left;">
                     ২।
                 </div>
                 <div class="font_kp" style="width: 26%; float: left; padding-left: 5px; ">
                     পিতার নাম (ইংরেজী) :
                 </div>
                 <div class="border_bottom_dotted" style="width: 70%; float: left; font-size:14px ">
                     <?= strtoupper($model->father_name); ?>
                 </div>
             </div>
             <div style="width: 100%; margin-top: 2px;">
                 <div class="font_kp" style="width: 3%; float: left;">
                     &nbsp;
                 </div>
                 <div class="font_kp" style="width: 26%; float: left; padding-left: 5px;">
                     পিতার নাম (বাংলায়) :
                 </div>
                 <div class="border_bottom_dotted font_kp" style="width: 70%; float: left; font-size:14px ">
                     <?= $model->father_name_bangla ?> &nbsp;
                 </div>
             </div>



         </div>
         <div class="font_kp" style="width: 24%; float: left;">
            <?php
                $generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
                echo '<img style="padding-top:5px"  src="data:image/png;base64,' . base64_encode($generator->getBarcode($model->serial_no, $generator::TYPE_CODE_128)) . '">';
                ?>               
             <div class="font_kp" style="width: 100%; float: left; font-size: 12.5px; font-weight: bold; margin-top: 5px; ">
                 &nbsp; &nbsp; রোল নং: <strong><?php echo $model->serial_no; ?></strong>
             </div>
             <div class="font_kp" style="width: 100%; float: left; font-size: 12.5px; font-weight: bold; margin-top: 2px; ">
                 &nbsp; &nbsp; পরীক্ষার তারিখ: <?= date('d M Y', strtotime($model->exam_date)); ?>
             </div>

             <div class="font_kp" style="width: 100%; float: left; font-size: 12.5px; font-weight: bold; margin-top: 2px; ">
                 &nbsp; &nbsp; পরীক্ষার সময়: 0800 AM
             </div>

         </div>
     </div>

     <!-- Candidate Information -->
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
         <div class="font_kp" style="width: 4%; float: left;">
             <!-- পরিচয় পত্র নম্বর: --> NID:
         </div>
         <div class="border_bottom_dotted" style="width: 37.8%; float: left; ">
             &nbsp;<?= ($model->father_nid) ? strtoupper($model->father_nid) : '&nbsp;'; ?>
         </div>
     </div>

     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             ৩।
         </div>
         <div class="font_kp" style="width: 18%; float: left;">
             মাতার নাম (ইংরেজী) :
         </div>
         <div class="border_bottom_dotted" style="width: 78%; float: left; font-size:14px ">
             <?= strtoupper($model->mother_name); ?>
         </div>
     </div>
     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             &nbsp;
         </div>
         <div class="font_kp" style="width: 17%; float: left;">
             মাতার নাম (বাংলায়) :
         </div>
         <div class="border_bottom_dotted font_kp" style="width: 48%; float: left; font-size:14px ">
             <?= $model->mother_name_bangla ?> &nbsp;
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
         <div class="border_bottom_dotted" style="width: 35%; float: left; ">
             <?= strtoupper($model->current_village); ?>
         </div>
         <div class="font_kp" style="width: 14%; float: left;">
           
               ইউনিয়ন/রোড নং:
         </div>
         <div class="border_bottom_dotted" style="width: 28%; float: left; ">
           
              <?= strtoupper($model->current_union); ?>
         </div>
     </div>
     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             &nbsp;
         </div>
         <div class="font_kp" style="width: 8%; float: left;">
              ওয়ার্ড নং:
         </div>
         <div class="border_bottom_dotted" style="width: 10%; float: left; ">
               <?= ($model->current_word_no) ? strtoupper($model->current_word_no) : '&nbsp;'; ?>
         </div>

         <div class="font_kp" style="width: 12%; float: left;">
             থানা/উপজেলা:
         </div>
         <div class="border_bottom_dotted" style="width: 30%; float: left; ">
             <?= strtoupper($model->current_thana); ?>
         </div>
         <div class="font_kp" style="width: 6%; float: left;">
             জেলা:
         </div>
         <div class="border_bottom_dotted" style="width: 30.5%; float: left; ">
             <?= strtoupper(Districts::findOneBySlug($model->current_district)); ?>
         </div>


         
         
     </div>
     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             &nbsp;
         </div>
         <div class="font_kp" style="width: 12%; float: left;">
             পোস্ট অফিস:
         </div>
         <div class="border_bottom_dotted" style="width: 30%; float: left; ">
             <?= strtoupper($model->current_post_office);  ?>
         </div>

          <div class="font_kp" style="width: 10%; float: left;">
             পোস্ট কোড:
         </div>
         <div class="border_bottom_dotted" style="width: 44.5%; float: left; ">
             <?= strtoupper($model->current_post_code); ?>
         </div>
         <!-- <div class="font_kp" style="width: 14%; float: left;">
             ফোন / মোবাইল:
         </div>
          <div class="border_bottom_dotted" style="width: 20.5%; float: left; ">
             <?= $model->current_phone; ?>
         </div> -->
         
     </div>    

    

     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             ৫।
         </div>
         <div class="font_kp" style="width: 11%; float: left;">
             স্থায়ী ঠিকানা:
         </div>
         <div class="font_kp" style="width: 8%; float: left;">
             গ্রাম/বাসা:
         </div>
         <div class="border_bottom_dotted" style="width: 35%; float: left; ">
             <?= strtoupper($model->permanent_village); ?>
         </div>

         <div class="font_kp" style="width: 14%; float: left;">
             ইউনিয়ন/রোড নং:
         </div>
         <div class="border_bottom_dotted" style="width: 28.5%; float: left; ">
             <?= strtoupper($model->permanent_union); ?>
         </div>


         
     </div>
     
     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             &nbsp;
         </div>
         <div class="font_kp" style="width: 8%; float: left;">
             ওয়ার্ড নং:
         </div>
         <div class="border_bottom_dotted" style="width: 10%; float: left; ">
             <?= ($model->permanent_word_no) ? strtoupper($model->permanent_word_no) : '&nbsp;'; ?>
         </div>

         <div class="font_kp" style="width: 12%; float: left;">
             থানা/উপজেলা:
         </div>
         <div class="border_bottom_dotted" style="width: 30%; float: left; ">
             <?= strtoupper($model->permanent_thana); ?>
         </div>
         <div class="font_kp" style="width: 6%; float: left;">
             জেলা:
         </div>
         <div class="border_bottom_dotted" style="width: 30%; float: left; ">
             <?= strtoupper(Districts::findOneBySlug($model->permanent_district)); ?>
         </div>
     </div>

     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             &nbsp;
         </div>
          <div class="font_kp" style="width: 12%; float: left;">
             পোস্ট অফিস:
         </div>
         <div class="border_bottom_dotted" style="width: 30%; float: left; ">
             <?= strtoupper($model->permanent_post_office);  ?>
         </div>

         <div class="font_kp" style="width: 10%; float: left;">
             পোস্ট কোড:
         </div>
         <div class="border_bottom_dotted" style="width: 44.5%; float: left; ">
             <?= strtoupper($model->permanent_post_code);   ?>
         </div>
         <!-- <div class="font_kp" style="width: 14%; float: left;">
             ফোন / মোবাইল:
         </div>
         <div class="border_bottom_dotted" style="width: 20.5%; float: left; ">
             <?php // $model->permanent_phone; ?>
         </div> -->
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
         জন্ম তারিখ (এসএসসি সনদপত্র অনুযায়ী) 
         </div>
     </div>

     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             &nbsp;
         </div>
         <div class="font_kp" style="width: 8%; float: left;">
             
         </div>
         <div class="border_bottom_dotted" style="width: 22%; float: left; font-weight: bold;">
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
         <div class="font_kp" style="width: 15%; float: left;">
             জন্ম নিবন্ধন নং :
         </div>
         <div class="border_bottom_dotted" style="width: 82%; float: left;">
             <?= $model->birth_registration_no ?> &nbsp;
         </div>

     </div>


     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 3%; float: left;">
             ৯।
         </div>
         <div class="font_kp" style="width: 5%; float: left;">
             লিঙ্গ:
         </div>
         <div class="border_bottom_dotted" style="width: 10%; float: left;">
             <?= StaticMethod::gender($model->gender) ?>
         </div>
         <div class="font_kp" style="width: 7%; float: left;">
             ১০। ধর্ম:
         </div>
         <div class="border_bottom_dotted" style="width: 13%; float: left;">
             <?= StaticMethod::academicReligion($model->religion) ?>
         </div>
         <div class="font_kp" style="width: 17%; float: left;">
             ১১। বৈবাহিক অবস্থা:
         </div>
         <div class="border_bottom_dotted" style="width: 14%; float: left;">
             <?= StaticMethod::maritalStatus($model->marital_status) ?>
         </div>
         <div class="font_kp" style="width: 11%; float: left;">
             ১২। জাতীয়তা:
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
             ১৩।
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
                         <th class="font_kp">গ্রুপ / ট্রেড</th>
                         <th class="font_kp">শিক্ষা বোর্ড</th>
                         <th class="font_kp">রেজিস্ট্রেশন নং</th>
                         <th class="font_kp">রোল নং</th>
                         <th class="font_kp">পাশের সন / সেশন</th>
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
                      <tr>
                     <td class="font_kp"><?= $model->candidate_type == Constants::CANDIDATE_DE_SAILOR ? 'ডিপ্লোমা' : 'ট্রেড কোর্স' ?></td>
                     <td class="font-size-11"><?= ($model->diploma_trade_institute) ? $model->diploma_trade_institute : 'XX'; ?></td>
                     <td class="font-size-11" colspan="1"> <?= $diploma_trade_course ?> </td>
                     <td class="font-size-11">XX</td>
                     <td class="font-size-11" colspan="1"><?= ($model->diploma_trade_registration_roll) ? $model->diploma_trade_registration_roll : 'XX'; ?></td>
                     <td class="font-size-11">XX</td>
                     <td class="font-size-11">XX</td>
                     <td class="font-size-11">XX</td>
                     <td class="font-size-11"><?= ($model->diploma_trade_gpa) ? $model->diploma_trade_gpa : 'XX'; ?></td>
                 </tr>
                 </tbody>
             </table>
         </div>
     </div>

     <div style="width: 100%; margin-top: 2px;">
         <div class="font_kp" style="width: 4%; float: left;">
             ১৪।
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
             ১৫।
         </div>
         <div class="font_kp" style="width: 69%; float: left;">
             <!-- পিতা মুক্তিযোদ্ধা বা  নৌবাহিনীর কর্মরত / অবঃ / শহীদ /সামরিক /অসামরিক কর্মকর্তা বা কর্মচারী হলে তার বিবরণ (সংশ্লিষ্ট মূল কাগজপত্র সঙ্গে আনতে -->
             মুক্তিযোদ্ধার সন্তান / ক্ষুদ্র নৃ-গোষ্ঠি <!--গোষ্ঠী --> হলে তার বিবরণ (সংশ্লিষ্ট মূল কাগজপত্র সঙ্গে আনতে হবে)
         </div>
         <div class="border_bottom_dotted" style="width: 26%; float: left;">
             &nbsp;
             <?php
                echo StaticMethod::yesNoForFreedom($model->is_freedom_fighter);
                ?>
         </div>
     </div>

     <?php /*
     <div style="width: 100%;">
         <div class="font_kp" style="width: 4%; float: left;">
             &nbsp;
         </div>


         <!-- <div class="font_kp" style="width: 30%; float: left;">
         &nbsp; (ক )মুক্তিযোদ্ধার সন্তান/ ক্ষুদ্র নৃ-গোষ্ঠি :
     </div> -->
         <!-- <div class="border_bottom_dotted" style="width: 65%; float: left;"> -->
         <div class="border_bottom_dotted" style="width: 95%; float: left;">
             <?php
                echo StaticMethod::yesNoForFreedom($model->is_freedom_fighter);
                // if ($model->is_freedom_fighter == Constants::YES) echo  'Yes';
                // else echo 'No';
                // if ($model->is_freedom_fighter == Constants::YES) echo  strtoupper(StaticMethod::relationWithFreedomFighter($model->freedom_fighter_relation));
                // else echo 'XX';
                ?>
         </div>



    
     <div class="font_kp" style="width: 22%; float: left;">
         &nbsp; (ক ) Naval Child :
     </div>
     <div class="border_bottom_dotted" style="width: 67%; float: left;">
         <?php
            if ($model->is_child_of_naval_officer == Constants::YES)
                echo strtoupper($model->naval_father_name) . '(' . StaticMethod::navyUniformCivil($model->naval_uniform_civil) . ')' . ', Offical No : ' . strtoupper($model->naval_office_no) . ', Rank : ' . strtoupper($model->naval_rank); //. ', ' . strtoupper($model->navy_ship_etbd_retired);
            else echo 'XX';
            ?>
     </div>
    



     </div>  */ ?>
     <?php /* 
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
 </div>  */ ?>

     <!--Anser -->
     <?php /* 
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
 */ ?>



     <div style="width: 100%; margin-top: 5px;">
         <div class="font_kp" style="width: 4%; float: left;">
             <!-- ১৬। -->
             ১৬।
         </div>
         <div class="font_kp" style="width: 94%; float: left; color: red; text-align: justify;">
             আমি ঘোষণা করছি যে, উপরে প্রদত্ত সমস্ত তথ্য নির্ভুল ও সত্য । আবেদনপত্রে আমার দেয়া কোন তথ্য / সনদপত্র পরবর্তীতে মিথ্যা প্রমানিত হলে বাংলাদেশ নৌবাহিনী আমার আবেদনপত্র নাকচ করত <!--নাকচকরতঃ --> আমার বিরুদ্ধে যে কোনো আইনানুগ ব্যবস্থা গ্রহণ করলে আমি তা পালনে বাধ্য থাকব। এতদবিষয়ে আমার অভিভাবকের সম্মতি আছে।
         </div>
     </div>


     <!-- Signature -->
     <div style="width: 100%; margin-top: 12px;">
         <div class="font_kp" style="width: 65%; float: left;">
             <div style="width: 100%; margin: 0; padding: 0;">
                 <div class="font_kp" style="width: 26%; float: left;">
                     স্বাক্ষরের স্থান (জেলা)
                 </div>
                 <div class="border_bottom_dotted" style="width: 65%; float: left;">
                     &nbsp;
                 </div>
             </div>
         </div>
         <div class="font_kp" style="width: 34%; float: left;">
             <div class="border_bottom_dotted" style="width: 100%; float: left;">
                 &nbsp;
             </div>
         </div>
     </div>
     <div style="width: 100%; margin-top: 5px;">
         <div class="font_kp" style="width: 65%; float: left;">
             <div style="width: 100%; margin: 0; padding: 0;">
                 <div class="font_kp" style="width: 10%; float: left;">
                     তারিখ
                 </div>
                 <div class="border_bottom_dotted" style="width: 81%; float: left;">
                     &nbsp;
                 </div>
             </div>
         </div>
         <div class="font_kp" style="width: 34%; float: left;">
             <div class="font_kp" style="width: 100%; float: left; text-align: center;">
                 প্রার্থীর নাম এবং স্বাক্ষর
             </div>
         </div>
     </div>


     <div style="width: 100%;   margin-top: 15px;">
         <div class="font_kp" style="width: 4%; float: left;">
             <!-- ১৭। -->
             ১৭।
         </div>
         <div class="font_kp" style="width: 94%; float: left; color: red;">
             আবেদনপত্র দাখিল করার সময় নিম্নবর্ণিত সনদপত্রাদি সংযোজন করতে হবেঃ
         </div>
     </div>
     <?php /*
     <div style="width: 100%; margin-top: 5px;">
         <div class="font_kp" style="width: 4%; float: left;">
             &nbsp;
         </div>
         <div class="font_kp" style="width: 94%; float: left;">
             ক। সকল শিক্ষাগত যোগ্যতার মূল / সাময়িক সনদপত্র এবং মার্কশিটের মূল <!--সত্যায়িত--> কপি - প্রতিটি এক কপি। <br />
             খ। এসএসসি পরীক্ষার মূল রেজিস্ট্রেশন কার্ড এবং মূল এডমিট কার্ড। <br />
             গ। এসএসসি এর অধিক যোগ্যতার ক্ষেত্রে সংশ্লিষ্ট সনদপত্র । <br />
             ঘ। ইউনিয়ন পরিষদের চেয়ারম্যান / পৌরসভার মেয়র অথবা চেয়ারম্যান / ওয়ার্ড কাউন্সিলর বা কমিশনারের নিকট হতে গৃহীত &nbsp;&nbsp;&nbsp;&nbsp;জাতীয়তা ও চরিত্রগত সনদপত্র (বৈবাহিক মর্যাদা ও স্থায়ী বাসস্থানের উল্লেখ থাকতে হবে) - এক কপি ।<br />
             ঙ। জন্মনিবন্ধন / জাতীয় পরিচয়পত্রের সত্যায়িত ফটোকপি - এক কপি ।<br />
             চ। পিতার জাতীয় পরিচয়পত্রের সত্যায়িত ফটোকপি - এক কপি ।<br />
             ছ। শিক্ষা প্রতিষ্ঠান প্রধান কর্তৃক স্বাক্ষরিত প্রশংসাপত্র - এক কপি ।<br />
             জ। অভিভাবকের সম্মতিপত্র - এক কপি ।<br />
             ঝ। সম্প্রতি তোলা পাসপোর্ট আকারের প্রার্থীর নিজের ১৫ কপি, পিতার ১ কপি ও মাতার ১ কপি সত্যায়িত (ল্যাব প্রিন্ট আনএডিটেড) &nbsp;&nbsp;&nbsp;&nbsp; রঙিন ছবি ।<br />
             ঞ। চাকুরিরত প্রার্থীগনের নিয়োগকারী কর্তৃপক্ষের ছাড়পত্র - এক কপি।<br />
             ট। ক্রমিক নম্বর ১৩ ও ১৪ যাদের ক্ষেত্রে প্রযোজ্য তাদের সংশ্লিষ্ট সকল মূল কাগজপত্র।<br />
             <!-- ট। ক্রমিক নম্বর ১৩/১৪/১৫ যাদের ক্ষেত্রে প্রযোজ্য তাদের সংশ্লিষ্ট সকল মূল কাগজপত্র।<br /> -->
         </div>
     </div> */ ?>

     <div style="width: 100%; margin-top: 5px;">
         <div class="font_kp" style="width: 100%; float: left;">
             <table border="1" cellspacing="0" cellpadding="5" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px;">
                 <thead>
                     <tr>
                         <th class="font_kp" style="width: 50px; text-align: center; ">ক্রমিক</th>
                         <th class="font_kp" style="text-align: center;">প্রয়োজনীয় কাগজপত্র</th>
                         <th class="font_kp" style="width: 50px; text-align: center;">ক্রমিক</th>
                         <th class="font_kp" style="text-align: center;">প্রয়োজনীয় কাগজপত্র</th>
                     </tr>
                 </thead>
                 <tbody>
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ক। </td>
                         <td class="font_kp" style="vertical-align: top;">মূল অথবা শিক্ষাবোর্ড কর্তৃক ইস্যুকৃত সাময়িক সনদপত্র (এসএসসি)</td>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ঠ। </td>
                         <td class="font_kp" style="vertical-align: top;">অভিভাবকের সম্মতিসূচক সনদ </td>
                     </tr>


                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">খ। </td>
                         <td class="font_kp" style="vertical-align: top;">মূল মার্কশীট (এসএসসি)</td>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ড। </td>
                         <td class="font_kp" style="vertical-align: top;">বিবাহিত / অবিবাহিত সনদ </td>
                     </tr>
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">গ। </td>
                         <td class="font_kp" style="vertical-align: top;">মূল রেজিস্ট্রেশন কার্ড (এসএসসি)</td>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ঢ। </td>
                         <td class="font_kp" style="vertical-align: top;">ছবি : প্রার্থীর নিজের ১৫ কপি, পিতার ০১ কপি এবং মাতার ০১ কপি (পাসপোর্ট সাইজ রঙিন ছবি, ল্যাবপ্রিন্ট, আনএডিটেবল এবং সত্যায়িত) </td>
                     </tr>
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ঘ। </td>
                         <td class="font_kp" style="vertical-align: top;">মূল প্রবেশপত্র (এসএসসি)</td>
                         <td class="font_kp" style="text-align: center; vertical-align: top;" rowspan="7">ণ। </td>
                         <td class="font_kp" style="vertical-align: top;" rowspan="7">
                             প্রার্থী এইচএসসি পাশ হলে উপরোক্ত কাগজ-পত্রাদির পাশাপাশি নিম্নোক্ত কাগজপত্রাদি সঙ্গে আনতে হবে : <br>

                             <p>(১) মূল অথবা শিক্ষাবোর্ড কর্তৃক ইস্যুকৃত সাময়িক সনদপত্র (এইচএসসি)</p>
                             <p style="font-size: 3px;">&nbsp;</p>
                             <p>(২) মূল সনদপত্র (এইচএসসি)</p>
                             <p style="font-size: 3px;">&nbsp;</p>
                             <p> (৩) মূল রেজিস্ট্রেশন কার্ড (এইচএসসি)</p>
                             <p style="font-size: 3px;">&nbsp;</p>
                             <p> (৪) মূল প্রবেশ পত্র (এইচএসসি)</p>
                             <p style="font-size: 3px;">&nbsp;</p>
                             <p> (৫) শিক্ষাপ্রতিষ্ঠান প্রধান কর্তৃক স্বাক্ষরিত মূল প্রশংসাপত্র/ চারিত্রিক সনদ (এইচএসসি) </p>

                         </td>
                     </tr>                     
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ঙ। </td>
                         <td class="font_kp" style="vertical-align: top;"> শিক্ষাপ্রতিষ্ঠান প্রধান কর্তৃক স্বাক্ষরিত মূল প্রশংসাপত্র/ চারিত্রিক সনদ (এসএসসি) </td>                         
                     </tr>                     
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">চ। </td>
                         <td class="font_kp" style="vertical-align: top;">ট্রেড কোর্স সনদ (প্রযোজ্য ক্ষেত্রে) </td>
                     </tr>
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ছ। </td>
                         <td class="font_kp" style="vertical-align: top;">জন্ম নিবন্ধন সনদ এবং জাতীয় পরিচয় পত্র (যদি থাকে) এর সত্যায়িত কপি </td>
                     </tr>
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">জ। </td>
                         <td class="font_kp" style="vertical-align: top;"> পিতা/ অভিভাবকের জাতীয় পরিচয় পত্রের সত্যায়িত কপি </td>
                     </tr>
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ঝ। </td>
                         <td class="font_kp" style="vertical-align: top;"> মাতার জাতীয় পরিচয় পত্রের সত্যায়িত কপি </td>
                     </tr>
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ঞ। </td>
                         <td class="font_kp" style="vertical-align: top;"> জাতীয়তা/ নাগরিকত্বের সনদ </td>
                     </tr>
                     <tr>
                         <td class="font_kp" style="text-align: center; vertical-align: top;">ট। </td>
                         <td class="font_kp" style="vertical-align: top;">চারিত্রিক সনদ (সদ্য স্বাক্ষরিত) </td>
                          <td class="font_kp" style="text-align: center; vertical-align: top;">ত। </td>
                         <td class="font_kp" style="vertical-align: top;"> প্রার্থীকে সর্বশেষ পাশকৃত শিক্ষা প্রতিষ্ঠানের প্রধান কর্তৃক ইস্যুকৃত টেস্টিমোনিয়াল/ প্রশংসাপত্রের দুইকপি সঙ্গে আনতে হবে। </td>

                     </tr>

                     <tr class="font_kp" style="height: 200px;">
                         <td class="font_kp" style="vertical-align: top; " colspan="4">
                             <b>বিশেষ দ্রষ্ট্যব্য : </b><br>
                             <p>- কোন কারণে ভর্তি পরীক্ষায় অংশগ্রহণকালীন সময়ে মূল কাগজপত্র জমা না দিতে পারলে ফটোকপি (প্রথম শ্রেণির গেজেটেড অফিসার কর্তৃক সত্যায়িত) জমা দিলে চলবে। তবে ফলাফল প্রকাশ পরবর্তী ফরম পূরণ ও নিয়োগ প্রদানের সময় অবশ্যই মূলকপি জমা দিতে হবে।</p>
                             <p style="font-size: 2px;">&nbsp;</p>
                             <p>- জাতীয়তা/নাগরিকত্ব সনদ, বিবাহিত / অবিবাহিত সনদ, অভিভাবকের সম্মতিসূচক সনদ স্ব স্ব ইউনিয়ন পরিষদের চেয়ারম্যান/ পৌরসভার মেয়র অথবা চেয়ারম্যান/ ওয়ার্ড কাউন্সিলর বা কমিশনার কর্তৃক স্বাক্ষরিত / প্রতিস্বাক্ষরিত হতে হবে। </p>

                             <p style="font-size: 2px;">&nbsp;</p>
                             <p>- ক্রমিক নম্বর ১৪ ও ১৫ যাদের ক্ষেত্রে প্রযোজ্য তাদের সংশ্লিষ্ট সকল মূল কাগজপত্র। </p>
                         </td>
                     </tr>
                 </tbody>
             </table>



         </div>
     </div>


     <div style="width: 100%; margin-top: 5px;">
         <div class="font_kp" style="width: 100%; float: left; color: red; font-weight: bold; font-size: 18px;text-decoration: underline;">
             (প্রার্থীকে অবশ্যই ফরমের রোল নম্বর মনে রাখতে হবে এবং যোগাযোগের জন্য উল্লেখ করতে হবে)
         </div>
     </div>

     <div style="width: 100%; margin-top: 15px; ">
         <div class="font_kp " style="width: 100%; float: left; font-weight: bold; font-size: 18px; border-bottom: 2px solid ;">
             নিচের অংশটুকু কেবলমাত্র ভর্তি কেন্দ্র কর্তৃক ব্যবহারের জন্য (প্রার্থী পূরণ করবে না)
         </div>
     </div>
     <br />
     <div style="width: 100%;   margin-top: 15px;">
         <div class="font_kp" style="width: 4%; float: left;">
             <!-- ১৮। -->
             ১৮।
         </div>
         <div class="font_kp" style="width: 26%; float: left;">
             রিক্রুটিং মেডিকেল অফিসারের মন্তব্য:
         </div>
         <div class="border_bottom_dotted" style="width: 69%; float: left;">
             &nbsp;
         </div>
     </div>


     <div style="width: 100%;   margin-top: 30px;">
         <div class="font_kp" style="width: 5%; float: left;">
             তারিখ
         </div>
         <div class="border_bottom_dotted" style="width: 30%; float: left;">
             &nbsp;
         </div>
         <div style="width: 30%; float: left;">
             &nbsp;
         </div>
         <div class="font_kp" style="width: 30%; float: left;">
             মেডিকেল অফিসারের স্বাক্ষর
         </div>
     </div>
     <br />
     <div style="width: 100%;   margin-top: 35px;">
         <div class="font_kp" style="width: 4%; float: left;">
             <!-- ১৯। -->
             ১৯।
         </div>
         <div class="font_kp" style="width: 20%; float: left;">
             রিক্রুটিং কমান্ডারের মন্তব্য:
         </div>
         <div class="border_bottom_dotted" style="width: 75%; float: left;">
             &nbsp;
         </div>
     </div>

     <div style="width: 100%;   margin-top: 30px;">
         <div class="font_kp" style="width: 5%; float: left;">
             তারিখ
         </div>
         <div class="border_bottom_dotted" style="width: 30%; float: left;">
             &nbsp;
         </div>
         <div style="width: 30%; float: left;">
             &nbsp;
         </div>
         <div class="font_kp" style="width: 30%; float: left;">
             রিক্রুটিং কমান্ডারের স্বাক্ষর
         </div>
     </div>

     <footer class="font_kp" style="margin: 30px auto; text-align: center; font-size: 14pt;">
         ২
     </footer>
 <?php } ?>