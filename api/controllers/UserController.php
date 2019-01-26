<?php

namespace api\controllers;

use Yii;

use common\helpers\Utils;
use common\helpers\Validate;
use api\models\LoginForm;
use api\models\CheckSms;
use api\models\User;
use yii\filters\Cors;
use yii\helpers\ArrayHelper;
use AlibabaCloud\Client\AlibabaCloud;


/**
 * 后台用户控制器
 */
class UserController extends BaseController
{

    public $modelClass = 'api\models\User';

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

            if (empty($data['password']) || strlen($data['password']) < 6) {
                $this->error('密码为空或者小于6字符');
            }
            //默认普通用户
            $data['type'] = User::SIGNSTATUS_FRONTEND;

            $model->setAttributes($data);
            $model->setPassword($data['password']);
            $model->id = Utils::createIncrementId();
            if ($model->save()) {
                return Utils::returnMsg(0, "success");
            } else {
                return Utils::returnMsg(1, "add fail");
            }
        }
    }

    public function actionLogin()
    {    
        $model = new LoginForm();
        $data = Yii::$app->request->post();
        if ($model->isLogin($data['username'])) {
            return Utils::returnMsg(1, "您已经登录");
        }

        if ($model->load($data, '') && $model->login()) {

            //设置session
            $model->setSession($data['username']);

            return Utils::returnMsg(0, "登录成功");
        } else {
            return Utils::returnMsg(0, "登录失败");
        }
    }

    /**
    * 补全信息
    */
    public function actionReplenish()
    {
        $result = array(
            'code' => 0,
            'msg' => '失败'
        );

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $uid = Yii::$app->user->id;

            $code = $data['code'];
            //短信验证
            $result = CheckSms::get($data['phone']);
            $time = time();
            if(!$result || $result[0]['code'] != $data['code'] || $time - strtotime($result[0]['ctime']) > 300)
            {
                Utils::returnMsg('1', '短信验证码不正确或已过期');
            }
            unset($data['code']);

            $data['updated_at'] = date('Y-m-d H:i:s');

            $model = User::findIdentity($uid);

            if ($model && $model->status) {
                $model->setAttributes($data);

                if ($model->save()) {
                    return Utils::returnMsg(0, "success");
                } else {
                    return Utils::returnMsg(1, "fail");
                }
            }
        }

        return $result;
    }

    /**
     * 编辑用户
     * @return [type] [description]
     */
    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $model = User::findIdentity($data['id']);
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
                return Utils::returnMsg(0, "success");
            }else {
                return Utils::returnMsg(1, "fail");
            }
        }
        return $this->returnData;
    }


    public function actionDelete()
    {
        //判断批量删除
        $ids = Yii::$app->request->get('id', 0);
        $ids = implode(',', array_unique((array)$ids));
        if (empty($ids)) {
            return Utils::returnMsg(1, "请选择要删除的数据");
        }
        $_where = 'id in (' . $ids . ')';
        if ((new User())->updateUserStatus($_where)) {
            return Utils::returnMsg(0, "删除用户成功");
        } else {
            return Utils::returnMsg(1, "删除用户失败");
        }
    }

    public function actionLogout()
    {   
        //移除所有session信息
        Yii::$app->session->removeAll();
        Yii::$app->session->destroy();

        return Utils::returnMsg(1, "登出成功");
    }

    public function actionSmsCode()
    {
        $data = Yii::$app->request->post();

        if(isset($data['phone']) && !Validate::isMobile($data['phone']))
        {
            return Utils::returnMsg(1, "请正确填写手机号");
        }

        $result = CheckSms::get($data['phone']);

        //每天最多20次
        if(count($result) > 20)
        {
            Utils::returnMsg('1', '请求次数超过限制，请明天再试');
        }

        $time = time();
        if($result && $time - strtotime($result[0]['ctime']) < 300)
        {
            Utils::returnMsg('1', '请求次数过多，请稍后再试');
        }

        //随机验证码
        $code = mt_rand(100000,999999);
        str_shuffle($code);


        AlibabaCloud::accessKeyClient('LTAIVBWvIkd7jKXi', 'Mw9k7bjpCVFZxBfS5fXKmfO8ayIkaW')
                        ->regionId('cn-hangzhou') // replace regionId as you need
                        ->asGlobalClient();

        $result = AlibabaCloud::rpcRequest()
                  ->product('Dysmsapi')
                  // ->scheme('https')
                  ->version('2017-05-25')
                  ->action('SendSms')
                  ->method('POST')
                  ->options([
                    'query' => [
                        'PhoneNumbers' => $data['phone'],
                        'SignName' => '做到',
                        'TemplateCode' => 'SMS_138650016',
                        'TemplateParam' => json_encode(["code" => $code])
                    ],
                ])->request();
        if($result->Code !== "OK") 
        {
            return Utils::returnMsg(1, "发送失败，请检查后再试！");
        }
        return Utils::returnMsg(0, "发送成功！");
    }
}
