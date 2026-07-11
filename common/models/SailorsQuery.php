<?php

namespace common\models;

use common\static\Constants;

/**
 * This is the ActiveQuery class for [[Sailors]].
 *
 * @see Sailors
 */
class SailorsQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Sailors[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Sailors|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }


     /**
     * Scope Query for status active 
     */
    public function activeApplication()
    {
        return $this->andFilterWhere(['application_status' => Constants::STATUS_ACTIVE]);
    }

     /**
     * Scope Query for status active 
     */
    public function cancelApplication()
    {
        return $this->andFilterWhere(['application_status' => Constants::STATUS_INACTIVE]);
    }
}
