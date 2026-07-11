<?php

use common\models\CanDesignation;
use common\models\DeSailors;
use common\models\Districts;
use common\models\SailorBatchs;
use common\models\SailorCenters;

use common\static\Constants;
use common\static\DataEncryption;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var common\models\DeSailorsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Direct Entry Sailors');
$this->params['breadcrumbs'][] = $this->title;


$session = Yii::$app->session;
if ($session->has('all_district_session')) {
    $all_district_session = $session->get('all_district_session');
} else {
    $all_district_session = Districts::getAllDistrict();
    $session->set('all_district_session', $all_district_session);
}


?>

<script>
    // var root = document.getElementsByTagName( 'html' )[0]; // '0' to assign the first (and only `HTML` tag)
    // root.setAttribute( 'class', 'sidebar-enable' );
    // root.setAttribute( 'data-sidenav-size', 'condensed' ); 
</script>
<style>
    .table-responsive {
        overflow-x: scroll !important;
        overflow-y: auto;
    }

    .fake-scrollbar {
        overflow-x: scroll !important;
        overflow-y: auto;
    }
</style>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#">DE Sailor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Candidate List</li>
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
                <div style="position: relative;">
                    <div class="fake-scrollbar" style="position: sticky; top: 46px;">
                        <div>&nbsp;</div>
                    </div>
                    <div class="table-responsive">

                        <?php Pjax::begin(); ?>

                        <?= GridView::widget([
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
                                ['class' => 'yii\grid\SerialColumn'],
                                'app_unique_id',
                                                   
                                [
                                'attribute' => 'candidate_type',
                                'value' => function ($data) {
                                    return StaticMethod::candidateType($data->candidate_type);
                                },
                                // 'filter' => Html::activeDropDownList($searchModel, "candidate_type", StaticMethod::candidateType(), ["prompt" => "Select", "class" => "form-control"]),
                                'filter' => Html::activeDropDownList($searchModel, "candidate_type", [Constants::CANDIDATE_DE_SAILOR => 'Direct Entry Artificer', Constants::CANDIDATE_DE_SAILOR_DOCKYARD => 'Direct Entry Dockyard'], ["prompt" => "Select Type", "class" => "form-control"]),
                            ],

                                [
                                    'attribute' => 'candidate_designation',
                                    'value' => function ($data) {
                                        return CanDesignation::getAllDesignationSession($data->candidate_designation);
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "candidate_designation", $searchModel->candidate_type ? CanDesignation::getAllDesignation(type: intval($searchModel->candidate_type)) : [], ["prompt" => "Select Type", "class" => "form-control"]),
                                ],
                                [
                                    'attribute' => 'diploma_trade_course',
                                    'value' => function ($data) use ($all_subjects) {
                                        return array_key_exists($data->diploma_trade_course, $all_subjects) ? $all_subjects[$data->diploma_trade_course] : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "diploma_trade_course", $all_subjects, ["prompt" => "Select Type", "class" => "form-control"]),
                                ],

                                [
                                    'attribute' => 'center_id',
                                    'value' => function ($data) {
                                        return SailorCenters::getAllCenterSession($data->center_id);
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "center_id", SailorCenters::getAllCenter(), ["prompt" => "Select Center", "class" => "form-control"]),
                                ],

                                [
                                    'attribute' => 'batch_id',
                                    'value' => function ($data) {
                                        return SailorBatchs::getAllBatchSession($data->batch_id);
                                    },
                                    // 'filter' => Html::activeDropDownList($searchModel, "batch_id", SailorBatchs::getAllBatch(type: Constants::CANDIDATE_SAILOR), ["prompt" => "Select Batch", "class" => "form-control"]),
                                    'filter' => Html::activeDropDownList($searchModel, "batch_id", SailorBatchs::getAllBatch(type: Constants::CANDIDATE_DE_SAILOR, type_2: Constants::CANDIDATE_DE_SAILOR_DOCKYARD), ["prompt" => "Select Batch", "class" => "form-control"]),
                                ],
                                // 'batch_config_id',
                                'exam_date',
                                'serial_no',
                                [
                                    'attribute' => 'exam_group',
                                    'value' => function ($data) {
                                        return $data->exam_group ? StaticMethod::sailorGroup($data->exam_group) : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "exam_group", StaticMethod::sailorGroup(), ["prompt" => "Select Group", "class" => "form-control"]),
                                ],

                                [
                                    'attribute' => 'eligible_district',
                                    'value' => function ($data) use ($all_district_session) {
                                        return ($data->eligible_district && array_key_exists($data->eligible_district, $all_district_session)) ? $all_district_session[$data->eligible_district] : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "eligible_district", $all_district_session, ["prompt" => "Select District", "class" => "form-control"]),
                                ],

                                [
                                    'attribute' => 'permanent_district',
                                    'value' => function ($data) use ($all_district_session) {
                                        return ($data->permanent_district && array_key_exists($data->permanent_district, $all_district_session)) ? $all_district_session[$data->permanent_district] : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "permanent_district", $all_district_session, ["prompt" => "Select District", "class" => "form-control"]),
                                ],



                                // 'district',
                                'name',
                                // 'permanent_phone',

                                [
                                    'attribute' => 'permanent_phone',
                                    'value' => function ($data) {
                                        return DataEncryption::dataDecrypt($data->permanent_phone);
                                    },
                                    'filter' => false,
                                ],


                                [
                                    'attribute' => 'gender',
                                    'value' => function ($data) {
                                        return $data->gender ? StaticMethod::gender($data->gender) : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "gender", StaticMethod::gender(), ["prompt" => "Select Gender", "class" => "form-control"]),
                                ],                                                              

                                [
                                    'attribute' => 'payment_status',
                                    'value' => function ($data) {
                                        return $data->payment_status ? StaticMethod::paymentStatus($data->payment_status) : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "payment_status", StaticMethod::paymentStatus(), ["prompt" => "Select ", "class" => "form-control"]),
                                ],

                                [
                                    'attribute' => 'application_status',
                                    'value' => function ($data) {
                                        return $data->application_status ? StaticMethod::isCanselApplication($data->application_status) : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "application_status", StaticMethod::isCanselApplication(), ["prompt" => "Select ", "class" => "form-control"]),
                                ],

                                [
                                    'class' => ActionColumn::className(),
                                    'header' => 'Action',
                                    'headerOptions' => ['width' => '150'],                              
                                    'template' => '{update}&nbsp; {download-form}',

                                    'buttons' => [
                                        'download-form' => function ($url, $model) {
                                            if ($model->serial_no) {
                                                $url_custom = Yii::getAlias('@baseUrl') . '/de-sailor/download-form?slug=' . StaticMethod::encryptPk($model->id);
                                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                            <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                          </svg>';
                                                return Html::a($svg,  $url_custom, ['class' => '', 'target' => '_blank', 'data-pjax' => '0', 'title' => 'Download Form']);
                                            } else
                                                return '';
                                        },                                      

                                    ],
                                    'urlCreator' => function ($action, DeSailors $model, $key, $index, $column) {
                                        return Url::toRoute([$action, 'id' => $model->id]);
                                    }
                                ],
                            ],
                        ]); ?>

                        <?php Pjax::end(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
    var tableContainer = $(".table-responsive");
    var table = $(".table-responsive table");
    var fakeContainer = $(".fake-scrollbar");
    var fakeDiv = $(".fake-scrollbar div");



    var tableWidth = table.width();
    fakeDiv.width(tableWidth);

    fakeContainer.scroll(function() {
        tableContainer.scrollLeft(fakeContainer.scrollLeft());
    });
    tableContainer.scroll(function() {
        fakeContainer.scrollLeft(tableContainer.scrollLeft());
    });
</script>