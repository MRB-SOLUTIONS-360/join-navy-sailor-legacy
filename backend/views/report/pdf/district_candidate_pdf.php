<?php

use common\models\CanDesignation;
use common\models\SailorBatchs;
use common\models\SailorCenters;

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
        <?php
        /* if ($filter['center']) { ?>
            <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> কেন্দ্র: <?= SailorCenters::getAllCenterSession($filter['center'])  ?></h4>
        <?php }  */ ?>
        <?php
        if ($filter['district']) { ?>
            <h4 class="h2_padding_margin_0 font_kp h2_head_block_common" style="margin: 0px"> জেলা: <?= ucfirst($filter['district']) ?> </h4>
        <?php }    ?>

    </div>
    <div class="col-lg-2"></div>
</div>
<div class="row">
    <table class="table" style="  width: 100%;">
        <thead>
            <tr>
                <th>SL</th>
                <th>Designation</th>
                <th>Total Candidate</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $total = 0;
            foreach ($model as $k => $value) {
                $total  += $value['candidate_count'];
                $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
            ?>
                <tr>
                    <td><?= ($k + 1) ?></td>
                    <td><?= ($desig) ?></td>
                    <td><?= ($value['candidate_count']) ?></td>
                </tr>
            <?php }  ?>

            <tr>
                <td colspan="2" style="text-align: right; font-weight: bold;">Total</td>
                <td style="font-weight: bold;"><?= $total  ?></td>
            </tr>
        </tbody>
    </table>
</div>
'