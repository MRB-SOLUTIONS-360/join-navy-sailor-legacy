<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SailorBatchConfiguration;

/**
 * SailorBatchConfigurationSearch represents the model behind the search form of `common\models\SailorBatchConfiguration`.
 */
class SailorBatchConfigurationSearch extends SailorBatchConfiguration
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'batch_id', 'center_id', 'candidate_type', 'exam_group', 'roll_swap_in_group', 'created_by', 'updated_by', 'status','team'], 'integer'],
            [['gender', 'candidate_designation', 'district_slug', 'exam_date', 'group_start_roll', 'group_end_roll', 'group_no_of_candidate', 'created_dt', 'updated_dt'], 'safe'],
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
        $query = SailorBatchConfiguration::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 70,
            ],
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
            'batch_id' => $this->batch_id,
            'center_id' => $this->center_id,
            'candidate_type' => $this->candidate_type,
            'exam_date' => $this->exam_date,
            'exam_group' => $this->exam_group,
            'team' => $this->team,
            'roll_swap_in_group' => $this->roll_swap_in_group,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_dt' => $this->created_dt,
            'updated_dt' => $this->updated_dt,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'candidate_designation', $this->candidate_designation])
            ->andFilterWhere(['like', 'district_slug', $this->district_slug])
            ->andFilterWhere(['like', 'group_start_roll', $this->group_start_roll])
            ->andFilterWhere(['like', 'group_end_roll', $this->group_end_roll])
            ->andFilterWhere(['like', 'group_no_of_candidate', $this->group_no_of_candidate]);

        return $dataProvider;
    }
}
