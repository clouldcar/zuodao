<?php
namespase api\controllers;

use Yii;

class PlanController extends baseController
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
		$uid = Yii::$app->user->id;
	}
}