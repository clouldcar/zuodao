<?php
namespace api\controllers;

use Yii;

class Invite extends BaseController
{
	public function init()
    {
        parent::init();
    }

	public function actionTeam()
	{
		$data = Yii::$app->request->get();
		$uid = Yii::$app->identity->id;
	}
}