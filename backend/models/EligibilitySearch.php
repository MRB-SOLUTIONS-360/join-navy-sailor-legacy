<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Eligibility;

/**
 * EligibilitySearch represents the model behind the search form of `common\models\Eligibility`.
 */
class EligibilitySearch extends Eligibility
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'candidate_type', 'candidate_designation', 'jsc_result', 'is_required_biology', 'is_allow_trade_course', 'is_allow_diploma', 'is_allow_hons_appeared', 'is_allow_masters_appeared', 'created_by', 'updated_by', 'status'], 'integer'],
            [['min_age', 'max_age', 'dept_can_max_age', 'marital_status', 'gender', 'height_male', 'weight_male', 'height_female', 'weight_female', 'chest_normal_male', 'chest_extended_male', 'chest_normal_female', 'chest_extended_female', 'ssc_result', 'ssc_ac_group', 'hsc_result', 'hsc_ac_group', 'diploma_result', 'hons_result', 'masters_result', 'masters_subject', 'hons_diploma_subject', 'created_dt', 'updated_dt'], 'safe'],
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
        $query = Eligibility::find();

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
            'candidate_designation' => $this->candidate_designation,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
            'dept_can_max_age' => $this->dept_can_max_age,
            'jsc_result' => $this->jsc_result,
            'is_required_biology' => $this->is_required_biology,
            'is_allow_trade_course' => $this->is_allow_trade_course,
            'is_allow_diploma' => $this->is_allow_diploma,
            'is_allow_hons_appeared' => $this->is_allow_hons_appeared,
            'is_allow_masters_appeared' => $this->is_allow_masters_appeared,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_dt' => $this->created_dt,
            'updated_dt' => $this->updated_dt,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'marital_status', $this->marital_status])
            ->andFilterWhere(['like', 'gender', $this->gender])
            ->andFilterWhere(['like', 'height_male', $this->height_male])
            ->andFilterWhere(['like', 'weight_male', $this->weight_male])
            ->andFilterWhere(['like', 'height_female', $this->height_female])
            ->andFilterWhere(['like', 'weight_female', $this->weight_female])
            ->andFilterWhere(['like', 'chest_normal_male', $this->chest_normal_male])
            ->andFilterWhere(['like', 'chest_extended_male', $this->chest_extended_male])
            ->andFilterWhere(['like', 'chest_normal_female', $this->chest_normal_female])
            ->andFilterWhere(['like', 'chest_extended_female', $this->chest_extended_female])
            ->andFilterWhere(['like', 'ssc_result', $this->ssc_result])
            ->andFilterWhere(['like', 'ssc_ac_group', $this->ssc_ac_group])
            ->andFilterWhere(['like', 'hsc_result', $this->hsc_result])
            ->andFilterWhere(['like', 'hsc_ac_group', $this->hsc_ac_group])
            ->andFilterWhere(['like', 'diploma_result', $this->diploma_result])
            ->andFilterWhere(['like', 'hons_result', $this->hons_result])
            ->andFilterWhere(['like', 'masters_result', $this->masters_result])
            ->andFilterWhere(['like', 'masters_subject', $this->masters_subject])
            ->andFilterWhere(['like', 'hons_diploma_subject', $this->hons_diploma_subject]);

        return $dataProvider;
    }
}
