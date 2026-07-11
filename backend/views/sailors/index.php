<?php

use common\models\CanDesignation;
use common\models\Districts;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\models\Sailors;
use common\static\AES256CTR;
use common\static\Constants;
use common\static\DataEncryption;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\jui\DatePicker;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var common\models\SailorsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Sailors');
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
    /* ================================
   Sticky Header + Filters
   ================================ */
/* Main container with scroll */
.table-responsive.grid-view-sticky-header {
    max-height: calc(100vh - 220px); /* adjust as needed */
    overflow-y: auto;
    overflow-x: auto;
    position: relative;
}

/* Top horizontal scrollbar */
.fake-scrollbar {
    position: sticky !important;
    top: 0 !important;
    height: 10px;
    overflow-x: auto !important;
    overflow-y: hidden !important;
    background: #f8fafc;
    z-index: 40; /* sits above header */
    border-bottom: 1px solid #e9ecef;
}
.fake-scrollbar div { height: 1px; }
 

/* Sticky table header (beneath top scrollbar) */
.table-responsive.grid-view-sticky-header thead tr:first-child th {
    position: sticky;
    top: 0px; /* below the top fake scrollbar */
    background: #f8fafc !important; /* solid, no transparency */
    color: #0f172a;
    z-index: 30; /* above filters and content */
    box-shadow: 0 1px 0 rgba(15,23,42,.06), 0 4px 8px -4px rgba(15,23,42,.12);
    white-space: nowrap;
}

/* Sticky filters row below header */
.table-responsive.grid-view-sticky-header thead tr.filters th {
    position: sticky;
    top: 54px; /* 14px (top scrollbar) + ~40px (header height) */
    background: #f8fafc !important; /* solid, no transparency */
    z-index: 25;
    box-shadow: 0 1px 0 rgba(15,23,42,.05);
}

/* Ensure filter inputs don’t shrink */
.table-responsive.grid-view-sticky-header thead tr.filters input,
.table-responsive.grid-view-sticky-header thead tr.filters select {
    min-width: 100px;
}

/* Sticky summary & pagination */
.grid-view-sticky-header .summary,
.grid-view-sticky-header .pagination-wrapper {
    position: sticky;
    top: 54px; /* 14px (top scrollbar) + ~40px (header height) */
    background: #fff;
    z-index: 25;
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

/* Table base styles */
.table-responsive.grid-view-sticky-header table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}

.table-responsive.grid-view-sticky-header th,
.table-responsive.grid-view-sticky-header td {
    padding: 8px 12px;
    border: 1px solid #dee2e6;
}
/* Sticky table header */  

    
</style>


<script>
    $(document).ready(function () {
        // Your code here
        $('#decodePhone').on('click', function (e) {
            e.preventDefault(); // Prevent default link behavior
            // Add your decoding logic here
            const x = confirm('Are you sure you want to decode phone numbers?');
            if (x) {
                $.ajax({
                    url: "<?= \yii\helpers\Url::to(['ajax/decode-phone']) ?>",
                    type: 'POST',
                    dataType: 'json',    
                    data: {
                        
                    },
                    beforeSend: function() {
                        $('#preloader').css('display', 'flex');
                    },
                    success: function(data) {                         
                        $('#preloader').css('display', 'none');
                        if(data.data == 'success') {
                            alert(data.decode_phone+' Phone numbers decoded successfully');
                            location.reload();
                        }
                    },
                    error: function() {
                        $('#preloader').css('display', 'none');
                        alert('Something went wrong');
                    }
                });
            }
     
        });
    });
</script>


