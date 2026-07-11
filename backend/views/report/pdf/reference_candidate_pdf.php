<?php

use common\models\CanDesignation;
use common\models\Districts;
use common\models\SailorCenters;
use common\models\Sailors;
use common\models\SailorBatchConfiguration;
use common\static\DataEncryption;
use common\static\Constants;
 

$centerName =  ($model[0]['center_id']) ? SailorCenters::find()->select(['id', 'name_en'])->where(['id' => $model[0]['center_id']])->one()->name_en : '';
$ex_date  = $model[0]['exam_date'] ?? date('d-m-Y');
$district = $model[0]['eligible_district'];
// $district_full_name = Districts::find()
//     ->select(['name_en'])
//     ->where(['in', 'slug', $district])
//     ->asArray()
//     ->one();
// if ($district_full_name)
//     $district_full_name = $district_full_name['name_en'];
// else $district_full_name = '';


// Total Candidate of this batch 
$total_candidate = Sailors::find()->where(['batch_id' => $model[0]['batch_id']])
->andWhere(['center_id' => $model[0]['center_id']])
->andWhere(['exam_date' => $model[0]['exam_date']])
->andWhere(['not', ['exam_group' => null]])
->andWhere( ['application_status' => 1])->count();
// $total_candidate = Sailors::find()->where(['eligible_district' => $district])->andWhere(['batch_id' => $model[0]['batch_id']])->andWhere(['center_id' => $model[0]['center_id']])->andWhere(['not', ['exam_group' => null]])->count();
//echo  $toal_count->createCommand()->getRawSql();


$total = 0;
$medical = $petrolman = $seaman_comm_tech = $cook = $modc = $topas = 0;
if ($configuration) {
    $seaman_comm_tech = !empty($configuration['du_uc_can_total']) ? $configuration['du_uc_can_total'] : 0;
    $medical = !empty($configuration['medical_can_total']) ? $configuration['medical_can_total'] : 0;
    $petrolman = !empty($configuration['pertol_store_can_total']) ? $configuration['pertol_store_can_total'] : 0;
    $cook = !empty($configuration['cook_steward_can_total']) ? $configuration['cook_steward_can_total'] : 0;
    $modc = !empty($configuration['modc_can_total']) ? $configuration['modc_can_total'] : 0;
    $topas = !empty($configuration['topass_can_total']) ? $configuration['topass_can_total'] : 0;
    $total = $seaman_comm_tech + $medical + $petrolman + $cook + $modc + $topas;
}

$branchs = [];
foreach ($model as $k => $val){
    $branch = CanDesignation::getAllDesignationSession($val['candidate_designation']);
    $branchs[] = $branch;  
}
$branchs = array_unique($branchs);

$district_by_slug = Districts::getAllActiveDistrictBySlug();
$all_designation = CanDesignation::getAllDesignation(Constants::CANDIDATE_SAILOR);
 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Candidate List</title>
</head>

