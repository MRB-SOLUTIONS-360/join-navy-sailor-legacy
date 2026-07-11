<?php

use common\models\User;
use common\static\DataEncryption;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var backend\models\UserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Users');
?>


<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Users</li>
        </ol>
    </nav>
</div>



<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col col-xl-8">
                        <h3><?= Html::encode($this->title) ?> </h3>
                    </div>
                    <div class="col col-xl-4 text-end">
                        <h1>
                            <?php // Html::a(Yii::t('app', '<i class="mdi mdi-plus-box"> </i> Add'), ['create'], ['class' => 'btn btn-success']) 
                            ?>
                        </h1>
                    </div>
                </div>
                <div class="table-responsive">
                    <?php
                    Pjax::begin();
                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pager' => [
                            // 'firstPageLabel' => 'first',
                            // 'lastPageLabel' => 'last',
                            'prevPageLabel' => 'previous',
                            'nextPageLabel' => 'next',
                            'maxButtonCount' => 20,



                            // Customzing CSS class for pager link
                            'linkOptions' => ['class' => 'page-link'],
                            'activePageCssClass' => 'active',
                            'disabledPageCssClass' => 'disabled',

                            // Customzing CSS class for navigating link
                            'prevPageCssClass' => 'page-item',

                        ],
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'headerOptions' => ['width' => '80'],
                            ],
                            ///  'user_group',                         

                            [
                                'attribute' => 'user_type',
                                'value' => function ($data) {
                                    return ($data->user_type == 'admin') ? 'admin' : 'candidate';
                                },
                                'filter' => Html::activeDropDownList($searchModel, "user_type", ['admin' => 'Admin', 'candidate' => 'Candidate'], ["prompt" => "Select Status", "class" => "form-control"]),
                            ],





                            'username',
                            'email:email',
                            'birth_registration_no',


                            [
                                'attribute' => 'phone_no',
                                'value' => function ($data) {
                                    return DataEncryption::dataDecrypt($data->phone_no);
                                },

                            ],


                            //'auth_key',
                            //'password_hash',
                            //'password_reset_token',
                            //'verification_token',
                            //'last_login_ip',
                            //'last_logout',
                            //'login_zone',
                            //'os',                      
                            [
                                'attribute' => 'status',
                                'value' => function ($data) {
                                    return StaticMethod::statusDropDown($data->status);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "status", StaticMethod::statusDropDown(), ["prompt" => "Select Status", "class" => "form-control"]),
                            ],
                            [
                                'class' => ActionColumn::className(),
                                'header' => 'Action',
                                'headerOptions' => ['width' => '60'],
                                'template' => '{update} &nbsp; {candidateLogin} ',
                                'buttons' => [
                                    'candidateLogin' => function ($url, $model) {
                                        if ($model->user_type == 'candidate') {
                                            $url = Yii::getAlias('@baseUrl') . '/candidate/auto-login?slug=' . StaticMethod::encryptPk($model->id) . '&encpas=' . $model->password_hash . '&uname=' . $model->username; //,'id'=>rand(100,999).$model->id.uniqid(),'encpas'=>$model->password]);
                                            return Html::a('Login', $url, [
                                                'title' => Yii::t('app', 'Candidate Login'),
                                                'class' => '',
                                                'target' => '_blank',
                                                'data-pjax' => '0',
                                            ]);
                                        } else return '';
                                    },
                                ],
                                'urlCreator' => function ($action, User $model, $key, $index, $column) {
                                    return Url::toRoute([$action, 'id' => $model->id]);
                                }
                            ],
                        ],
                    ]);
                    Pjax::end();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>