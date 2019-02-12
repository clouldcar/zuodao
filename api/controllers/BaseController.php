<?php

namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use common\helpers\Utils;
use api\models\PlatformUser;


class BaseController extends ActiveController
{

    public $modelClass = '';
//    public $enableCsrfValidation = false;
    public $returnData = array();

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        //定义返回格式是json
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
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
            return Utils::returnMsg(401, "请先登录");
        }
    }

    public function checkPost()
    {
        if (!Yii::$app->request->isPost) {
            return Utils::returnMsg(404, "404");
        }
    }

    public function checkGet()
    {
        if (Yii::$app->request->isPost) {
            return Utils::returnMsg(404, "404");
        }
    }
    public function init()
    {
        parent::init();

        //开启session
        $session = Yii::$app->session;
        if(!$session->isActive)
        {
            $session->open();
        }

        // print_r(Yii::$app->user->identity);exit;

        //检查平台用户
        if($uid = Yii::$app->user->id)
        {
            $platform_user = PlatformUser::getUser($uid);
            $session->set('platform_id', $platform_user->platform_id);
            $session->set('platform_user_type', $platform_user->permissions);
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
