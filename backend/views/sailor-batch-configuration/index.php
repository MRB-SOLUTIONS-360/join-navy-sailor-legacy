<?php

use common\models\CanDesignation;
use common\models\Districts;
use common\models\SailorBatchConfiguration;
use common\models\SailorBatchs;
use common\models\SailorCenters;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var backend\models\SailorBatchConfigurationSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Sailor Batch Configurations');
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Batch Configurations</li>
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
                            <?= Html::a(Yii::t('app', '<i class="mdi mdi-plus-box"> </i> Add '), ['create'], ['class' => 'btn btn-success']) ?>
                        </h1>
                    </div>
                </div>
                <div class="table-responsive">
                    <?php
                     $this->registerCss("
                     .table-responsive {
                         max-height: 600px; /* scroll height */
                         overflow-y: auto;
                     }
                     .grid-view table thead th {
                         position: sticky;
                         top: 0;
                         z-index: 20;
                         background: #fff !important;       /* solid background (white) */
                         box-shadow: 0 2px 2px rgba(0,0,0,0.05); /* subtle shadow */
                     }
                 ");


                    //Pjax::begin();
                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'headerOptions' => ['width' => '80'],
                            ],
                            [
                                'attribute' => 'batch_id',
                                'value' => function ($data) {
                                    return  $data->batch->name_en;
                                },
                                'filter' => Html::activeDropDownList($searchModel, "batch_id", SailorBatchs::getAllBatch(), ["prompt" => "Select", "class" => "form-control"]),
                            ],
                            [
                                'attribute' => 'center_id',
                                'value' => function ($data) {
                                    return  SailorCenters::getAllCenterSession($data->center_id);   // $data->center->name_en;
                                },
                                'filter' => Html::activeDropDownList($searchModel, "center_id", SailorCenters::getAllCenter(), ["prompt" => "Select", "class" => "form-control"]),
                            ],
                            [
                                'attribute' => 'team',
                                'value' => function ($data) {
                                    return $data->team ? StaticMethod::team($data->team) : '';   // $data->center->name_en;
                                },
                                'filter' => Html::activeDropDownList($searchModel, "team", StaticMethod::team(), ["prompt" => "Select", "class" => "form-control"]),
                            ],
                            [
                                'attribute' => 'candidate_type',
                                'value' => function ($data) {
                                    return StaticMethod::candidateType($data->candidate_type);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "candidate_type", StaticMethod::candidateType(), ["prompt" => "Select", "class" => "form-control"]),
                            ],
                            [
                                'attribute' => 'gender',
                                'value' => function ($data) {
                                    $gender = [];
                                    foreach (explode(',', $data->gender) as $g)
                                        $gender[] = StaticMethod::gender(intval($g));
                                    return implode(', ', $gender);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "gender", StaticMethod::gender(), ["prompt" => "Select", "class" => "form-control"]),
                            ],                         
                            [
                                'attribute' => 'marital_status',
                                'value' => function ($data) {
                                    $gender = [];
                                    if($data->marital_status){
                                        foreach (explode(',', $data->marital_status) as $g)
                                        $gender[] =  StaticMethod::maritalStatus(intval($g)) ;
                                    }
                                   
                                    return implode(', ', $gender);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "marital_status", StaticMethod::maritalStatus(), ["prompt" => "Select", "class" => "form-control"]),
                            ],                         
                            
                            [
                                'attribute' => 'candidate_designation',
                                'format'=>'html',
                       
                               
                                'value' => function ($data) {
                                    $designation = [];
                                    if($data->candidate_designation){
                                        foreach (explode(',', $data->candidate_designation) as $g)
                                            $designation[] = CanDesignation::getAllDesignationSession($g);
                                    }
                                    return implode(', ', $designation);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "candidate_designation", CanDesignation::getAllDesignation(), ["prompt" => "Select", "class" => "form-control"]),
                            ],


                            [
                                'attribute' => 'district_slug',
                                'format'=>'html',                                
                                'headerOptions'  => ['style' => 'width:200px;'], 
                                'options' => ['style' => 'table-layout: fixed; width:100%;'],                                
                                'value' => function ($data) {                                 
                                    $district = [];
                                    foreach (explode(',', $data->district_slug) as $g)
                                        $district[] = Districts::districtSessionSlug($g);
                                    return implode(', ', $district);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "district_slug", Districts::getAllDistrict(), ["prompt" => "Select", "class" => "form-control"]),
                            ],
                            'exam_date',
                            [
                                'attribute' => 'exam_group',
                                'value' => function ($data) {
                                    return StaticMethod::sailorGroup($data->exam_group);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "exam_group", StaticMethod::sailorGroup(), ["prompt" => "Select", "class" => "form-control"]),
                            ],

                            [
                                'attribute' => 'roll_swap_in_group',
                                'value' => function ($data) {
                                    return $data->roll_swap_in_group 
                                    ? StaticMethod::yesNo($data->roll_swap_in_group) :'';
                                },
                                'filter' => Html::activeDropDownList($searchModel, "roll_swap_in_group", StaticMethod::yesNo(), ["prompt" => "Select", "class" => "form-control"]),
                            ],


                            
                            //'group_start_roll',
                            //'group_end_roll',
                            'group_no_of_candidate',
                            //'created_by',
                            //'updated_by',
                            //'created_dt',
                            //'updated_dt',
                            //'status',
                            [
                                'attribute' => 'status',
                                'value' => function ($data) {
                                    return StaticMethod::statusDropDown($data->status);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "status", StaticMethod::statusDropDown(), ["prompt" => "Select", "class" => "form-control"]),
                            ],
                            [
                                'class' => ActionColumn::className(),
                                'header' => 'Action',
                                'headerOptions' => ['width' => '100'],
                                'template' => '{update}&nbsp; {delete}',
                                'urlCreator' => function ($action, SailorBatchConfiguration $model, $key, $index, $column) {
                                    return Url::toRoute([$action, 'id' => $model->id]);
                                }
                            ],
                        ],
                    ]);
                    //Pjax::end(); 
                    ?>
                </div>
              
            </div>
            <div class="mt-3">
    <?= \yii\widgets\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'prevPageLabel' => 'Previous',
        'nextPageLabel' => 'Next',
        'maxButtonCount' => 20,
        'options' => ['class' => 'pagination justify-content-center'],
        'linkOptions' => ['class' => 'page-link'],
        'activePageCssClass' => 'active',
        'disabledPageCssClass' => 'disabled',
        'prevPageCssClass' => 'page-item',
        'nextPageCssClass' => 'page-item',
    ]) ?>
</div>
        </div>
    </div>
</div>