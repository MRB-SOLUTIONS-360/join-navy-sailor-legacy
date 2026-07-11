<?php

namespace common\models;

use common\static\Constants;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%districts}}".
 *
 * @property int $id
 * @property int $division
 * @property string|null $slug
 * @property string $name_en
 * @property string|null $name_bn
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int $status 1=>Active, 2=>Inactive
 */
class Districts extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%districts}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'slug' => [
                'class' => 'skeeks\yii2\slug\SlugBehavior',
                'slugAttribute' => 'slug',                      //The attribute to be generated
                'attribute' => 'name_en',                          //The attribute from which will be generated
                // optional params
                'maxLength' => 20,                              //Maximum length of attribute slug
                'minLength' => 5,                               //Min length of attribute slug
                'ensureUnique' => true,
                'slugifyOptions' => [
                    'lowercase' => true,
                    'separator' => '-',
                    'trim' => true
                    //'regexp' => '/([^A-Za-z0-9]|-)+/',
                    //'rulesets' => ['russian'],
                    //@see all options https://github.com/cocur/slugify
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name_en', 'division'], 'required'],
            [['slug', 'name_en'], 'unique'],
            [['name_en', 'name_bn'], 'filter', 'filter' => 'strip_tags'],
            // ['name_en', 'filter', 'filter' => 'htmlspecialchars'],
            [['created_by', 'updated_by', 'status'], 'integer'],
            [['created_dt', 'updated_dt'], 'safe'],
            [['slug'], 'string', 'max' => 200],
            [['name_en', 'name_bn'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'division' => Yii::t('app', 'Division'),
            'slug' => Yii::t('app', 'Slug'),
            'name_en' => Yii::t('app', 'Name'),
            'name_bn' => Yii::t('app', 'Name Bangla'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Dt'),
            'updated_dt' => Yii::t('app', 'Updated Dt'),
            'status' => Yii::t('app', 'Status'),
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if ($this->isNewRecord) {
                $this->created_by = (isset(Yii::$app->user->identity->id)) ? Yii::$app->user->identity->id : 1;
                $this->created_dt = date('Y-m-d H:i:s');
            } else {
                $this->updated_by = (isset(Yii::$app->user->identity->id)) ? Yii::$app->user->identity->id : 2;
                $this->updated_dt = date('Y-m-d H:i:s');
            }
            return true;
        } else return false;
    }

    /**
     * All active District.All active designation for dropdown
     * @param type array      
     */
    public static function getAllActiveDistrict(int $isActive = Constants::STATUS_ACTIVE)
    {
        $model = self::find()
            ->where('status =:status', [':status' => $isActive])
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'slug', 'name_en');
    }
    public static function getDistrictsList(int $isActive = Constants::STATUS_ACTIVE)
    {
        $model = self::find()
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'name_en');
    }

    /**
     * find district by slug 
     * @param type array      
     */
    public static function getAllActiveDistrictBySlug(string $slug = null, int $isActive = Constants::STATUS_ACTIVE)
    {
        $model = self::find()
            ->where('status =:status', [':status' => $isActive])
            ->andFilterWhere(['slug' => $slug])
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'slug', 'name_en');
    }


    /**
     * All District in system for dropdown filter 
     * @param type array      
     */
    public static function getAllDistrict()
    {
        $model = self::find()
            ->orderBy('name_en ASC')
            ->all();
        return  ArrayHelper::map($model, 'slug', 'name_en');
    }

    /**
     * District Slug String. by explode return district name array
     * @param type array      
     */
    public static function getDistrictBySlug(string $district_slug)
    {
        $explode_slug = explode(',', $district_slug);
        $model = self::find()
            ->select(['name_en'])
            ->where(['in', 'slug', $explode_slug])
            ->orderBy('name_en ASC')
            ->asArray()
            ->all();
        $return = [];
        if ($model) :
            foreach ($model as $k => $v)
                $return[] = $v['name_en'];
        endif;
        return implode(', ', $return);
    }

    /**
     * District Slug String. by explode return district name array
     * @param type array      
     */
    public static function districtSessionSlug(string $district_slug)
    {

        $session = Yii::$app->session;
        if ($session->has('districtSession')) {
            $district = $session->get('districtSession');
            if (array_key_exists($district_slug, $district)) {
                return $district[$district_slug];
            }
        }
        $model = self::find()
            ->select(['name_en', 'slug'])
            ->orderBy('name_en ASC')
            ->asArray()
            ->all();
        $district = ArrayHelper::map($model, 'slug', 'name_en');
        $session->set('districtSession', $district);
        // return $district[$district_slug];

        if (array_key_exists($district_slug, $district)) {
                return $district[$district_slug];
            }
            return '';
    }


    public static function findOneBySlug(string $district_slug)
    {

        $name = '';
        $model = self::find()
            ->select(['name_en', 'slug'])
            ->where(['slug' => $district_slug])
            ->asArray()
            ->one();
        if ($model)
            $name = $model['name_en'];

        return $name;
    }
    public static function getIdBySlug(string $district_slug)
    {
        $model = self::find()
            ->select(['id', 'slug'])
            ->where(['slug' => $district_slug])
            ->asArray()
            ->one();

        return $model;
    }
}
