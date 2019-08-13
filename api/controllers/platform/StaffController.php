<?php
namespace api\controllers\platform;

use Yii;
use  yii\web\Session;

use api\controllers\BaseController;
use common\helpers\Utils;
use api\models\PlatformUser;
use api\models\UserInfo;


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

    //新增员工时，按手机号查询用户
    public function actionSearch()
    {
        parent::checkGet();
        $phone = Yii::$app->request->get('phone');

        $result = UserInfo::getInfoByPhone($phone, 1);

        return Utils::returnMsg(0, null, $result);
    }

    /**
	* 新增员工
    */
	public function actionCreate()
	{
		parent::checkPost();

		$data = Yii::$app->request->post();

        $user = UserInfo::getInfoByUID($data['uid']);
        if(!$user)
        {
            return Utils::returnMsg(1, "此用户不存在");
        }

		$model = new PlatformUser();
		if($model->isExists($data['uid'], $this->platform_id))
		{
			return Utils::returnMsg(1, "用户已存在");
		}

		$params = [
            'uid'    => $data['uid'],
            'platform_id'   => $this->platform_id,
            'permissions'=> $data['permissions']
        ];

        $platform = $model->addStaff($params);

		return Utils::returnMsg(0, "success");
	}

    //修改权限
	public function actionUpdatePermission()
	{
		parent::checkPost();
		$data = Yii::$app->request->post();

		$params = array(
			'platform_id'   => $this->platform_id,
			'uid' => $data['uid'],
			'permissions'=> $data['permissions']
		);

		PlatformUser::updatePermission($params);
	}

    public function actionRemove()
    {
        parent::checkGet();

        $uid = Yii::$app->request->get('uid');

        if(!$uid)
        {
            return Utils::returnMsg(1, '无效参数');
        }

        $user = PlatformUser::getUser($uid);
        if(!$user || $user->platform_id != $this->platform_id)
        {
            return Utils::returnMsg(1, '非法操作');
        }

        if(!PlatformUser::remove($uid, $this->platform_id))
        {
            return Utils::returnMsg(1, '删除失败');
        }

        return Utils::returnMsg(0, '删除成功');
    }
}