<?php
namespace api\controllers;

use Yii;
use common\helpers\Utils;

class ArticleController extends BaseController
{

	public function init()
    {
        parent::init();
        parent::checkLogin();
    }

    public function actionCreate()
    {
    	$data = Yii::$app->request->post();
    }

}