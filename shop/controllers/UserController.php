<?php

namespace shop\controllers;

use shop\models\LoginForm;
use Yii;
use shop\models\User;

/**
 * 后台用户控制器
 */
class UserController extends BaseController
{
    public $modelClass = 'shop\models\User';

    //用户列表
    public function actionIndex()
    {
        return ['msg' => 'ok'];
    }

    /** 
     * 添加用户操作
     */
    public function actionAdd()
    {
        $model = new User();
        if (Yii::$app->request->isPost) {
            //表单验证是不是post方法
            $data = Yii::$app->request->post();
            if ($data['type'] == User::SIGNSTATUS_BACKEND) {
                //后台管理使用账号密码创建及登录
                if (empty($data['password']) || strlen($data['password']) < 6) {
                    $this->error('密码为空或者小于6字符');
                }
                $model->setAttributes($data);
                $model->setPassword($data['password']);
                $model->id = createIncrementId();
                if ($model->save()) {
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = 'add success';
                } else {
                    $this->returnData['code'] = 0;
                    $this->returnData['msg'] = 'add fail';
                }
                return $this->returnData;
            } else {

            }
        }
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        $data = Yii::$app->request->post();
        if (intval($data['type']) === User::SIGNSTATUS_BACKEND) {
            if ($model->load($data, '') && $model->login()) {
                //设置session
                $userInfo = (new User())->getUserAllInfo($data['username']);
                Yii::$app->session->set('user_id', $userInfo['id']);
                Yii::$app->session->set('username', $userInfo['username']);
                Yii::$app->session->set('platform_id', $userInfo['platform_id']);

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = 'login success';
            } else {
                $this->returnData['code'] = 0;
                $this->returnData['msg'] = 'login fail';
            }
        }

        return $this->returnData;
    }
}
