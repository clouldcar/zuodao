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
            [['username','password'], 'required' ],
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
            $result = Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600 * 24* 30 : 0);

            return $result;
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


    /**
     * 设置登录成功后的session
     * @param [type] $username [description]
     */
    public function setSession($username)
    {
        $userInfo = (new User())->getUserAllInfo($username);
        Yii::$app->session->set('user_id', $userInfo['id']);
        Yii::$app->session->set('username', $userInfo['username']);

        if (isset($userInfo['platform_id'])) {
            Yii::$app->session->set('platform_id', $userInfo['platform_id']);
        }
                
        if (isset($userInfo['team_id'])) {
            Yii::$app->session->set('team_id', $userInfo['team_id']);
        }

        return true;
    }
}