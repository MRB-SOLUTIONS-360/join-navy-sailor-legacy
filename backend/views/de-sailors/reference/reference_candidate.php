<?php

use common\models\CanDesignation;
use common\models\Districts;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\models\Sailors;
use common\static\Constants;
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

$this->title = Yii::t('app', 'Reference Candidate');
?>
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
            <li class="breadcrumb-item"><a href="#"> Sailor</a></li>
            <li class="breadcrumb-item active" aria-current="page">Reference Candidate</li>
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
                            <?php
                            $pdf = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-pdf" viewBox="0 0 16 16">
                                    <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                    <path d="M4.603 12.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.701 19.701 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.187-.012.395-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.065.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.716 5.716 0 0 1-.911-.95 11.642 11.642 0 0 0-1.997.406 11.311 11.311 0 0 1-1.021 1.51c-.29.35-.608.655-.926.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.27.27 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.647 12.647 0 0 1 1.01-.193 11.666 11.666 0 0 1-.51-.858 20.741 20.741 0 0 1-.5 1.05zm2.446.45c.15.162.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.881 3.881 0 0 0-.612-.053zM8.078 5.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                                  </svg>';
                            $excel = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-excel" viewBox="0 0 16 16">
                                  <path d="M5.18 4.616a.5.5 0 0 1 .704.064L8 7.219l2.116-2.54a.5.5 0 1 1 .768.641L8.651 8l2.233 2.68a.5.5 0 0 1-.768.64L8 8.781l-2.116 2.54a.5.5 0 0 1-.768-.641L7.349 8 5.116 5.32a.5.5 0 0 1 .064-.704z"/>
                                  <path d="M4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4zm0 1h8a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1z"/>
                                </svg>';
                            echo Html::a($pdf . ' PDF', ['/de-sailor-report/reference-de-candidate-pdf'], ['class' => 'btn', 'target' => '_blank', 'style' => 'color: white;background-color: rebeccapurple; font-weight: bold;']) ?>
                            <?php //  Html::a($excel . ' Excel', ['/report/reference-candidate-excel'], ['class' => 'btn', 'style' => 'color: white;background-color: rebeccapurple; font-weight: bold;']) 
                            ?>
                            <?php // Html::a('Submit', '#', ['class' => 'btn btn-success']) 
                            ?>
                            <?= Html::a(Yii::t('app', '<i class="mdi mdi-plus-box"> </i> Add'), ['add-reference-candidate'], ['class' => 'btn btn-success']) ?>
                        </h1>
                    </div>
                </div>

                <div class="fake-scrollbar" style="position: sticky; top: 46px;">
                    <div>&nbsp;</div>
                </div>
                <div class="table-responsive">
                    <?php Pjax::begin(); ?>
                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            ['class' => 'yii\grid\SerialColumn'],
                            'app_unique_id',
                            [
                                'attribute' => 'candidate_type',
                                'value' => function ($data) {
                                    return ($data->candidate_type == Constants::CANDIDATE_DE_SAILOR) ? 'Artificer' : 'Dockyard';
                                },
                                'filter' => Html::activeDropDownList($searchModel, "candidate_type", [Constants::CANDIDATE_DE_SAILOR => 'Direct Entry Artificer', Constants::CANDIDATE_DE_SAILOR_DOCKYARD => 'Direct Entry Dockyard'], ["prompt" => "Select Type", "class" => "form-control"]),
                            ],
                            [
                                'attribute' => 'candidate_designation',
                                'value' => function ($data) {
                                    return CanDesignation::getAllDesignationSession($data->candidate_designation);
                                },
                                //'filter' => Html::activeDropDownList($searchModel, "candidate_designation", CanDesignation::getAllDesignation(type: Constants::CANDIDATE_DE_SAILOR, type_two: Constants::CANDIDATE_DE_SAILOR_DOCKYARD), ["prompt" => "Select Type", "class" => "form-control"]),
                                'filter' => Html::activeDropDownList($searchModel, "candidate_designation", $searchModel->candidate_type ? CanDesignation::getAllDesignation(type: intval($searchModel->candidate_type)) : [], ["prompt" => "Select Type", "class" => "form-control"]),
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
                                'value' => function ($data) {
                                    return $data->eligible_district;
                                },
                                'filter' => Html::activeDropDownList($searchModel, "eligible_district", Districts::getAllDistrict(), ["prompt" => "Select District", "class" => "form-control"]),
                            ],
                            'name',
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
                                'attribute' => 'marital_status',
                                'value' => function ($data) {
                                    return $data->marital_status ? StaticMethod::maritalStatus($data->marital_status) : '';
                                },
                                'filter' => Html::activeDropDownList($searchModel, "marital_status", StaticMethod::maritalStatus(), ["prompt" => "Select", "class" => "form-control"]),
                            ],

                            [
                                'class' => ActionColumn::className(),
                                'header' => 'Action',
                                'headerOptions' => ['width' => '80'],
                                'template' => '{update}',
                                'buttons' => [
                                    'update' => function ($url, $model) {
                                        $icon  = '<svg aria-hidden="true" style="display:inline-block;font-size:inherit;height:1em;overflow:visible;vertical-align:-.125em;width:1em" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M498 142l-46 46c-5 5-13 5-17 0L324 77c-5-5-5-12 0-17l46-46c19-19 49-19 68 0l60 60c19 19 19 49 0 68zm-214-42L22 362 0 484c-3 16 12 30 28 28l122-22 262-262c5-5 5-13 0-17L301 100c-4-5-12-5-17 0zM124 340c-5-6-5-14 0-20l154-154c6-5 14-5 20 0s5 14 0 20L144 340c-6 5-14 5-20 0zm-36 84h48v36l-64 12-32-31 12-65h36v48z"></path></svg>';
                                        return Html::a($icon, ['de-sailors/reference-candidate-update', 'id' => $model->id], [
                                            'title' => Yii::t('app', 'Update Reference'),
                                            'class' => '',
                                            'data-pjax' => '0',
                                        ]);
                                    },
                                ],
                            ],
                        ],
                    ]); ?>

                    <?php Pjax::end(); ?>
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