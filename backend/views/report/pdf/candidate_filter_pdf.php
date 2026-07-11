<?php

use common\models\CanDesignation;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\static\DataEncryption;
use common\static\StaticMethod;

?>

<style>
    table,
    th,
    td {
        border: 1px solid;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 5px;
    }

    .font_kp {
        font-family: kalpurush;
    }

    .h2_padding_margin_0 {
        margin: 0px;
    }
</style>

<div class="row" style="text-align: center;">
    <div class="col-lg-2"> </div>
    <div class="col-lg-8">
        <img src="<?= Yii::getAlias('@rootDirFilUpload'); ?>/media/main_logo.png" alt="QR not found" style="width:80px; text-align: center; margin: 0 auto;">
        <h2 class="h2_padding_margin_0 font_kp" style="font-size: 10pt; font-weight: bold; margin: 0px; ">বাংলাদেশ নৌবাহিনী</h2>
        <h2 class="h2_padding_margin_0 font_kp" style="line-height: 17px; font-size: 10pt; font-weight: bold; margin: 0px"><!--নাবিক,মহিলা --> নাবিক ও এমওডিসি (নৌ) পদে ভর্তির আবেদনপত্র </h2>
        <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> ব্যাচ: <?= SailorBatchs::getAllBatchSession($filter['batch']) ?></h4>
        <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> কেন্দ্র: <?= SailorCenters::getAllCenterSession($filter['center']) ?></h4>
    </div>
    <div class="col-lg-2"></div>
</div>
<div class="row">
    <table class="table table-striped table-bordered mt-2">
        <thead>
            <tr>
                <th>SL</th>
                <th>Application ID</th>
                <th>Designation</th>
                <th>District</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Phone No</th>
                <th>Serial No</th>
                <th>Exam Date</th>
                <th>Photo</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($model as $k => $value) {
                $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
            ?>
                <tr>
                    <td><?= ($k + 1) ?></td>
                    <td><?= $value['app_unique_id'] ?></td>
                    <td><?= $desig; ?></td>
                    <td><?= ucfirst($value['permanent_district']) ?></td>
                    <td><?= $value['name'] ?></td>
                    <td><?= ($value['gender']) ? StaticMethod::gender($value['gender']) : ''; ?></td>
                    <td><?= DataEncryption::dataDecrypt($value['permanent_phone']) ?></td>
                    <td><?= $value['serial_no'] ?></td>
                    <td><?= date('d-m-Y', strtotime($value['exam_date'])) ?></td>
                    <td>
                        <?php
                        if ($value['photo'] && file_exists(Yii::getAlias('@rootDirFilUpload') . $value['photo'])) {
                            //echo Yii::getAlias('@rootDirFilUpload') .'/media/main_logo.png';                         
                            //echo Yii::getAlias('@rootDirFilUpload') . $value['photo'];                         
                        ?>
                            <img src="<?= Yii::getAlias('@rootDirFilUpload'); ?><?= $value['photo']; ?>" alt="QR not found" style="width:80px; text-align: center; margin: 0 auto;">
                        <?php  } ?>
                    </td>
                </tr>
            <?php }  ?>
        </tbody>
    </table>
</div>
'