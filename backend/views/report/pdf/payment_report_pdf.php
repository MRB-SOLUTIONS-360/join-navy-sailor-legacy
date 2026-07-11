<?php

use common\models\CanDesignation;

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
</style>
<table class="table table-striped table-bordered mt-2">
    <thead>
        <tr>
            <th>SL</th>
            <th>Application ID</th>
            <th>Designation</th>
            <th>Name</th>
            <th>Payment Date</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($model) {
            foreach ($model as $k => $value) {
                $desig = CanDesignation::getAllDesignationSession($value['candidate_designation']);
        ?>
                <tr>
                    <td><?= ($k + 1) ?></td>
                    <td><?= $value['app_unique_id'] ?></td>
                    <td><?= $desig; ?></td>
                    <td><?= $value['name'] ?></td>
                    <td><?= date('d M Y, h:i A', strtotime($value['trans_date'])) ?></td>
                </tr>
            <?php }
        } else { ?>
            <tr>
                <td colspan="5"> No record found</td>
            </tr>
        <?php }
        ?>
    </tbody>
</table>