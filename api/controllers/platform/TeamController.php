<?php
namespace api\controllers\platform;

use Yii;
use api\controllers\BaseController;

class TeamController extends BaseController
{

	public function actionIndex()
	{
		parent::checkGet();

		$data = Yii::$app->request->get();
		$page = isset($data['page']) ? $data['page'] : 1;
    	$page_size = 20;
    	$platform_id = Yii::$app->session->get("platform_id");


	}

	public function actionCreate()
	{
		parent::checkPost();

		$data = Yii::$app->request->post();

		$params = array(
			'name' => '',
			'platform_id' => '',
			'start_date'
		);
	}

}