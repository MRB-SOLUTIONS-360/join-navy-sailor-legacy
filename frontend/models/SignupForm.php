<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use common\models\User;
use common\static\DataEncryption;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $username;
    public $birth_registration_no;
    public $email;
    public $password;
    public $confirm_password;
    public $phone_no;
    public $dob;
    public $captcha;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // ['phone_no', 'trim'],
            // ['phone_no', 'required'],
            ['username', 'trim'],
            [['username', 'dob', 'captcha','birth_registration_no'], 'required'],
            ['username', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This username has already been taken.'],
            ['birth_registration_no', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This Birth Registration No has already been taken.'],
            // ['username', 'integer'],

            ['username', 'usernameValidation'],
            // ['username', 'string', 'min' => 8, 'max' => 12],


            ['email', 'trim'],
            /// ['email', 'required'],
            ['email', 'email'],
            ['email', 'string', 'max' => 255],
            /// ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            [['password', 'confirm_password'], 'required'],
            ['password', 'string', 'min' => 6, 'max' => 15],

            [
                'confirm_password',
                'compare',
                'compareAttribute' => 'password',
                'message' => "Passwords don't match",
            ],


            ///['password', 'string', 'min' => Yii::$app->params['user.passwordMinLength']],
        ];
    }

    public function usernameValidation($attribute, $params, $validator)
    {
        if (!empty($this->$attribute)) {
            $value = $this->$attribute;
            if (strlen($value) < 6 || strlen($value) > 15)
                $this->addError($attribute, 'The username must contain a minimum of 6 characters including letters, digits and special character.No whitespace is allowed.');
            else if (!preg_match('/[a-zA-Z]/', $value))
                $this->addError($attribute, 'The username must have at least  1 letter.');
            elseif (!preg_match('/\d/', $value))
                $this->addError($attribute, 'The username must have at least  1 digit.');
            // elseif (!preg_match('/[@$!%*?&]/', $value))
            //     $this->addError($attribute, 'The username must have at least  1 special character.');
            elseif (!preg_match('/^\S*$/', $value))
                $this->addError($attribute, 'No whitespace is allowed');
        }
        return true;


        // if (!empty($this->$attribute) && !preg_match('/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[a-zA-Z\d@$!%*?&]{8,12}$/', $this->$attribute)) // '/[^a-z\d]/i' should also work.
        // {
        //     $this->addError(
        //         $attribute,
        //         'The username must contain a minimum of 6 characters including letters, digits and special character.No whitespace is allowed.'
        //     );
        // }
        // if (!empty($this->$attribute) && !preg_match('/^(?:\+88|88)?(01[3-9]\d{8})$/', $this->$attribute)) // '/[^a-z\d]/i' should also work.
        // {
        //     $this->addError($attribute, 'Please provide a valid phone number.01555XXXXXX');
        // }
        // return true;
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'dob' => 'Date of Birth',
            'captcha' => 'Answer',
            'birth_registration_no' => 'Birth Registration No',

        ];
    }

    /**
     * Validate birth registration number
     */
    public function validateBirthRegistration($attribute, $params)
    {
        $birth_reg_no = $this->$attribute;
        
        // Check if birth registration number exists in database
        $exists = \common\models\User::find()
            ->where(['birth_registration_no' => $birth_reg_no])
            ->exists();
            
        if ($exists) {
            $this->addError($attribute, 'This Birth Registration No is already registered.');
        }
        
        // You can add additional validation logic here
        // For example, format validation, length check, etc.
        if (empty($birth_reg_no)) {
            $this->addError($attribute, 'Birth Registration No cannot be empty.');
        } elseif (!preg_match('/^[0-9]+$/', $birth_reg_no)) {
            $this->addError($attribute, 'Birth Registration No must contain only numbers.');
        } elseif (strlen($birth_reg_no) < 10 || strlen($birth_reg_no) > 17) {
            $this->addError($attribute, 'Birth Registration No must be between 10 to 17 digits.');
        }
    }

    /**
     * Signs user up.
     *
     * @return bool whether the creating new account was successful and email was sent
     */
    public function signup()
    {

        if (!$this->validate()) {
            return null;
        }


        $user = new User();
        $user->username = $this->username;
        $user->birth_registration_no = $this->birth_registration_no;
        // $user->phone_no = $this->username;
        $user->phone_no = DataEncryption::dataEncrypt($this->phone_no);
        $user->email = ($this->email) ? $this->email : $this->username . '@register.com';
        // $user->phone_no = $this->phone_no;
        $user->dob = date('Y-m-d', strtotime($this->dob));
        $user->setPassword($this->password);
        $user->generateAuthKey();
        $user->generateEmailVerificationToken();
        $user->user_group = 'register';
        $user->user_type = 'candidate';
        $user->status = $user::STATUS_ACTIVE;


        return $user->save(false) ? $user : null;
        // return $user->save() && $this->sendEmail($user);
    }

    /**
     * Sends confirmation email to user
     * @param User $user user model to with email should be send
     * @return bool whether the email was sent
     */
    protected function sendEmail($user)
    {
        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'emailVerify-html', 'text' => 'emailVerify-text'],
                ['user' => $user]
            )
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name . ' robot'])
            ->setTo($this->email)
            ->setSubject('Account registration at ' . Yii::$app->name)
            ->send();
    }
}
