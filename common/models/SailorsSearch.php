<?php

namespace common\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Sailors;
use common\static\AES256CTR;
use common\static\Constants;

/**
 * SailorsSearch represents the model behind the search form of `common\models\Sailors`.
 */
class SailorsSearch extends Sailors
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'eligibility_info_id', 'is_departmental_candidate', 'candidate_designation', 'exam_group', 'center_id', 'batch_id', 'batch_config_id', 'religion', 'gender', 'marital_status', 'ac_type_ssc', 'ssc_edu_board', 'hsc_or_diploma', 'hsc_dip_board', 'is_freedom_fighter', 'freedom_fighter_relation', 'is_child_of_naval_officer', 'naval_uniform_civil', 'is_anser_vdp', 'is_khudro_jati_gosti', 'phase', 'is_manula_paid', 'payment_status', 'application_status', 'have_reference', 'is_online_manual', 'created_by', 'updated_by', 'list_custom_filter'], 'integer'],
            [['app_unique_id', 'exam_date', 'serial_no', 'eligible_district', 'district', 'name', 'father_name', 'father_nid', 'father_occupation', 'mother_name', 'mother_occupation', 'current_village', 'current_word_no', 'current_union', 'current_post_office', 'current_thana', 'current_post_code', 'current_district', 'current_phone', 'permanent_village', 'permanent_union', 'permanent_word_no', 'permanent_post_office', 'permanent_thana', 'permanent_district', 'permanent_post_code', 'permanent_phone', 'permanent_phone_de', 'guardian_name', 'guardian_relation', 'guardian_occupation', 'guardian_address', 'dob', 'age_according_to_circular', 'nationality', 'photo', 'qr_photo', 'jsc_reg_no', 'jsc_institute_name', 'jsc_passing_year', 'jsc_gpa', 'ssc_institute', 'ssc_group', 'ssc_reg_no', 'ssc_roll_no', 'ssc_passing_year', 'ssc_additional_subject', 'ssc_gpa', 'hsc_dip_institute', 'hsc_dip_group', 'hsc_dip_reg_no', 'hsc_dip_roll_no', 'hsc_dip_passing_year', 'hsc_dip_additional_subject', 'hsc_dip_gpa', 'ssc_edu_data', 'hsc_edu_data', 'ssc_teletalk_data', 'hsc_teletalk_data', 'experience_one_institute', 'experience_one_subject', 'experience_one_year', 'experience_one_cert_name', 'experience_two_institute', 'experience_two_subject', 'experience_two_year', 'experience_two_cert_name', 'experience_three_institute', 'experience_three_subject', 'experience_three_year', 'experience_three_cert_name', 'experience_four_institute', 'experience_four_subject', 'experience_four_year', 'experience_four_cert_name', 'naval_father_name', 'naval_office_no', 'naval_rank', 'navy_ship_etbd_retired', 'anser_vdp_rank', 'anser_vdp_office_no', 'payment_type', 'ref_id', 'validation_id', 'order_id_original', 'card_type', 'card_no', 'trans_date', 'payment_api', 'referred_by', 'reference_details', 'relationship', 'created_dt', 'updated_dt'], 'safe'],
            [['father_income', 'amount', 'store_amount'], 'number'],
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
        $query = Sailors::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 300,
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
            'eligibility_info_id' => $this->eligibility_info_id,
            'candidate_designation' => $this->candidate_designation,
            'center_id' => $this->center_id,
            'batch_id' => $this->batch_id,
            'batch_config_id' => $this->batch_config_id,
            'exam_date' => $this->exam_date,
            'exam_group' => $this->exam_group,
            'father_income' => $this->father_income,
            'dob' => $this->dob,
            'religion' => $this->religion,
            'gender' => $this->gender,
            'marital_status' => $this->marital_status,
            'ac_type_ssc' => $this->ac_type_ssc,
            'ssc_edu_board' => $this->ssc_edu_board,
            'hsc_or_diploma' => $this->hsc_or_diploma,
            'hsc_dip_board' => $this->hsc_dip_board,
            'is_freedom_fighter' => $this->is_freedom_fighter,
            'freedom_fighter_relation' => $this->freedom_fighter_relation,
            'is_child_of_naval_officer' => $this->is_child_of_naval_officer,
            'naval_uniform_civil' => $this->naval_uniform_civil,
            'is_anser_vdp' => $this->is_anser_vdp,
            'is_khudro_jati_gosti' => $this->is_khudro_jati_gosti,
            'phase' => $this->phase,
            'is_manula_paid' => $this->is_manula_paid,
            'amount' => $this->amount,
            'store_amount' => $this->store_amount,
            'trans_date' => $this->trans_date,
            'payment_status' => ($this->payment_status) ? $this->payment_status : Constants::PAYMENT_PAID,
            'application_status' => $this->application_status,
            'have_reference' => $this->have_reference,
            'is_online_manual' => $this->is_online_manual,
            'is_departmental_candidate' => $this->is_departmental_candidate,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_dt' => $this->created_dt,
            'updated_dt' => $this->updated_dt,
        ]);


        if ($this->permanent_phone_de) {
            $phoneFilter = substr(AES256CTR::dataEncrypt($this->permanent_phone_de), 0, -3);
            $query->andFilterWhere(['like', 'permanent_phone_de',  $phoneFilter]);
        }

        $query->andFilterWhere(['like', 'app_unique_id', $this->app_unique_id])
            ->andFilterWhere(['like', 'serial_no', $this->serial_no])
            ->andFilterWhere(['like', 'eligible_district', $this->eligible_district])
            ->andFilterWhere(['like', 'district', $this->district])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'father_name', $this->father_name])
            ->andFilterWhere(['like', 'father_nid', $this->father_nid])
            ->andFilterWhere(['like', 'father_occupation', $this->father_occupation])
            ->andFilterWhere(['like', 'mother_name', $this->mother_name])
            ->andFilterWhere(['like', 'mother_occupation', $this->mother_occupation])
            ->andFilterWhere(['like', 'current_village', $this->current_village])
            ->andFilterWhere(['like', 'current_word_no', $this->current_word_no])
            ->andFilterWhere(['like', 'current_union', $this->current_union])
            ->andFilterWhere(['like', 'current_post_office', $this->current_post_office])
            ->andFilterWhere(['like', 'current_thana', $this->current_thana])
            ->andFilterWhere(['like', 'current_post_code', $this->current_post_code])
            ->andFilterWhere(['like', 'current_district', $this->current_district])
            ->andFilterWhere(['like', 'current_phone', $this->current_phone])
            ->andFilterWhere(['like', 'permanent_village', $this->permanent_village])
            ->andFilterWhere(['like', 'permanent_union', $this->permanent_union])
            ->andFilterWhere(['like', 'permanent_word_no', $this->permanent_word_no])
            ->andFilterWhere(['like', 'permanent_post_office', $this->permanent_post_office])
            ->andFilterWhere(['like', 'permanent_thana', $this->permanent_thana])
            ->andFilterWhere(['like', 'permanent_district', $this->permanent_district])
            ->andFilterWhere(['like', 'permanent_post_code', $this->permanent_post_code])
            // ->andFilterWhere(['like', 'permanent_phone', $this->permanent_phone])
            // ->andFilterWhere(['like', 'permanent_phone_de', $this->permanent_phone_de])
            ->andFilterWhere(['like', 'guardian_name', $this->guardian_name])
            ->andFilterWhere(['like', 'guardian_relation', $this->guardian_relation])
            ->andFilterWhere(['like', 'guardian_occupation', $this->guardian_occupation])
            ->andFilterWhere(['like', 'guardian_address', $this->guardian_address])
            ->andFilterWhere(['like', 'age_according_to_circular', $this->age_according_to_circular])
            ->andFilterWhere(['like', 'nationality', $this->nationality])
            ->andFilterWhere(['like', 'photo', $this->photo])
            ->andFilterWhere(['like', 'qr_photo', $this->qr_photo])
            ->andFilterWhere(['like', 'jsc_reg_no', $this->jsc_reg_no])
            ->andFilterWhere(['like', 'jsc_institute_name', $this->jsc_institute_name])
            ->andFilterWhere(['like', 'jsc_passing_year', $this->jsc_passing_year])
            ->andFilterWhere(['like', 'jsc_gpa', $this->jsc_gpa])
            ->andFilterWhere(['like', 'ssc_institute', $this->ssc_institute])
            ->andFilterWhere(['like', 'ssc_group', $this->ssc_group])
            ->andFilterWhere(['like', 'ssc_reg_no', $this->ssc_reg_no])
            ->andFilterWhere(['like', 'ssc_roll_no', $this->ssc_roll_no])
            ->andFilterWhere(['like', 'ssc_passing_year', $this->ssc_passing_year])
            ->andFilterWhere(['like', 'ssc_additional_subject', $this->ssc_additional_subject])
            ->andFilterWhere(['like', 'ssc_gpa', $this->ssc_gpa])
            ->andFilterWhere(['like', 'hsc_dip_institute', $this->hsc_dip_institute])
            ->andFilterWhere(['like', 'hsc_dip_group', $this->hsc_dip_group])
            ->andFilterWhere(['like', 'hsc_dip_reg_no', $this->hsc_dip_reg_no])
            ->andFilterWhere(['like', 'hsc_dip_roll_no', $this->hsc_dip_roll_no])
            ->andFilterWhere(['like', 'hsc_dip_passing_year', $this->hsc_dip_passing_year])
            ->andFilterWhere(['like', 'hsc_dip_additional_subject', $this->hsc_dip_additional_subject])
            ->andFilterWhere(['like', 'hsc_dip_gpa', $this->hsc_dip_gpa])
            ->andFilterWhere(['like', 'ssc_edu_data', $this->ssc_edu_data])
            ->andFilterWhere(['like', 'hsc_edu_data', $this->hsc_edu_data])
            ->andFilterWhere(['like', 'ssc_teletalk_data', $this->ssc_teletalk_data])
            ->andFilterWhere(['like', 'hsc_teletalk_data', $this->hsc_teletalk_data])
            ->andFilterWhere(['like', 'experience_one_institute', $this->experience_one_institute])
            ->andFilterWhere(['like', 'experience_one_subject', $this->experience_one_subject])
            ->andFilterWhere(['like', 'experience_one_year', $this->experience_one_year])
            ->andFilterWhere(['like', 'experience_one_cert_name', $this->experience_one_cert_name])
            ->andFilterWhere(['like', 'experience_two_institute', $this->experience_two_institute])
            ->andFilterWhere(['like', 'experience_two_subject', $this->experience_two_subject])
            ->andFilterWhere(['like', 'experience_two_year', $this->experience_two_year])
            ->andFilterWhere(['like', 'experience_two_cert_name', $this->experience_two_cert_name])
            ->andFilterWhere(['like', 'experience_three_institute', $this->experience_three_institute])
            ->andFilterWhere(['like', 'experience_three_subject', $this->experience_three_subject])
            ->andFilterWhere(['like', 'experience_three_year', $this->experience_three_year])
            ->andFilterWhere(['like', 'experience_three_cert_name', $this->experience_three_cert_name])
            ->andFilterWhere(['like', 'experience_four_institute', $this->experience_four_institute])
            ->andFilterWhere(['like', 'experience_four_subject', $this->experience_four_subject])
            ->andFilterWhere(['like', 'experience_four_year', $this->experience_four_year])
            ->andFilterWhere(['like', 'experience_four_cert_name', $this->experience_four_cert_name])
            ->andFilterWhere(['like', 'naval_father_name', $this->naval_father_name])
            ->andFilterWhere(['like', 'naval_office_no', $this->naval_office_no])
            ->andFilterWhere(['like', 'naval_rank', $this->naval_rank])
            ->andFilterWhere(['like', 'navy_ship_etbd_retired', $this->navy_ship_etbd_retired])
            ->andFilterWhere(['like', 'anser_vdp_rank', $this->anser_vdp_rank])
            ->andFilterWhere(['like', 'anser_vdp_office_no', $this->anser_vdp_office_no])
            ->andFilterWhere(['like', 'payment_type', $this->payment_type])
            ->andFilterWhere(['like', 'ref_id', $this->ref_id])
            ->andFilterWhere(['like', 'validation_id', $this->validation_id])
            ->andFilterWhere(['like', 'order_id_original', $this->order_id_original])
            ->andFilterWhere(['like', 'card_type', $this->card_type])
            ->andFilterWhere(['like', 'card_no', $this->card_no])
            ->andFilterWhere(['like', 'payment_api', $this->payment_api])
            ->andFilterWhere(['like', 'referred_by', $this->referred_by])
            ->andFilterWhere(['like', 'reference_details', $this->reference_details])
            ->andFilterWhere(['like', 'relationship', $this->relationship]);



        if ($this->list_custom_filter) {
            if ($this->list_custom_filter == 1) // paid & complete application            
            {
                $query->andFilterWhere(['payment_status' => 1]);
                $query->andFilterWhere(['is not', 'serial_no', new \yii\db\Expression('NULL')]);
            } else if ($this->list_custom_filter == 2) // paid & not complete application            
            {
                $query->andFilterWhere(['payment_status' => 1]);
                $query->andFilterWhere(['is', 'serial_no', new \yii\db\Expression('NULL')]);
            }
        }

        // $query->orderBy('serial_no DESC');
        // echo $query->createCommand()->getRawSql();
        // die();

        return $dataProvider;
    }
}
