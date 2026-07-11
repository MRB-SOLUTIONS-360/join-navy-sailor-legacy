<?php

namespace common\models;

use yii\base\Model;


class ResetPassword extends Model
{
   public $username;
   public $dob;

   public function rules()
   {
      return [

         [['username', 'dob'], 'required'],

      ];
   }


   public function attributeLabels()
   {
      return [
         'username' => 'Username',
         'dob' => 'Date of Birth',
      ];
   }
}
