<?php
namespace api\controllers\platform;

use Yii;
use  yii\web\Session;

use api\controllers\BaseController;
use common\helpers\Utils;
use api\models\PlatformUser;


class StaffController extends BaseController
{
	const  INVITE_STATUS_ON  = 1;
    const  INVITE_STATUS_OFF = 0;
    const  INVITE_TYPE_IN = 1;
    const  INVITE_TYPE_OUT = 0;
    const  USER_PASSWORD = 123456;
    const  PLATFORM_STATUS = 0;

    public function init()
    {
        parent::init();
        parent::checkPlatformUser();
    }

    /**
	* 员工列表
    */
    public function actionIndex()
    {
    	parent::checkGet();

    	$data = Yii::$app->request->get();

    	$type = isset($data['type']) ? $data['type'] : 0;
    	$page = isset($data['page']) ? $data['page'] : 1;
    	if($page < 1)
        {
            $page = 1;
        }
        $page_size = 20;

    	$model = new PlatformUser();

    	$list = $model->getUsers($this->platform_id, $type, $page, $page_size);

    	return Utils::returnMsg(0, null, $list);
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
            'platform_id'   => $this->platform_id,
            'permissions'=> $data['permissions'],
            'status'     => self::PLATFORM_STATUS
        ];

        $platform = $model->addStaff($params);

		return Utils::returnMsg(0, "success");
	}

	public function actionUpdatePermission()
	{
		parent::checkPost();
		$data = Yii::$app->request->post();

		$platform_id = Yii::$app->session->get("platform_id");

		$params = array(
			'platform_id'   => $platform_id,
			'uid' => $data['uid'],
			'permissions'=> $data['permissions']
		);

		PlatformUser::updatePermission($params);
	}

    public function actionRemove()
    {
        parent::checkGet();

        $uid = Yii::$app->request->get('uid');
        
    }
}