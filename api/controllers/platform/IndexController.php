<?php
namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;
use api\models\User;


class IndexController extends BaseController
{
    public function init()
    {
        parent::init();
        parent::checkPlatformUser();
    }

	public function actionIndex()
	{
		$user = User::findOne(['id' => 41275744476381]);
		$result = Yii::$app->user->login($user, 3600 * 24* 30);
		var_dump($result);
		print_r(Yii::$app->user->identity);exit;
		return Utils::returnMsg();
	}
}