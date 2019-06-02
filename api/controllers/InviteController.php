<?php
namespace api\controllers;

use Yii;
use common\helpers\Utils;
use api\models\Team;
use api\models\TeamUser;

class InviteController extends BaseController
{
    public function init()
    {
        parent::init();

        parent::checkLogin();
    }

    public function actionCreateTeamUrl()
    {
        $data = Yii::$app->request->get();
        $uid = Yii::$app->user->id;
        if(!isset($data['team_id']))
        {
            Utils::returnMsg(1, '参数有误');
        }

        //团队表
        $model = new Team();
        $teamInfo = $model->teamInfo($data['team_id']);
        if(!$teamInfo)
        {
            //TODO 404处理
            return Utils::redirectMsg('404');
        }

        //判断是否团队成员
        if(!TeamUser::hasUser($data['team_id'], $uid))
        {
            return Utils::returnMsg(1, '非法操作');
        }

        //3天有效期
        $invite_code = $teamInfo['invite_code'];
        $now = time();
        if(empty($teamInfo['invite_time']) || $now - strtotime($teamInfo['invite_time']) > 86400*3)
        {
            $invite_code = md5(rand(1, 1000000));
            $params = [
                'invite_code' => $invite_code,
                'invite_time' => date('Y-m-d H:i:s')
            ];

            $model->teamEditor($params, $data['team_id']);
        }


        return Utils::returnMsg(0, null, $invite_code);
    }

    //邀请加入团队
    public function actionInTeam()
    {
        $data = Yii::$app->request->get();
        $uid = Yii::$app->identity->id;
        if(!isset($data['team_id']) || !isset($data['code']))
        {
            Utils::returnMsg(1, '参数有误');
        }

        //团队表
        $model = new Team();
        $teamInfo = $model->teamInfo($data['team_id']);
        if(!$teamInfo)
        {
            //TODO 404处理
            return Utils::redirectMsg('404');
        }

        if($data['code'] != $teamInfo['invite_code'])
        {
            Utils::returnMsg(1, '邀请码不正确');
        }


        $member = array(
            'team_id' => $data['team_id'],
            'uid' => $uid,
            'permissions' => TeamUser::LEVEL_STUDENT,
            'status' => TeamUser::STATUS_NORMAL
        );

        TeamUser::addMember($member);

        return Utils::returnMsg(0, '成功加入团队');
    }
}