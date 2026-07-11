<?php

namespace common\models;

use yii\base\Model;


class ChangePassword extends Model
{
   public $newpassword;
   public $repeatnepassword;

   public function rules()
   {
      return [
         [['newpassword', 'repeatnepassword'], 'string', 'min' => 6],
         [['newpassword', 'repeatnepassword'], 'required'],
         ['repeatnepassword', 'compare', 'compareAttribute' => 'newpassword'],
      ];
   }


   public function attributeLabels()
   {
      return [
         'newpassword' => 'New Password',
         'repeatnepassword' => 'Retype Password',
      ];
   }

   
}
  