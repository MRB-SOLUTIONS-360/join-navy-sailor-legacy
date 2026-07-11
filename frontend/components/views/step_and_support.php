<?php // $slug; 
?>



<div class="form__steps">
    <div class="single_step <?= in_array(1, $steps) ? 'active_step' : '' ?>">
        <!-- <a href="#">Payment</a> -->
        <div class="step_index">
            <span>01</span>
        </div>
        <div class="step_name">
            <span>Payment</span>
        </div>
    </div>
    <div class="single_step <?= in_array(2, $steps) ? 'active_step' : '' ?>">
        <!-- <a href="#">Academic Information</a> -->
        <div class="step_index">
            <span>02</span>
        </div>
        <div class="step_name">
            <span>Academic Information</span>
        </div>
    </div>
    <div class="single_step <?= in_array(3, $steps) ? 'active_step' : '' ?>">
        <!-- <a href="#">Personal Information</a> -->

        <div class="step_index">
            <span>03</span>
        </div>
        <div class="step_name">
            <span>Personal Information</span>
        </div>
    </div>
    <div class="single_step <?= in_array(4, $steps) ? 'active_step' : '' ?>">
        <!-- <a href="#">Application Preview</a> -->
        <div class="step_index">
            <span>04</span>
        </div>
        <div class="step_name">
            <span>Application Preview</span>
        </div>
    </div>
    <div class="single_step">
        <!-- <a href="#">Complete</a> -->
        <div class="step_index">
            <span>05</span>
        </div>
        <div class="step_name">
            <span>Complete</span>
        </div>
    </div>
</div>