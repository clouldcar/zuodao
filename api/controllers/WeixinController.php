<?php
namespace api\controllers;

use Yii;

use common\helpers\Utils;

class WeixinController extends BaseController
{
	public function actionAuth()
	{
		$data = Yii::$app->request->get();
		// if($data['state'])
		if(!isset($data['code']) || empty($data['code'])) 
		{
			return Utils::returnMsg(1, "请正确填写手机号");
		}
	}
}