<?php

namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $username;
    public $password;
    public $rememberMe = true;

    public $captcha;

    public $user_type;

    private $_user;


    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // username and password are both required
            ['username', 'trim'],
            [['username', 'password', 'captcha'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Username',
            'captcha' => 'Answer',
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }

            // check user type 
            // is candidate or admin user            
            if ($user && $user->user_type != $this->user_type) {
                $this->addError($attribute, 'You are not ' . $this->user_type . ' user!');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {

        if ($this->validate()) {
            $userModel = $this->getUser();
            $userModel->login_zone = $this->getLoginAddress($_SERVER['REMOTE_ADDR']);
            $userModel->os = PHP_OS;
            $userModel->save(false);
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
        }

        return false;
    }

    // ip & others information's
    public function getLoginAddress($ip)
    {
        $json = @file_get_contents("http://ipinfo.io/" . $ip);
        $details = json_decode($json);
        $city = !empty($details->city) ? $details->city : 'local';
        $region = !empty($details->region) ? $details->region : 'local';
        $country = !empty($details->country) ? $details->country : 'local';
        $loc = !empty($details->loc) ? $details->loc : 'latlong';
        $org = !empty($details->org) ? $details->org : 'local ';
        return $city . ', ' . $region . ', ' . $country . ', ' . $loc . ', ' . $org;
    }



    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }
}
