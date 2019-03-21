<?php
namespace api\controllers;

use Yii;
use use common\helpers\Utils;

class WeekPlanController extends baseController
{
    public function init()
    {
        parent::init();
        parent::checkLogin();
    }

    public function actionIndex()
    {

    }

    public function actionCreate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();

        $uid = Yii::$app->user->id;

    }

}