<?php

namespace common\models\scopeQuery;

use common\static\Constants;
use yii\db\ActiveQuery;

class SailorBatchs extends ActiveQuery
{
    /**
     * Scope Query for status active 
     */
    public function isActive()
    {
        return $this->andFilterWhere(['status' => Constants::STATUS_ACTIVE]);
    }

    /**
     * Scope Query for active bacth
     */
    public function isActiveBatch()
    {
        return $this->andFilterWhere(['is_active_batch' => Constants::STATUS_ACTIVE]);
    }

    /**
     * Scope Query for active bacth
     */
    public function isCircularCloseDateGraterCurrentDate()
    {

        return $this->andWhere([
            'and',
            ['>=', 'circular_close_date', date('Y-m-d H:i')],
            ['<=', 'circular_start_date', date('Y-m-d H:i')]
        ]);
        // return $this->andFilterWhere(['>=', 'circular_close_date', date('Y-m-d')]);
    }
}
