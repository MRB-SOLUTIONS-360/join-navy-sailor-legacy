<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\SailorCenters;

/**
 * SailorCentersSearch represents the model behind the search form of `common\models\SailorCenters`.
 */
class SailorCentersSearch extends SailorCenters
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'candidate_type', 'created_by', 'updated_by', 'status'], 'integer'],
            [['name_en', 'name_bn', 'created_dt', 'updated_dt'], 'safe'],
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
        $query = SailorCenters::find();

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
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_dt' => $this->created_dt,
            'updated_dt' => $this->updated_dt,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name_en', $this->name_en])
            ->andFilterWhere(['like', 'name_bn', $this->name_bn]);

        return $dataProvider;
    }
}
