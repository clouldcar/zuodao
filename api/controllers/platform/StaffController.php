<?php
namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;

class StaffController extends BaseController
{
	const  INVITE_STATUS_ON  = 1;
    const  INVITE_STATUS_OFF = 0;
    const  INVITE_TYPE_IN = 1;
    const  INVITE_TYPE_OUT = 0;
    const  USER_PASSWORD = 123456;
    const  PLATFORM_STATUS = 1;

    /**
	* 员工列表
    */
    public function actionIndex()
    {
    	parent::checkGet();

    	$data = Yii::$app->request->get();

    	$type = $data['type'] ? $data['type'] : 0;
    	$page = $data['page'] ? $data['page'] : 1;
    	$page_size = 20;

    	$user_list = (new PlatformUser())->platformUsers($platform_id, $type, $page, $page_size);
    }

    /**
	* 新增员工
    */
	public function actionCreate()
	{
		parent::checkPost();

		$data = Yii::$app->request->post();
		$model = new PlatformUser();
		if($model->isExists($data['uid'], $data['platform_id']))
		{
			return Utils::returnMsg(1, "用户已存在");
		}

		$params = [
            'uid'    => $data['uid'],
            'platform_id'   => $data['platform_id'],
            'permissions'=> $data['permissions'],
            'status'     => self::PLATFORM_STATUS
        ];

        $platform = $model->addStaff($params);

		return Utils::returnMsg(0, "success");
	}
}