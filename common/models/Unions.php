<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%unions}}".
 *
 * @property int $id
 * @property int|null $upozila_id
 * @property int|null $district_id
 * @property string|null $name
 * @property string|null $bn_name
 * @property string|null $slug
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $status
 */
class Unions extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%unions}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'district_id', 'upozila_id'], 'required'],
            [['upozila_id', 'district_id', 'name', 'bn_name', 'slug', 'created_at', 'updated_at', 'created_by', 'updated_by', 'status'], 'default', 'value' => null],
            [['upozila_id', 'district_id', 'created_by', 'updated_by', 'status'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['name', 'bn_name', 'slug'], 'string', 'max' => 250],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'upozila_id' => 'Upozila',
            'district_id' => 'District',
            'name' => 'Name',
            'bn_name' => 'BN Name',
            'slug' => 'Slug',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'created_by' => 'Created By',
            'updated_by' => 'Updated By',
            'status' => 'Status',
        ];
    }


    public static function getUnionsListForCandidate(int $upozila_id = null)
    {
        $model = self::find()
            ->where(['status' => 1])
            ->andFilterWhere(['upozila_id' => $upozila_id])
            ->orderBy('name ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'name');
    }

    public static function unionNameById(int $id)
    {
        $model = self::find()->select(['id', 'name'])
            ->where(['id' => $id])->asArray()->one();
        return  $model['name'] ?? '';
    }
}
