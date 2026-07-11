<?php

namespace common\models;

use common\static\Constants;

/**
 * This is the ActiveQuery class for [[SailorBatchConfiguration]].
 *
 * @see SailorBatchConfiguration
 */
class SailorBatchConfigurationQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return SailorBatchConfiguration[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return SailorBatchConfiguration|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function isActive()
    {
        return $this->andFilterWhere(['status' => Constants::STATUS_ACTIVE]);
    }
}
