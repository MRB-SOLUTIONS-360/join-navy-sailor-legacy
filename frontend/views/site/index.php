<?php

/** @var yii\web\View $this */

$this->title = 'My Yii Application';
?>


<section class="signup-step-container pt-120 pb-120" style="background-color: #001731;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-12 col-xl-11">
                <div class="section-title section-title-white">
                    <h1>Life at Bangladesh Navy</h1>
                    <p class="mt-4">Joining and Serving In Bangladesh Navy Is not Just a Career. It is a Way of Life.</p>
                </div>
                <div class="wizard mt-60">
                    <div class="wizard-inner">
                        <ul class="d-flex flex-wrap">
                            <li class="active">
                                <span class="round-tab">Personal Details</span> <i>Share your personal information</i>
                            </li>
                            <li>
                                <span class="round-tab">Personal Details</span> <i>Share your personal information</i>
                            </li>
                            <li>
                                <span class="round-tab">Personal Details</span> <i>Share your personal information</i>
                            </li>
                        </ul>
                    </div>
                </div>
                <form action="#" class="eligilbe-form-wrap mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-box">
                                <input type="date" placeholder="Date of Birth">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box">
                                <select name="cars" id="cars">
                                    <option disabled selected>Gender</option>
                                    <option>Man</option>
                                    <option>Women</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box">
                                <select name="cars" id="cars">
                                    <option disabled selected>Gender</option>
                                    <option>Man</option>
                                    <option>Women</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box">
                                <select name="cars" id="cars">
                                    <option disabled selected>Gender</option>
                                    <option>Man</option>
                                    <option>Women</option>
                                </select>
                            </div>
                        </div>

                        <!-- Height -->
                        <div class="col-lg-12">
                            <div class="height-count d-flex mt-4 mb-4">
                                <h3 class="me-3 text-white">Height</h3>
                                <ul>
                                    <li>0 feet</li>
                                    <li>0 inch</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box">
                                <input type="text" placeholder="Feet">
                                <i class='bx bx-ruler'></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box">
                                <input type="text" placeholder="Inch">
                                <i class='bx bx-ruler'></i>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-box">
                                <input type="text" placeholder="CM">
                                <i class='bx bx-ruler'></i>
                            </div>
                        </div>

                        <!-- Next Button -->
                        <div class="col-lg-12">
                            <div class="form-check-btn-wrap d-flex justify-content-end">
                                <a class="common-btn bg-yellow" href="acadimic.html">Continue</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

<div class="circular-area " id="join" style="background-color: #fff; padding-top: 60px; padding-bottom: 60px;">
    <div class="container">
        <div class="row">
            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <div class="section-title-circular text-center" style="background-color: #FCA900;">
                    <h1 style="color: #1E427E;">Current Circular Sailor</h1>
                </div>
            </div>
        </div>
        <div class="single-circular">
            <div class="row mt-5 mb-4">
                <div class="col-lg-12">
                    <div class="circular-title text-center">
                        <h2><u>Sailor & Mode Batch 2023 A</u></h2>
                    </div>
                </div>
            </div>
            <div class="row justify-content-between">
                <div class="col-xxl-3 col-xl-3 col-lg-3 col-sm-12 col-12">
                    <div class="circular-img">
                        <a href="#"><img src="<?= Yii::getAlias('@web'); ?>/navy/images/cercular-details-2.png" alt=""></a>
                        <div class="details-circular-btn mt-3 d-flex justify-content-center">
                            <a class="common-btn" href="circular-details.html">Details</a>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-8 col-xl-8 col-lg-8 col-sm-12 col-12 mobt-60">
                    <table class="table table-striped circular-table">
                        <tbody>
                            <tr>
                                <th scope="row" width="100%">Start Date: 22 October 2022</th>
                            </tr>
                            <tr>
                                <th scope="row" width="100%" class="text-danger">End Date: 22 October 2022</th>
                            </tr>
                            <tr>
                                <th scope="row" width="100%">
                                    <div class="course-apply-btn-wrap">
                                        <a class="apply-btn" href="https://joinnavy.navy.mil.bd/site/eligible?id=1"><i class="bx bx-file"></i> Apply Now</a>
                                    </div>
                                </th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Circular Mobile End -->
<!-- Join Area Start -->
<div class="join-area" style="background:#FFE500;">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-12 col-sm-12 col-12">
                <div class="section-title">
                    <h1>Confused about Your Eligibility?</h1>
                    <p class="mt-4">You can check your eligibility right here before applying to any position</p>
                </div>
            </div>
            <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-12 col-sm-12 col-12 mobt-24">
                <div class="join-btn-wrap text-lg-end wow fadeIn" data-wow-duration="2s" data-wow-delay=".6s">
                    <button type="button" class="common-btn bg-black" style="border:none;" data-bs-toggle="modal" data-bs-target="#staticBackdrop-eligibility">
                        Check Eligibility
                    </button>
                    <!-- Modal -->
                    <div class="modal fade text-center" id="staticBackdrop-eligibility" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog eligibility">
                            <div class="modal-content">
                                <div class="modal-header position-relative">
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: none; color:#EF3F2E; opacity:1; font-size:23px;"><i class="bi bi-x-lg"></i></button>
                                    <span class="position-absolute">Close & Check Eligibility</span>
                                </div>
                                <div class="modal-body">
                                    <img src="<?= Yii::getAlias('@web'); ?>/navy/images/eligibility.png" alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>