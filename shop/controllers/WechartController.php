<?php
namespace shop\controllers;

class WechartController extends Controller
{

	public function actionAuth() {
		//获取临时票据code
		$code = Yii::$app->request->get('code');
		file_put_contents("/tmp/zuodao-log", $code);
	}
}