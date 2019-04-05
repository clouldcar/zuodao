<?php

namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\QueryParamAuth;
use yii\filters\Cors;
use yii\web\Response;
use yii\helpers\Json;
use common\helpers\Utils;
use api\models\Platform;
use api\models\PlatformUser;


class BaseController extends ActiveController
{

    public $modelClass = '';
//    public $enableCsrfValidation = false;
    public $platform_id;

    public function init()
    {
        parent::init();

        // \Yii::$app->user->enableSession = false;

        //开启session
        $session = Yii::$app->session;
        if(!$session->isActive)
        {
            $session->open();
        }

        $user = User::findOne(['id' => 41275744476381]);
        Yii::$app->user->login($user, 3600 * 24* 30);

    }

    public function behaviors()
    {
        $behaviors =  parent::behaviors();

        //设置跨域
        $behaviors['corsFilter'] = [
            'class' => Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => true,
            ],
        ];

        //定义返回格式是json
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;

        //取消默认authenticator认证，以确保 cors 被首先处理。然后，我们在实施自己的认证程序之前，强制 cors 允许凭据。
        unset($behaviors['authenticator']);

        //token验证
        // $behaviors['authenticator'] = [
        //    'class' => QueryParamAuth::className(),
        //    'optional' => [
        //         'login',
        //         'add'
        //     ],
        // ];
   
        return $behaviors;
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'test' : null,
            ],
        ];
    }

    public function checkLogin()
    {
        if(Yii::$app->user->isGuest)
        {
            echo Json::encode(Utils::returnMsg(401, "请先登录"));
            exit;
        }
    }

    public function checkPost()
    {
        if (!Yii::$app->request->isPost) {
            echo Json::encode(Utils::returnMsg(404, "404"));
            exit;
        }
    }

    public function checkGet()
    {
        if (Yii::$app->request->isPost) {
            echo Json::encode(Utils::returnMsg(404, "404"));
            exit;
        }
    }

    //检查平台用户
    public function checkPlatformUser()
    {
        $result = false;
        if(Yii::$app->user->isGuest)
        {
            echo Json::encode(Utils::returnMsg(404, "404"));
            exit;
        }

        $uid = Yii::$app->user->id;

        $platform = Platform::getInfoByUID($uid);
        if($platform && $platform->id)
        {
            $this->platform_id = $platform->id;
            $result = true;
        }
        elseif($platform_user = PlatformUser::getUser($uid))
        {
            $this->platform_id = $platform_user->platform_id;
            $result = true;
        }

        if(!$result)
        {
            echo Json::encode(Utils::returnMsg(404, "404"));
            exit;
        }
    }
//
//    /**
//     * ajax返回客户端json方法
//     */
//    protected function ajaxReturn($data)
//    {
//        header('Content-Type:application/json; charset=utf-8');
//        echo json_encode($data);
//        exit();
//    }
}
