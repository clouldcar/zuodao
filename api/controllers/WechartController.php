<?php
namespace api\controllers;

class WechartController extends Controller
{

	public function actionAuth() {
		$data = Yii::$app->request->get();
		//TODO $data['state']

		//获取临时票据code
		if(!isset($data['code']) || empty($data['code'])) 
		{
			return Utils::returnMsg(1, "请正确填写手机号");
		}
	}
}