<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Sailor</a></li>
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
                        <h3><?= Html::encode($this->title) ?> 
                        <!-- <a href="javascript:void(0);" id="decodePhone" class="btn btn-primary">Decode Phone</a> -->

                    </h3>
                    </div>
                    <div class="col col-xl-4 text-end">
                        <h1>
                            <?php // Html::a(Yii::t('app', '<i class="mdi mdi-plus-box"> </i> Add'), ['create'], ['class' => 'btn btn-success']) 
                            ?>
                        </h1>
                    </div>
                </div>

                <div id="preloader" style="position: fixed; top: 0; left: 0; inset: 0; z-index: 9999;  background: rgba(0, 0, 0, .5); display: none; justify-content: center; align-items:center">
                <div class="spinner-grow" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
                <div style="position: relative;">
                    
                    <div class="fake-scrollbar">
                        <div>&nbsp;</div>
                    </div>
                    <div class="table-responsive grid-view-sticky-header">

                  

                        <?php Pjax::begin(); ?> 

                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'filterModel' => $searchModel,
                            'layout' => "<div class='d-flex justify-content-between align-items-center mt-2'>
                            {summary}
                         </div>\n{items}\n<div class='d-flex justify-content-end'>{pager}</div>",
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
                                'prevPageCssClass' => 'page-item',
                             
                            ],
                            'columns' => [
                                ['class' => 'yii\grid\SerialColumn'],
                                [
                                    'attribute' => 'app_unique_id',
                                    'format' => 'raw',
                                    'contentOptions' => ['style' => 'max-width:130px;'],
                                    'value' => function ($data) {
                                        if ($data->request_for_cancel ==1 && $data->cancel_application_view == 1) {
                                            return $data->app_unique_id ? $data->app_unique_id . ' <span class="badge bg-danger">Cancel Marked</span>' : '';
                                        } else  
                                        return $data->app_unique_id ? $data->app_unique_id : '';
                                    },
                                ],
                                [
                                    'attribute' => 'candidate_designation',
                                    'value' => function ($data) {
                                        return CanDesignation::getAllDesignationSession($data->candidate_designation);
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "candidate_designation", CanDesignation::getAllDesignation(Constants::CANDIDATE_SAILOR), ["prompt" => "Select Type", "class" => "form-control"]),
                                    'contentOptions' => ['style' => 'max-width:120px;'],
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
                                    'filter' => Html::activeDropDownList($searchModel, "batch_id", SailorBatchs::getAllBatch( Constants::CANDIDATE_SAILOR), ["prompt" => "Select Batch", "class" => "form-control"]),
                                ],
                                // 'batch_config_id',
                                // 'exam_date',
                                [
                                    'attribute' => 'exam_date',                            
                                    'filter' => DatePicker::widget([
                                        'model' => $searchModel,
                                        'attribute' => 'exam_date', 
                                        'dateFormat' =>  'yyyy-MM-dd',
                                        'clientOptions' => [
                                            'changeMonth' => true,
                                            'changeYear' => true,  
                                            'yearRange' => (date('Y') - 3) . ':' . (date('Y') + 3),                                  
                                        ],                                 
                                        'options' => [
                                            'class' => 'form-control custom-date-class',
                                        ],                                   
                                    ]),                               
                                ],
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
                                [
                                    'attribute' => 'name',
                                    'contentOptions' => ['style' => 'max-width:100px;'],
                                ],
                                //'father_name',                              
                                'father_name',
                                //'father_nid',
                                //'father_occupation',
                                //'father_income',
                                //'mother_name',
                                //'mother_occupation',
                                //'current_village',
                                //'current_word_no',
                                //'current_union',
                                //'current_post_office',
                                //'current_thana',
                                //'current_post_code',
                                //'current_district',
                                //'current_phone',
                                //'permanent_village',
                                //'permanent_union',
                                //'permanent_word_no',
                                //'permanent_post_office',
                                //'permanent_thana',
                                //'permanent_district',
                                //'permanent_post_code',
                                // 'permanent_phone',
                                //'guardian_name',
                                //'guardian_relation',
                                //'guardian_occupation',
                                //'guardian_address',
                                //'dob',
                                //'age_according_to_circular',
                                //'religion',
                                // 'permanent_phone_de',
                                [
                                    'attribute' => 'permanent_phone_de',
                                    'value' => function ($data) {
                                        return $data->permanent_phone_de ? AES256CTR::dataDecrypt($data->permanent_phone_de) : '';
                                    },
                                ],
                                [
                                    'attribute' => 'permanent_phone',
                                    'value' => function ($data) {
                                        return $data->permanent_phone ? DataEncryption::dataDecrypt($data->permanent_phone) : '';
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
                                    'contentOptions' => ['style' => 'max-width:100px;'],
                                ],

                                [
                                    'attribute' => 'list_custom_filter',
                                    'label' => 'Custom Filter',
                                    'format' => 'raw',
                                    'value' => function ($data) use ($searchModel) {
                                        if ($searchModel['list_custom_filter']) {
                                            if ($searchModel['list_custom_filter'] == 1) return 'Paid & complete';
                                            else return 'Paid & not complete';
                                        } else return '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "list_custom_filter", [1 => 'Paid & complete', 2 => 'Paid & not complete'], ["prompt" => "Select ", "class" => "form-control"]),
                                ],


                                [
                                    'attribute' => 'application_status',
                                    'value' => function ($data) {
                                        return $data->application_status ? StaticMethod::isCanselApplication($data->application_status) : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "application_status", StaticMethod::isCanselApplication(), ["prompt" => "Select ", "class" => "form-control"]),
                                    'contentOptions' => ['style' => 'max-width:100px;'],
                                ],
                                [
                                    'class' => ActionColumn::className(),
                                    'header' => 'Action',
                                    'headerOptions' => ['width' => '150'],                       
                                    'template' => '{update}&nbsp; {download-form} ',
                                    'buttons' => [
                                        'download-form' => function ($url, $model) {
                                            if ($model->serial_no) {
                                                $url_custom = Yii::getAlias('@baseUrl') . '/sailor-candidate/download-form?slug=' . StaticMethod::encryptPk($model->id);
                                                $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download" viewBox="0 0 16 16">
                                <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                              </svg>';
                                                return Html::a($svg,  $url_custom, ['class' => '', 'target' => '_blank', 'data-pjax' => '0', 'title' => 'Download Form']);
                                            } else
                                                return '';
                                        },
                                    ],
                                    'urlCreator' => function ($action, Sailors $model, $key, $index, $column) {
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

    console.log(tableContainer, table, fakeContainer, fakeDiv)

    var tableWidth = table.width();
    fakeDiv.width(tableWidth);

    fakeContainer.scroll(function() {
        tableContainer.scrollLeft(fakeContainer.scrollLeft());
    });
    tableContainer.scroll(function() {
        fakeContainer.scrollLeft(tableContainer.scrollLeft());
    });
    
    // Toggle elevated header shadow when content is scrolled
    function updateScrolledState() {
        var scroller = $(".table-responsive.grid-view-sticky-header");
        if (scroller.length) {
            scroller.toggleClass('is-scrolled', scroller.scrollTop() > 0);
        }
    }
    updateScrolledState();
    $(".table-responsive.grid-view-sticky-header").on('scroll', updateScrolledState);

    // Recompute widths and bindings after PJAX reloads
    $(document).on('pjax:end', function() {
        tableContainer = $(".table-responsive");
        table = $(".table-responsive table");
        fakeContainer = $(".fake-scrollbar");
        fakeDiv = $(".fake-scrollbar div");

        var tableWidthUpdated = table.width();
        fakeDiv.width(tableWidthUpdated);

        fakeContainer.off('scroll').on('scroll', function() {
            tableContainer.scrollLeft(fakeContainer.scrollLeft());
        });
        tableContainer.off('scroll').on('scroll', function() {
            fakeContainer.scrollLeft(tableContainer.scrollLeft());
        });
        updateScrolledState();
    });
</script>