<?php

namespace shop\controllers;

use Yii;
use shop\models\Admin;

/**
 * 后台用户控制器
 */
class AdminController extends BaseController
{
    public function init()
    {
        parent::init();
    }

    //用户列表
    public function actionIndex()
    {
        
    }

    /** 
     * 添加用户操作
     */
    public function actionAdd()
    {
        $model = new Admin();
        if (Yii::$app->request->isPost) {
            //表单验证是不是post方法
            $data = Yii::$app->request->post();
            if ($data['type'] == 1) {
                //后台管理使用账号密码创建及登录
                if (empty($data['password']) || strlen($data['password']) < 6) {
                    $this->error('密码为空或者小于6字符');
                }
                $model->setAttributes($data);
                $model->setPassword($data['password']);
            } else {

            }
        }
    }
}