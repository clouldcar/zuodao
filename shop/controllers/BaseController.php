<?php

namespace shop\controllers;

use Yii;


class BaseController extends Controller
{
    public function init()
    {

    }

    /**
     * ajax返回客户端json方法
     */
    protected function ajaxReturn($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }
}