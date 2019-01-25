<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $phone;
    public $password;
    public $rememberMe = true;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // phone and password are both required
            [['phone', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
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
                $this->addError($attribute, 'Incorrect phone or password.');
            }
        }
    }

    /**
     * Logs in a user using the provided phone and password.
     *
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $status =  Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24 * 30 : 0);
            if($status){
                $user = $this->getUser();
                $user->password_reset_token = $token = $this->setToken();
                $user->token_overdue_time = strtotime("+15 day");
                $status = $user->save();
                if($status===false){
                    return false;
                }else{
                    return $token;
                }
////                $this->setToken($this->User());
            }else{
                return false;
            }
            return $status;
        } else {
            return false;
        }
    }

    /**
     * Finds user by [[phone]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->phone);
        }

        return $this->_user;
    }
    //获取token
    protected function setToken(){
        return md5(str_replace('.','',uniqid($this->getRandomString(10).time(),true)));
    }
    //获取随机字符串
    protected function getRandomString($len, $chars=null)
    {
        if (is_null($chars)){
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }
        mt_srand(10000000*(double)microtime());
        for ($i = 0, $str = '', $lc = strlen($chars)-1; $i < $len; $i++){
            $str .= $chars[mt_rand(0, $lc)];
        }
        return $str;
    }
}