<body>
    <div class="main-wrap" style="max-width: 100%; text-align: center;">
        <h3 style="font-size: 20px; text-align: center; line-height: 1.3; text-decoration:underline">
            <?php // strtoupper($district_full_name); ?>
            <?php 
            if ($branchs) echo 'Branch : '.implode(' / ', $branchs);            
            ?>          
            <br> <?= strtoupper($centerName); ?> <br> DATE: <?php echo strtoupper(date('d M Y', strtotime($ex_date))); ?>-TOTAL APPLICANT-<?= $total_candidate ?></h3>
    </div>
    <?php /*
    <div style="margin-top: 2px;">
        <h5 style="margin-bottom: 15px; font-size: 18px; text-decoration: underline; text-decoration-thickness: 1.5px;">Quota - <?= strtoupper($district_full_name); ?></h5>

        <table style="width: 100%; border: 1px solid #000;  border-collapse: collapse;">
            <tr style="border: 1px solid #000;">
                <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">DEUC (Seaman / <br>Communication / Technical)
                </th>
                <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Medical</th>
                <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Patrolman / Writer / Store </th>
                <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Cook / Steward
                </th>
                <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">MODC</th>
                <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Topass </th>
                <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Total</th>
            </tr>
            <tr style="border: 1px solid #000;">
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"><?= $seaman_comm_tech; ?></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"><?= $medical; ?></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"><?= $petrolman; ?></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"><?= $cook; ?></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"><?= $modc; ?></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"><?= $topas; ?></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"><?= $total; ?></td>

            </tr>
            <tr style="border: 1px solid #000;">
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;"></td>
                <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center;">&nbsp;</td>
            </tr>
        </table>
    </div>
    */ ?>

    <div style="margin-top: 10px;">
        <?php /*<h5 style="margin-bottom: 15px; font-size: 18px; text-decoration: underline; text-decoration-thickness: 1.5px;">Quota - <?= strtoupper($district_full_name); ?></h5> */ ?>
        <table style="width: 100%; border: 1px solid #000;  border-collapse: collapse;">
            <thead>
                <tr style="border: 1px solid #000;">
                    <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Ser</th>
                    <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Roll & Mobile</th>
                    <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;"> District <!--Branch--></th>
                    <th style="width:140px; border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Description</th>
                    <th style="width:150px; border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Reference</th>
                    <th style="width:140px; border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Relationship</th>
                    <th style="width:70px; border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Subject</th>
                    <th style="width:140px; border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Final Medical </th>
                    <th style="border: 1px solid #000; padding: 4px 4px; font-weight: 600;">Fit/Unfit</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($model as $k => $val) {  ?>
                    <tr style="border: 1px solid #000;">
                        <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 200; text-align: center; vertical-align: top; font-size: 12px;"><?= ($k + 1) ?></td>
                        <td style="border: 1px solid #000; padding: 4px 6px; font-weight: 200; vertical-align: top;font-size: 12px;">
                            <span> Roll No: <?= $val['serial_no']; ?></span> <br>
                            <span>Mob: <?= DataEncryption::dataDecrypt($val['permanent_phone']); ?></span> <br>
                            <span>Designation: <?= CanDesignation::getAllDesignationSession($val['candidate_designation']); ?></span>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px 6px; font-weight: 200; vertical-align: top;font-size: 12px;">
                            <?php
                            $district = $district_by_slug[$val['eligible_district']] ?? $val['eligible_district']; 
                           echo ucfirst($district);
                            // $branch = CanDesignation::getAllDesignationSession($val['candidate_designation']);
                            // $explode = explode('(', $branch);
                            // if (array_key_exists(0, $explode))
                            //     echo '<p>' . $explode[0] . '</p>';
                            // if (array_key_exists(1, $explode))
                            //     echo '<p>(' . $explode[1] . '</p>';
                            ?></td>
                        <td style="border: 1px solid #000; padding: 4px 6px; font-weight: 200; vertical-align: top;font-size: 12px;">
                            <span>Name : <?= $val['name']; ?></span><br>
                            <span>F/Name : <?= $val['father_name']; ?></span><br>
                            <span>Dist: <?= ucfirst(strtolower($val['permanent_district'])); ?></span><br>
                            <?php
                            if ($val['ssc_gpa'] && $val['ssc_group']) {
                            ?>
                                <span>GPA : <?= $val['ssc_gpa']; ?>, <?= $val['ssc_group']; ?></span><br>
                            <?php } ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 200; text-align: center; vertical-align: top;font-size: 12px;">
                            <?php
                            if (!empty($val['referred_by'])) {
                                $ref_dec = json_decode($val['referred_by'], true);
                                echo implode(' & ', $ref_dec);
                                // foreach ($ref_dec as $k => $v) {
                                //     // if ($v) echo '<p>' . $v . '</p>';
                                //     if ($v) echo ' & '.$v ;
                                // }
                            } else echo '&nbsp;';
                            ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 200; text-align: center; vertical-align: top; max-width: 80px; font-size: 12px;">
                            <?php
                            if (!empty($val['relationship'])) {
                                $ref_dec = json_decode($val['relationship'], true);
                                echo implode(' & ', $ref_dec);
                                // foreach ($ref_dec as $k => $v) {
                                //     // if ($v) echo '<p>' . $v . '</p>';
                                //     if ($v) echo ' & '.$v ;
                                // }
                            } else echo '&nbsp;';
                            ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 6px 6px; font-weight: 200; vertical-align: top;font-size: 12px;">
                            <span>B-</span><br>
                            <span>E-</span><br>
                            <span>M-</span><br>
                            <span>Sc-</span><br>
                            <span>Gk-</span>
                        </td>
                        <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center; vertical-align: top;">&nbsp;</td>
                        <td style="border: 1px solid #000; padding: 4px 4px; font-weight: 400; text-align: center; vertical-align: top;">&nbsp;</td>
                    </tr>
                <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>