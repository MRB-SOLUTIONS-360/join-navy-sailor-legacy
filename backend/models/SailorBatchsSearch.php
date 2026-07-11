<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SailorBatchs;

/**
 * SailorBatchsSearch represents the model behind the search form of `common\models\SailorBatchs`.
 */
class SailorBatchsSearch extends SailorBatchs
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'candidate_type', 'is_active_batch', 'allow_application_after_close', 'is_batch_live_mode', 'created_by', 'updated_by', 'status'], 'integer'],
            [['name_en', 'name_bn', 'description', 'circular_date', 'circular_close_date', 'roll_from', 'start_roll', 'secrate_key', 'created_dt', 'updated_dt'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SailorBatchs::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'candidate_type' => $this->candidate_type,
            'circular_date' => $this->circular_date,
            'circular_close_date' => $this->circular_close_date,
            'is_active_batch' => $this->is_active_batch,
            'allow_application_after_close' => $this->allow_application_after_close,
            'is_batch_live_mode' => $this->is_batch_live_mode,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_dt' => $this->created_dt,
            'updated_dt' => $this->updated_dt,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name_en', $this->name_en])
            ->andFilterWhere(['like', 'name_bn', $this->name_bn])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'roll_from', $this->roll_from])
            ->andFilterWhere(['like', 'start_roll', $this->start_roll])
            ->andFilterWhere(['like', 'secrate_key', $this->secrate_key]);

        return $dataProvider;
    }
}
