<?php

use common\models\CanDesignation;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var backend\models\CanDesignationSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Candidate Designations');

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
                            <?= Html::a(Yii::t('app', '<i class="mdi mdi-plus-box"> </i> Add'), ['create'], ['class' => 'btn btn-success']) ?>
                        </h1>
                    </div>
                </div>
                <div class="table-responsive">
                    <?php
                    // Pjax::begin();
                    echo GridView::widget([
                        'dataProvider' => $dataProvider,
                        'filterModel' => $searchModel,
                        'pager' => [                          
                            'prevPageLabel' => 'previous',
                            'nextPageLabel' => 'next',
                            'maxButtonCount' => 20,                     
                            // Customzing CSS class for pager link
                            'linkOptions' => ['class' => 'page-link'],
                            'activePageCssClass' => 'active',
                            'disabledPageCssClass' => 'disabled',                    
                            // Customzing CSS class for navigating link
                            'prevPageCssClass' => 'page-item'                          
                        ],
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
                            'name_en',
                            // 'name_bn',
                            //'created_by',
                            //'updated_by',
                            //'created_dt',
                            //'updated_dt',
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
                                'urlCreator' => function ($action, CanDesignation $model, $key, $index, $column) {
                                    return Url::toRoute([$action, 'id' => $model->id]);
                                }
                            ],
                        ],
                    ]);
                    // Pjax::end();
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>