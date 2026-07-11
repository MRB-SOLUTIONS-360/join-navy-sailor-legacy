<?php

use common\models\Districts;
use common\models\SailorCentDistMapping;
use common\models\SailorCenters;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var backend\models\SailorCentDistMappingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Center District Mappings');
?>
<div class="row">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-light-lighten p-2 mb-0">
            <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
            <li class="breadcrumb-item active" aria-current="page">Center Dist Mapping </li>
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
                            <?= Html::a(Yii::t('app', '<i class="mdi mdi-plus-box"> </i> Add'), ['create'], ['class' => 'btn btn-success']) ?>
                        </h1>
                    </div>
                </div>
                <div class="table-responsive">
                    <?php
                    Pjax::begin();
                    echo  GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
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
                            // [
                            //     'attribute' => 'center_id',
                            //     'header'=>'Candidate Type',
                            //     'headerOptions' => ['width' => '130px'],
                            //     // 'contentOptions' => ['style' => 'width:200px; white-space: normal;'],
                            //     'value' => function ($data) {
                            //         return  StaticMethod::candidateType($data?->center?->candidate_type);
                            //     },
                            //     'filter' => false,
                            // ],


                            [
                                'attribute' => 'center_id',
                                'headerOptions' => ['width' => '300'],                               
                                'value' => function ($data) {
                                    return  $data?->center?->name_en;
                                },
                                'filter' => Html::activeDropDownList($searchModel, "center_id", SailorCenters::getAllCenter(), ["prompt" => "Select", "class" => "form-control"]),
                            ],
                           

                            [
                                'attribute' => 'district_slug',
                                ///'headerOptions' => ['width' => '300'],
                                // 'contentOptions' => ['style' => 'width:200px; white-space: normal;'],                                
                                'value' => function ($data) {
                                    return  Districts::getDistrictBySlug($data->district_slug);
                                },
                                'filter' => Html::activeDropDownList($searchModel, "district_slug", Districts::getAllDistrict(), ["prompt" => "Select", "class" => "form-control"]),
                            ],



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
                                'headerOptions' => ['width' => '80'],
                                'template' => '{update}&nbsp; {delete}',
                                'urlCreator' => function ($action, SailorCentDistMapping $model, $key, $index, $column) {
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