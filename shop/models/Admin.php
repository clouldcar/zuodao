<?php

namespace shop\controllers;

use Yii;
use yii\web\IdentityInterface;
use yii\base\NotSupportedException;

class Admin extends \common\models\Admin implements IdentityInterface
{
    //设置加密后的密码
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }
}