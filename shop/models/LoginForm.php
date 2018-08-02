<?php

namespace shop\models;

use shop\models\User as User;
use Yii;
use yii\base\Model;

class LoginForm extends Model
{
    public $rememberMe = true;
    public $username;
    public $password;
    public $type;

    protected $_user;

    public function rules()
    {
        return [
            [['username','password','type'], 'required' ],
            ['password', 'validatePassword'],
            ['rememberMe', 'boolean'],
        ];
    }

    /**
     * 判断是否登录
     * @param  [type]  $username [用户名]
     * @return boolean           
     */
    public function isLogin($username)
    {
        if (Yii::$app->session->get('username') == $username) {
            return true;
        } else {
            return false;
        }
    }
    
    public function login()
    {
        if ($this->validate()) {
            if ($this->rememberMe) {
                $this->_user->generateAuthKey();
            }
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24* 30 : 0);
        } else {
//            var_dump($this->getErrors());
            return false;
        }
    }

    public function validatePassword($attribute, $params)
    {
        if (!$this->haserrors()) {
            $user = $this->getUser();
            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }

        }
    }

    public function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }



}