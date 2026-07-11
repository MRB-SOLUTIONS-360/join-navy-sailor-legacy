<?php

use common\models\Districts;
use common\models\Unions;
use common\models\Upozilas;
use common\static\StaticMethod;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var common\models\UnionsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Unions';
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light-lighten p-2 mb-0">
                <li class="breadcrumb-item"><a href="#"><i class="uil-home-alt"></i> Home</a></li>
                <li class="breadcrumb-item"><a href="#"> Global Setting</a></li>
                <li class="breadcrumb-item active" aria-current="page">Unions</li>
            </ol>
        </nav>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col col-xl-8">
                            <h1><?= Html::encode($this->title) ?> </h1>
                        </div>
                        <div class="col col-xl-4 text-end">
                            <h1>
                                <?= Html::a(Yii::t('app', '<i class="mdi mdi-plus-box"> </i> Add'), ['create'], ['class' => 'btn btn-success']) ?>
                            </h1>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <?php Pjax::begin();
                        $all_districts =  Districts::getDistrictsList();
                        $all_upozilas =  Upozilas::getUpazilaListAdmin($searchModel->district_id);
                        ?>
                        <?= GridView::widget([
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
                                    'attribute' => 'district_id',
                                    'value' => function ($data) use ($all_districts) {
                                        return $data->district_id && array_key_exists($data->district_id, $all_districts) ? $all_districts[$data->district_id] : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "district_id", $all_districts, ["prompt" => "Select", "class" => "form-control"]),
                                ],
                                [
                                    'attribute' => 'upozila_id',
                                    'value' => function ($data) use ($all_upozilas) {
                                        return $data->upozila_id && array_key_exists($data->upozila_id, $all_upozilas) ? $all_upozilas[$data->upozila_id] : '';
                                    },
                                    'filter' => Html::activeDropDownList($searchModel, "upozila_id", $all_upozilas, ["prompt" => "Select", "class" => "form-control"]),
                                ],


                                'name',
                                'bn_name',
                                //'slug',
                                //'created_at',
                                //'updated_at',
                                //'created_by',
                                //'updated_by',
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
                                    'template' => '{update}',
                                    'urlCreator' => function ($action, Unions $model, $key, $index, $column) {
                                        return Url::toRoute([$action, 'id' => $model->id]);
                                    },
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