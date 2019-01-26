<?php

namespace api\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;


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
//    public function init()
//    {
//
//    }
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
