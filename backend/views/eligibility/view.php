<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Eligibility $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Eligibilities'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="eligibility-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a(Yii::t('app', 'Delete'), ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'candidate_type',
            'candidate_designation',
            'min_age',
            'max_age',
            'dept_can_max_age',
            'marital_status',
            'gender',
            'height_male',
            'weight_male',
            'height_female',
            'weight_female',
            'chest_normal_male',
            'chest_extended_male',
            'chest_normal_female',
            'chest_extended_female',
            'jsc_result',
            'ssc_result',
            'ssc_ac_group',
            'hsc_result',
            'hsc_ac_group',
            'is_required_biology',
            'is_allow_trade_course',
            'is_allow_diploma',
            'diploma_result',
            'is_allow_hons_appeared',
            'hons_result',
            'is_allow_masters_appeared',
            'masters_result',
            'masters_subject',
            'hons_diploma_subject',
            'created_by',
            'updated_by',
            'created_dt',
            'updated_dt',
            'status',
        ],
    ]) ?>

</div>
