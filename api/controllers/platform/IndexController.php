<?php
namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;


class IndexController extends BaseController
{
	public $platform_id;

    public function init()
    {
        parent::init();
        $this->platform_id = Yii::$app->session['platform_id'];
    }

	public function actionIndex()
	{
		return Utils::returnMsg();
	}
}