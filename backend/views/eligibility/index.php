<?php

use common\models\CanDesignation;
use common\models\Eligibility;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var backend\models\EligibilitySearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Eligibilities');

?>


<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Candidate Designation</li>
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


                    Pjax::begin();
                    echo  GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                       'layout' => "{summary}\n{items}",
                    //    'summary'      => "Total <b>{totalCount}</b> records found. Showing <b>{begin}</b> - <b>{end}</b>.",
                        
                        'columns' => [
                            [
                                'class' => 'yii\grid\SerialColumn',
                                'headerOptions' => ['width' => '80'],
                            ],

                            [
                                'attribute' => 'candidate_type',
                                'value' => function ($data) {
                                    return StaticMethod::candidateType($data->candidate_type);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "candidate_type", StaticMethod::candidateType(), ["prompt" => "Select", "class" => "form-control"]),
                            ],

                            [
                                'attribute' => 'candidate_designation',
                                'value'=>function($data){                                    
                                    //return $data?->createdBy?->username;
                                    return $data?->canDesignation?->name_en;
                                },
                                'filter' => Html::activeDropDownList($searchModel, "candidate_designation", CanDesignation::getAllDesignation(), ["prompt" => "Select", "class" => "form-control"]),
                            ],
                            'min_age',
                            'max_age',
                            // [
                            //     'attribute' => 'max_age',
                            //     'format' => 'raw',
                            //     'value' => function ($data) {
                            //         return $data->max_age;
                            //         //. '<br/> <span style="color:red">' . StaticMethod::getDifferenceBetweenTwoDate(maxDate: $data->max_age, minDate: $data->min_age) . '</span>';
                            //     },
                            // ],
                            // [
                            //     'attribute' => 'dept_can_max_age',
                            //     'format' => 'raw',
                            //     'value' => function ($data) {
                            //         $mx_age  = ($data->dept_can_max_age) ? $data->dept_can_max_age : $data->max_ag;
                            //         return $data->dept_can_max_age . '<br/> <span style="color:red">' . StaticMethod::getDifferenceBetweenTwoDate(maxDate: $mx_age, minDate: $data->min_age) . '</span>';
                            //     },
                            // ],

                            //'dept_can_max_age',
                            //'marital_status',
                            //'gender',
                            //'height_male',
                            //'weight_male',
                            //'height_female',
                            //'weight_female',
                            //'chest_normal_male',
                            //'chest_extended_male',
                            //'chest_normal_female',
                            //'chest_extended_female',
                            //'jsc_result',
                            'ssc_result',
                            'ssc_ac_group',
                           // 'hsc_result',
                           // 'hsc_ac_group',
                            //'is_required_biology',
                            //'is_allow_trade_course',
                           /// 'is_allow_diploma',
                            //'diploma_result',
                            //'is_allow_hons_appeared',
                            //'hons_result',
                            //'is_allow_masters_appeared',
                            //'masters_result',
                            //'masters_subject',
                            //'hons_diploma_subject',
                            //'created_by',
                            //'updated_by',
                            //'created_dt',
                            //'updated_dt',
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
                                'urlCreator' => function ($action, Eligibility $model, $key, $index, $column) {
                                    return Url::toRoute([$action, 'id' => $model->id]);
                                }
                            ],
                        ],
                    ]);
                    Pjax::end();
                    ?>

                    
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
</div>