<?php

/** @var yii\web\View $this */
/** @var string $content */

use backend\assets\AppAdminAsset;
use yii\helpers\Html;

AppAdminAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->registerCsrfMetaTags() ?>
    <?php $this->head() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="<?= Yii::getAlias('@web'); ?>/adminAsset/images/favicon.ico">
    <script src="<?= Yii::getAlias('@web'); ?>/adminAsset/js/vendor.min.js"></script>
    <script src="<?= Yii::getAlias('@web'); ?>/adminAsset/js/hyper-config.js"></script>
</head>

<body class="show">
    <?php $this->beginBody() ?>
    <div class="wrapper">
        <?= $this->render('top_bar'); ?>
        <?= $this->render('left_side_menu');  ?>
        
        <div class="content-page">
            <div class="content">
                <!-- Start Content-->
                <div class="container-fluid">
                    <?= $content ?>
                </div>
                <!-- container -->
            </div>
            <!-- content -->
            <!-- Footer Start -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © Unlocklive IT Limited 
                        </div>
                        <!-- <div class="col-md-6">
                            <div class="text-md-end footer-links d-none d-md-block">
                                <a href="javascript: void(0);">About</a>
                                <a href="javascript: void(0);">Support</a>
                                <a href="javascript: void(0);">Contact Us</a>
                            </div>
                        </div> -->
                    </div>
                </div>
            </footer>
            <!-- end Footer -->
        </div>
    </div>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage(); ?>
