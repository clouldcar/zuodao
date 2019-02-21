<?php
namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;
use api\models\Team;
use api\models\UserInfo;

class TeamController extends BaseController
{
	public function init()
    {
        parent::init();
        parent::checkPlatformUser();
    }

	public function actionIndex()
	{
		parent::checkGet();

		$data = Yii::$app->request->get();
		$page = isset($data['page']) ? $data['page'] : 1;
    	$page_size = 20;

    	$result = Team::getTeamList($this->platform_id, $page, $page_size);

    	return Utils::returnMsg(0, null, $result);
	}

    public function actionCreate()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();
        //检查是否存在
        if($info = Team::getInfoByName($data['name'], $this->platform_id))
        {
            return Utils::returnMsg(1, '团队名称已存在!');
        }

        $params = array(
            'name' => $data['name'],
            'uid' => Yii::$app->user->id,
            'platform_id' => $this->platform_id,
            'start_date' => $data['start_date']
        );

        $model = new Team();

        $result = $model->teamCreate($params);
        if(!$result)
        {
            return Utils::returnMsg(1, '创建失败');
        }

        return Utils::returnMsg(0, '创建成功');
    }

    public function actionEdit()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();

        //检查是否存在
        $info = Team::getInfoByName($data['name'], $this->platform_id);
        if($info && $info->id != $data['team_id'])
        {
            return Utils::returnMsg(1, '团队名称已存在!');
        }

        $params = array(
            'name' => $data['name'],
            'start_date' => $data['start_date']
        );

        $model = new Team();

        $result = $model->teamEditor($params, $data['team_id']);
        if(!$result)
        {
            return Utils::returnMsg(1, '修改失败');
        }

        return Utils::returnMsg(0, '修改成功');
    }

    public function actionInfo()
    {
        parent::checkGet();

        $team_id = Yii::$app->request->get('team_id');

        $info = Team::getInfoById($team_id);
        if(!$info || $info['platform_id'] != $this->platform_id)
        {
            return Utils::returnMsg(1, '记录不存在');
        }

        return Utils::returnMsg(0, null, $info);
    }

    public function actionUsers()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();
        $page = isset($data['page']) ? $data['page'] : 1;
        $page_size = 20;



        $result = UserInfo::getTeamUsers($data['team_id'], $page, $page_size);

        return Utils::returnMsg(0, null, $result);
    }


    //批量增加学员到团队
    public function actionAddUser()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();
        if(!$data['ids'])
        {
        	return Utils::returnMsg(1, '参数错误');
        }
        //检查是否为本平台学员
        if(!UserInfo::checkPlatformkUser($this->platform_id, $data['ids']))
        {
        	return Utils::returnMsg(1, '学员信息有误');
        }

        UserInfo::updateTeamInfo($data['ids'], $this->platform_id, $data['team_id'], $data['grade']);
        

        return Utils::returnMsg(0, 'success');
    }
}