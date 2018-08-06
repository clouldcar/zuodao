<?php

namespace shop\controllers;

use shop\models\LoginForm;
use Yii;
use shop\models\User;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;


/**
 * 后台用户控制器
 */
class UserController extends BaseController
{

    public $modelClass = 'shop\models\User';

   /* public $enableCsrfValidation = false;
    public $returnData = array();
    public function init()
    {
        parent::init();
    }*/

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return ArrayHelper::merge([
            [
                'class' => Cors::className(),
            ],
        ], $behaviors);
    }

    //用户列表
    public function actionIndex()
    {   
        $condition = ['status' => User::STATUS_ACTIVE];
        return User::findAll($condition);
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
            } else {

            }
        }
        return $this->returnData;
    }

    public function actionLogin()
    {    

        $model = new LoginForm();
        $data = Yii::$app->request->post();
        if ($model->isLogin($data['username'])) {
            return $this->returnData = [
                'code' => '801',
                'msg' => '您已经登录',
            ];
        }
        if (intval($data['type']) === User::SIGNSTATUS_BACKEND) {
            if ($model->load($data, '') && $model->login()) {
                //设置session
                $model->setSession($data['username']);

                $this->returnData['code'] = 1;
                $this->returnData['msg'] = '登录成功';
            } else {
                $this->returnData['code'] = 0;
                $this->returnData['msg'] = '登录失败';
            }
        }
        return $this->returnData;
    }

    /**
     * 编辑用户
     * @return [type] [description]
     */
    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $model = User::findOne($data['id']);
            if (!$model || !$model->status) {
                return $this->returnData = [
                    'code' => 803,
                    'msg' => '该用户不存在',
                ];
            }
            $data['updated_at'] = date('Y-m-d H:i:s');
            //判断是否需要重置密码
            if (!empty($data['password'])) {
                $model->generateAuthKey();
                $model->setPassword($data['password']);
            }
            unset($data['password']);
            $model->setAttributes($data);
            if ($model->save()) {
                $this->returnData = [
                    'code' => 1,
                    'msg' => '修改用户成功',
                ];             
            }else {
                $this->returnData = [
                    'code' => 0,
                    'msg' => '修改用户失败',
                ];
            }
        }
        var_dump($_SESSION);
        return $this->returnData;
    }


    public function actionDelete()
    {
        //判断批量删除
        $ids = Yii::$app->request->get('id', 0);
        $ids = implode(',', array_unique((array)$ids));
        if (empty($ids)) {
            return $this->returnData = [
                'code' => 802,
                'msg' => '请选择要删除的数据',
            ];
        }
        $_where = 'id in (' . $ids . ')';
        if ((new User())->updateUserStatus($_where)) {
            return $this->returnData = [
                'code' => 1,
                'msg' => '删除用户成功'
            ];
        } else {
            return $this->returnData = [
                'code' => 0,
                'msg' => '删除用户失败'
            ];
        }
    }

    public function actionLogout()
    {   
        //移除所有session信息
        Yii::$app->session->removeAll();
        Yii::$app->session->destroy();

        return $this->returnData = [
            'code' => 1,
            'msg' => '登出成功',
        ];

    }
}