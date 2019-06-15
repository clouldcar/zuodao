<?php

namespace api\controllers;

use Yii;

use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use AlibabaCloud\Client\AlibabaCloud;
use common\helpers\Utils;
use common\helpers\Validate;
use api\models\LoginForm;
use api\models\CheckSms;
use api\models\User;
use api\models\UserInfo;
use api\models\Plan;


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

    /*
    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ]
        ], $behaviors);
    }
    */

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
            $model->id = Utils::createIncrementId(Utils::ID_TYPE_USER);
            print_r($model);exit;
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

        $model->setAttributes($data);

        if ($model->validate() && $model->login()) {

            //设置session
            // $model->setSession($model->_user->id);
            return Utils::returnMsg(0, "登录成功", empty($model->updated_at));
        } else {
            return Utils::returnMsg(1, "登录失败");
        }
    }

    /**
    * 补全信息
    */
    public function actionReplenish()
    {
        parent::checkLogin();
        parent::checkPost();

        $data = Yii::$app->request->post();
        $uid = Yii::$app->user->id;

        $code = $data['code'];
        //短信验证
        $result = CheckSms::get($data['phone']);
        $time = time();
        if(!$result || $result[0]['code'] != $data['code'] || $time - strtotime($result[0]['ctime']) > 300)
        {
            return Utils::returnMsg('1', '短信验证码不正确或已过期');
        }
        unset($data['code']);

        $data['updated_at'] = date('Y-m-d H:i:s');

        $model = UserInfo::getInfoByPhone($data['phone']);
        //如果user_info中有记录，则替换uid
        if($model)
        {
            $model->isNewRecord = false;
            $data['id'] = $model->uid;
        }
        else
        {
            $model = new UserInfo();
        }

        $model->setAttributes($data);

        if(!$model->validate())
        {
            return Utils::returnMsg(1, "参数有误");
        }

        if (!$model->save()) {
            return Utils::returnMsg(1, "fail");
        }

        return Utils::returnMsg(0, "success");
    }

    /**
     * 编辑用户
     * @return [type] [description]
     */
    public function actionEdit()
    {
        parent::checkLogin();
        parent::checkPost();

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
        return $this->returnData;
    }


    public function actionLogout()
    {
        parent::checkLogin();
        parent::checkPost();

        //移除所有session信息
        Yii::$app->session->removeAll();
        Yii::$app->session->destroy();

        return Utils::returnMsg(1, "登出成功");
    }

    public function actionSmsCode()
    {
        parent::checkLogin();
        parent::checkPost();
        
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


        AlibabaCloud::accessKeyClient(
                Yii::$app->params['aliyun']['accessKeyId'], 
                Yii::$app->params['aliyun']['accessKeySecret']
        )->regionId(Yii::$app->params['sms']['regionId'])->asGlobalClient();

        $result = AlibabaCloud::rpcRequest()
                  ->product(Yii::$app->params['sms']['product'])
                  // ->scheme('https')
                  ->version(Yii::$app->params['sms']['version'])
                  ->action(Yii::$app->params['sms']['action'])
                  ->method(Yii::$app->params['sms']['method'])
                  ->options([
                    'query' => [
                        'PhoneNumbers' => $data['phone'],
                        'SignName' => Yii::$app->params['sms']['SignName'],
                        'TemplateCode' => Yii::$app->params['sms']['TemplateCode'],
                        'TemplateParam' => json_encode(["code" => $code])
                    ],
                ])->request();
        if($result->Code !== "OK") 
        {
            return Utils::returnMsg(1, "发送失败，请检查后再试！");
        }
        return Utils::returnMsg(0, "发送成功！");
    }

    public function actionPlanList()
    {
        parent::checkGet();
        $page = isset($data['page']) ? $data['page'] : 1;
        $page_size = 20;

        $uid = Yii::$app->user->id;

        $list = Plan::listByUid($uid, $page, $page_size);

        return Utils::returnMsg(0, null, $list);
    }

}
