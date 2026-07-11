<?php

namespace common\models;

use common\static\Constants;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "{{%sailor_cent_dist_mapping}}".
 *
 * @property int $id
 * @property int $candidate_type
 * @property int $center_id
 * @property string $district_slug district slug from district tbl
 * @property int $created_by
 * @property int|null $updated_by
 * @property string|null $created_dt
 * @property string|null $updated_dt
 * @property int $status 1=>Active, 2=>Inactive
 *
 * @property SailorCenters $center
 * @property User $createdBy
 * @property User $updatedBy
 */
class SailorCentDistMapping extends \yii\db\ActiveRecord
{
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%sailor_cent_dist_mapping}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['center_id', 'district_slug','candidate_type'], 'required'],
            [['center_id', 'created_by', 'updated_by', 'status','candidate_type'], 'integer'],
            [['created_dt', 'updated_dt','district_slug'], 'safe'],
            // [['district_slug'], 'string', 'max' => 255],
            [['center_id'], 'exist', 'skipOnError' => true, 'targetClass' => SailorCenters::class, 'targetAttribute' => ['center_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'center_id' => Yii::t('app', 'Center'),
            'candidate_type' => Yii::t('app', 'Sailor Type'),
            'district_slug' => Yii::t('app', 'District List'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'created_dt' => Yii::t('app', 'Created Dt'),
            'updated_dt' => Yii::t('app', 'Updated Dt'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * Gets query for [[Center]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCenter()
    {
        return $this->hasOne(SailorCenters::class, ['id' => 'center_id']);
    }

    /**
     * Gets query for [[CreatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * Gets query for [[UpdatedBy]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }


    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert)
    {

        if ($this->district_slug && is_array($this->district_slug)) {
            $implode  = implode(',', $this->district_slug);
            $this->district_slug = $implode;
        }

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
     * All mapping district.All active designation for dropdown
     * @param type array      
     */
    public static function getAllActiveMappingDistrict(int $isActive = Constants::STATUS_ACTIVE)
    {
        $model = self::find()
            ->where('status =:status', [':status' => $isActive])
            ->orderBy('center_id ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'center_id');
    }


    /**
     * All mapping district in system for dropdown filter 
     * @param type array      
     */
    public static function getAllMappingDistrict()
    {
        $model = self::find()
            ->orderBy('center_id ASC')
            ->all();
        return  ArrayHelper::map($model, 'id', 'center_id');
    }


    // All mapping district in system for dropdown filter 
    public static function GetAllAssignedDistrictByCenter($center_id)
    {
        $model = SailorCentDistMapping::find()->select(['id', 'district_slug'])
        ->where(['center_id'=>$center_id])
        ->all();

        $districts = [];
        foreach($model as $key=>$value){
            $dist  = explode(',',$value['district_slug']);
           $districts= array_merge($districts, $dist);
        }
        // Remove duplicate district slugs
        $districts = array_unique($districts);
        $districts= Districts::find()->select(['slug', 'name_en'])->where(['in','slug',$districts ])->all();
        $modelMap = ArrayHelper::map($districts, 'slug', 'name_en'); 
        return $modelMap;
    }
}
