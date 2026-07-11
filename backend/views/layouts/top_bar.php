<?php

use yii\helpers\Html;
?>
<div class="navbar-custom">
    <div class="topbar container-fluid">
        <div class="d-flex align-items-center gap-lg-2 gap-1">
            <!-- Topbar Brand Logo -->
            <div class="logo-topbar">
                <!-- Logo Dark -->
                <a href="index.html" class="logo-dark">
                    <span class="logo-lg">
                        <img src="<?= Yii::getAlias('@web'); ?>/adminAsset/images/navy_logo.png" alt="dark logo">
                    </span>
                    <span class="logo-sm">
                        <img src="<?= Yii::getAlias('@web'); ?>/adminAsset/images/navy_logo.png" alt="small logo">
                    </span>
                </a>
            </div>
            <!-- Sidebar Menu Toggle Button -->
            <button class="button-toggle-menu">
                <i class="mdi mdi-menu"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="navbar-toggle" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <div class="lines">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </button>
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-3">
            <!-- <li class="d-none d-md-inline-block">
            <a class="nav-link" href="#" data-toggle="fullscreen">
                <i class="ri-fullscreen-line font-22"></i>
            </a>
        </li> -->
            <li class="dropdown">
                <a class="nav-link dropdown-toggle arrow-none nav-user px-2" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false">
                    <span class="account-user-avatar">
                        <img src="<?= Yii::getAlias('@web'); ?>/adminAsset/images/users/avatar-1.jpg" alt="user-image" width="32" class="rounded-circle">
                    </span>
                    <span class="d-lg-flex flex-column gap-1 d-none">
                        <h5 class="my-0"><?= Yii::$app->user->identity->username; ?></h5>
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated profile-dropdown">
                    <!-- item-->
                    <div class=" dropdown-header noti-title">
                        <h6 class="text-overflow m-0">Welcome !</h6>
                    </div>
                    <!-- item-->

                    <?php
                    echo Html::beginForm(['/site/logout'], 'post', ['class' => 'd-flex'])
                        . Html::submitButton(
                            ' <i class="mdi mdi-logout me-1"></i> Logout (' . Yii::$app->user->identity->username . ')',
                            ['class' => 'btn btn-link logout text-decoration-none']
                        )
                        . Html::endForm();
                    ?>
                </div>
            </li>
        </ul>
    </div>
</div>