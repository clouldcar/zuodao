<?php
namespase api\controllers;

use Yii;

class PlanController extends baseController
{
	private $plan_type = [
		'1' => '事业',
		'2' => '家庭',
		'3' => '健康',
		'4' => '人际关系',
		'5' => '学习成长'
	];

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

		$params = [
			'title' => $data['title'],
			'uid' => $uid,
			'objective' => '',
			'inspire' => 3,
			'social_services' => 4
		];
	}
}