<?php

/** @var \yii\web\View $this */
/** @var string $content */

use common\widgets\Alert;
use frontend\assets\AppNavyAsset;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;

AppNavyAsset::register($this);
?>
<?php $this->beginPage() ?>
<!doctype html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link rel="icon" href="<?= Yii::getAlias('@web'); ?>/navy/images/fav.png" type="image/gif" sizes="20x20">

    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-0YDFGTTCDR"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }
        gtag('js', new Date());

        gtag('config', 'G-0YDFGTTCDR');
    </script>


</head>

<body>
    <?php $this->beginBody() ?>
    <!-- <script language="JavaScript">
        history.forward()
    </script> -->

    <!-- Preloader -->
    <!-- <div class="preloader">
	<div class="spinner-wrap">
		 <div class="preloader-logo">
			  <img src="assets/images/fav.png" alt="" class="img-fluid">
		 </div>
		 <div class="spinner"></div> 
	</div>
</div> -->

    <!-- Preloader End -->

    <!-- back to to button start-->
    <a href="#" id="scroll-top" class="back-to-top-btn"><i class="bi bi-arrow-up"></i></a>
    <!-- back to to button end-->
    <!-- Header area -->
    <header>
        <!-- Menu -->
        <nav>
            <div class="header-menu-area header-menu-area-form" style="background-color: #DDDDDD;">
                <div class="container-fluid">
                    <div class="row align-items-center">
                        <div class="col-xxl-3 col-xl-2 col-lg-2 col-sm-6 col-6 ">
                            <div class="logo text-left">
                                <a href="<?= Url::home(); ?>"><img src="<?= Yii::getAlias('@web'); ?>/navy/images/logo.png" alt=""></a>
                            </div>
                        </div>

                        <div class="col-xxl-9 col-xl-10 col-lg-10 col-sm-6 col-6 ">
                            <div class="menu-btn-wrap d-flex justify-content-end">
                                <div class="mobile__menu" id="mobileSidebar">
                                    <button class="close__menu d-xl-none" id="menuClose">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                                            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8 2.146 2.854Z" />
                                        </svg>
                                    </button>
                                    <ul>
                                        <li><a class="common-btn-v2" href="<?= Url::toRoute(['candidate/download-form']) ?>">Download Form</a></li>
                                        <?php if (Yii::$app->user->isGuest) { ?>
                                            <li>
                                                <a class="common-btn-v2 bg-red" href="<?= Url::toRoute(['candidate/login']) ?>">Login</a>
                                            </li>
                                            <li>
                                                <a class="common-btn-v2" href="<?= Url::toRoute(['candidate/sign-up']) ?>">SignUp</a>
                                            </li>

                                        <?php } else { ?>
                                            <li>
                                                <a class="common-btn-v2" href="<?= Url::toRoute(['candidate/change-password']) ?>">Change Password</a>
                                            </li>
                                            <li>
                                                <a class="common-btn-v2" href="<?= Url::toRoute(['/my-application']) ?>">My Application</a>
                                            </li>
                                            <li>
                                                <?php
                                                echo Html::beginForm(['/candidate/logout'], 'post', ['class' => 'd-flex'])
                                                    . Html::submitButton(
                                                        ' <i class="mdi mdi-logout me-1"></i> Logout (' . Yii::$app->user->identity->username . ')',
                                                        ['class' => 'common-btn-v2', 'style' => 'border:none; ']
                                                    )
                                                    . Html::endForm();
                                                ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                                <button class="toggle_btn d-inline-block d-xl-none" id="menuOpen">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <?php /* 
                        <div class="col-xxl-9 col-xl-10 col-lg-10 col-sm-6 col-6 ">
                            <div class="menu-btn-wrap d-flex justify-content-end">
                                <a class="common-btn bg-red" href="#">Download Form</a>
                                <?php
                                if (Yii::$app->user->isGuest) {
                                ?>
                                    &nbsp;
                                    <a class="common-btn bg-red" href="<?= Url::toRoute(['candidate/login']) ?>">Login</a>
                                    &nbsp;
                                    <a class="common-btn bg-red" href="<?= Url::toRoute(['candidate/sign-up']) ?>">SignUp</a>
                                <?php } else { ?>
                                    &nbsp;
                                    <a class="common-btn bg-red" href="#">My Application</a>
                                    &nbsp;
                                    <?php
                    echo Html::beginForm(['/candidate/logout'], 'post', ['class' => 'd-flex'])
                        . Html::submitButton(
                            ' <i class="mdi mdi-logout me-1"></i> Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'btn btn-link logout text-decoration-none']
                        )
                        . Html::endForm();
                    ?>


                                <?php } ?>
                            </div>
                        </div>  */ ?>
                    </div>
                </div>
            </div>

        </nav>
        <!-- Menu end -->
    </header>
    <!--  Header area end -->

    <div class="container-">
        <?= $content ?>
    </div>


    <!-- Join Area End -->
    <!-- Footer -->
    <div class="footer-area" style="background-color: #001731;">
        <div class="container">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-sm-12 col-12">
                    <div class="footer-wrap">
                        <div class="row justify-content-between">
                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12">
                                <div>
                                    <div class="footer-logo">
                                        <a href="index.html"><img src="<?= Yii::getAlias('@web'); ?>/navy/images/footer-logo.png" alt=""></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-3 col-md-6 col-sm-6 col-6">
                                <div class="single-widget">
                                    <div class="footer-title">
                                        <h3>Important Links</h3>
                                    </div>
                                    <div class="footer-link">
                                        <ul>
                                            <li><a href="#">News</a></li>
                                            <li><a href="#">Privacy Policy</a></li>
                                            <li><a href="#">Code of Conduct</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-6">
                                <div class="single-widget">
                                    <div class="footer-title">
                                        <h3>Contact Us</h3>
                                    </div>
                                    <div class="footer-link">
                                        <ul>
                                            <li><a href="tel:029836141"><i class='bx bx-phone'></i> +02-9836141-9</a></li>
                                            <li><a href="mailto:career@navy.mil.bd"><i class='bx bx-envelope'></i> career@navy.mil.bd</a></li>
                                            <li><a href="#"><i class='bx bx-location-plus'></i> Naval Headquarters, <br> Banani, Dhaka-1213</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-2 col-lg-2 col-md-6 col-sm-6 col-6">
                                <div class="single-widget">
                                    <div class="footer-title">
                                        <h3>Social Media</h3>
                                    </div>
                                    <div class="footer-link">
                                        <ul>
                                            <li><a href="https://www.facebook.com/bangladeshnavy.mil.bd/?ti=as" target="_blank"><i class='bx bxl-facebook'></i> Facebook</a></li>
                                            <li><a href="#" target="_blank"><i class='bx bxl-instagram'></i> Instagram</a></li>
                                            <li><a href="https://www.youtube.com/channel/UC7fsm_alAk9FKG6sho5pKsw" target="_blank"><i class='bx bxl-youtube'></i> Youtube</a></li>
                                            <li><a href="#" target="_blank"><i class='bx bxl-linkedin'></i> Linkedin</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="copy-right-area">
                        <p class="copy-text">All rights reserved, Copyright © 2022 Bangladesh Navy,
                            Developed by <a href="https://www.unlocklive.com/">Unlocklive It Ltd.</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->
    <!-- Jquery JS -->
    <script>
        const menuOpen = document.getElementById('menuOpen');
        const menuClose = document.getElementById('menuClose');
        const mobileSidebar = document.getElementById('mobileSidebar');

        // console.log(menuClose, menuOpen, mobileSidebar)

        menuOpen && menuOpen.addEventListener('click', () => {
            mobileSidebar.classList.toggle('active-menu')
        })
        menuClose && menuClose.addEventListener('click', () => {
            mobileSidebar.classList.remove('active-menu')
        })
    </script>
    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage(); ?>