<?php
namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;
use api\models\Team;
use api\models\TeamUser;
use api\models\platform\PlatformTeamUser;

class TeamController extends BaseController
{
	public function init()
    {
        parent::init();
        parent::checkPlatformUser();
    }

    public function actionGlobal()
    {
        $result['category'] = [
            0 => '学员',
            1 => '导师',
            2 => '总教练',
            3 => '教练', 
            4 => '团长',
            5 => '助教'
        ];

        return Utils::returnMsg(0, null, $result);
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
            'uid'  => Yii::$app->user->id,
            'platform_id' => $this->platform_id,
            'start_date' => $data['start_date'],
            'end_date'   => $data['end_date']
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
        $page_size = isset($data['page_size']) ? $data['page_size'] : 20;

        $filter = [];
        if(isset($data['grade']) && !empty($data['grade']))
        {
            $filter['grade'] = $data['grade'];
        }

        $result = PlatformTeamUser::getUsers($this->platform_id, $data['team_id'], $filter, $page, $page_size);

        if($result['list']) foreach ($result['list'] as &$item) 
        {
            $item['identity_text'] = TeamUser::IDENTITY_TEXT[$item['identity']];
        }

        return Utils::returnMsg(0, null, $result);
    }


    //批量增加学员到团队
    public function actionAddUser()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();

        $grade = (isset($data['grade']) && $data['grade'] !== '') ? $data['grade'] : null;

        if(!$data['ids'])
        {
        	return Utils::returnMsg(1, '参数错误');
        }
        //检查是否为本平台学员
        if(!PlatformTeamUser::checkPlatformkUser($this->platform_id, $data['ids']))
        {
        	return Utils::returnMsg(1, '学员信息有误');
        }
        //检查是否本平台团队
        $info = Team::getInfoById($data['team_id']);
        if($info->platform_id != $this->platform_id)
        {
            return Utils::returnMsg(1, '团队信息有误');
        }

        PlatformTeamUser::addTeamUser($this->platform_id, $data['ids'], $data['team_id'], $grade);

        return Utils::returnMsg(0, 'success');
    }

    public function actionEditUser()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();
        if(!$data['ids'])
        {
            return Utils::returnMsg(1, '参数错误');
        }

        //检查是否为本平台学员
        if(!PlatformTeamUser::checkPlatformkUser($this->platform_id, $data['ids']))
        {
            return Utils::returnMsg(1, '学员信息有误');
        }

        //检查是否本平台团队
        $info = Team::getInfoById($data['team_id']);
        if($info->platform_id != $this->platform_id)
        {
            return Utils::returnMsg(1, '团队信息有误');
        }

        $params = ['identity' => $data['identity']];

        PlatformTeamUser::updateUser($this->platform_id, $data['team_id'], $data['ids'], $params);

        return Utils::returnMsg(0, 'success');
    }

    //批量删除团队成员
    public function actionRemoveUser()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();
        if(!$data['ids'])
        {
            return Utils::returnMsg(1, '参数错误');
        }
        //检查是否为本平台学员
        if(!PlatformTeamUser::checkPlatformkUser($this->platform_id, $data['ids']))
        {
            return Utils::returnMsg(1, '学员信息有误');
        }

        //检查是否本平台团队
        $info = Team::getInfoById($data['team_id']);
        if($info->platform_id != $this->platform_id)
        {
            return Utils::returnMsg(1, '团队信息有误');
        }

        $params = ['status' => 1];
        PlatformTeamUser::updateUser($this->platform_id, $data['team_id'], $data['ids'], $params);

        return Utils::returnMsg(0, 'success');
    }

    public function actionPlanList()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();
        $page = isset($data['page']) ? $data['page'] : 1;
        $page_size = 20;
        $team_id = $data['team_id'];
        $uid = Yii::$app->user->id;

        //检查是否团队成员
        if(!TeamUser::isTeamUser($team_id, $Uid))
        {
            return Utils::returnMsg(1, '非团队成员，无法查看');
        }

        $list = Plan::listByTeamId($team_id, $page, $page_size);

        return Utils::returnMsg(0, null, $list);
    }
}