<?php

namespace api\controllers;

use Yii;
use common\helpers\Utils;
use api\models\TeamUser;
use api\models\UserInfo;
use api\models\Plan;
use api\models\PlanDetail;
use api\models\WeekPlan;
use api\models\WeekPlanDetail;

class WeekPlanController extends BaseController
{
    public function init()
    {
        parent::init();
        parent::checkLogin();
    }

    public function actionIndex()
    {
        parent::checkGet();
        $data = Yii::$app->request->get();
        $uid = Yii::$app->user->id;

        $page = isset($data['page']) ? $data['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }

        if(isset($data['team_id']) && !$data['team_id'])
        {
            return Utils::returnMsg(1, '缺少必要参数');
        }

        if(isset($data['team_id']))
        {
            //判断是否团队成员
            if(!TeamUser::hasUser($data['team_id'], $uid))
            {
                return Utils::returnMsg(1, '非法操作');
            }

            $list = WeekPlan::getList($data['team_id'], $page);
        }
        else
        {
            $list = WeekPlan::getListByUID($uid, $page);
        }

        return Utils::returnMsg(0, null, $list);
    }

    public function actionCreate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();
        $plan_id = $data['plan_id'];
        $uid = Yii::$app->user->id;

        if(!$data['start_time'] || !$data['plan_id'] || !$data['data'])
        {
            return Utils::returnMsg(1, '参数有误');
        }

        //检查是否自己的成就宣言
        $plan_info = Plan::info($plan_id);
        if(!$plan_info || $plan_info['uid'] != $uid)
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $params = [
            'id' => Utils::createIncrementId(Utils::ID_TYPE_WEEK_PLAN),
            'uid' => $uid,
            'plan_id' => $plan_id,
            'team_id' => $plan_info['team_id'],
            'start_time' => $data['start_time'][0],
            'end_time' => $data['start_time'][1],
            'detail' => $data['data']
        ];
        if(!WeekPlan::add($params))
        {
            return Utils::returnMsg(1, '添加失败');
        }

        return Utils::returnMsg(0, '添加成功');
    }

    public function actionInfo()
    {
        parent::checkGet();
        $data = Yii::$app->request->get();
        $uid = Yii::$app->user->id;
        $result = [];

        if(!isset($data['week_plan_id']) || !$data['week_plan_id'])
        {
            return Utils::returnMsg(1, '参数有误');
        }

        //检查是否自己的周计划
        $info = WeekPlan::info($data['week_plan_id']);

        if(!$info)
        {
            return Utils::returnMsg(1, '信息不存在');
        }

        //判断是否团队成员
        if(!TeamUser::hasUser($info['team_id'], $uid))
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $info['user'] = UserInfo::getInfoByUID($info['uid'], 1);
        $info['owner'] = ($info['uid'] == $uid) ? true : false;

        $check_data = json_decode($info['check_data'], true);
        if($check_data)
        {
            foreach ($check_data as &$item) {
                $item['user'] = UserInfo::getInfoByUID($item['check_uid'], 1);
            }
        }

        $info['check_data'] = $check_data;

        return Utils::returnMsg(0, null, $info);
    }

    public function actionEdit()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();
        $week_plan_id = $data['week_plan_id'];
        $uid = Yii::$app->user->id;

        //检查是否自己的周计划
        $info = WeekPlan::info($week_plan_id);
        if(!$info || $info['uid'] != $uid)
        {
            return Utils::returnMsg(1, '信息不存在');
        }

        $params = [
            'score' => $data['score1'],
            'note' => $data['note'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        WeekPlan::edit($params, $week_plan_id);

        $detail_params = [
            'score' => $data['score2']
        ];
        Plan::edit($detail_params, $info['plan_id']);

        return Utils::returnMsg(0, '修改成功');
    }

    public function actionValidate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();
        $week_plan_id = $data['week_plan_id'];
        $uid = Yii::$app->user->id;

        //检查是否自己团队的周计划
        $info = WeekPlan::info($week_plan_id);
        if(!$info)
        {
            return Utils::returnMsg(1, '信息不存在');
        }

        if($info['uid'] == $uid)
        {
            return Utils::returnMsg(1, '不可检视自己的信息');
        }

        //判断是否团队成员
        if(!TeamUser::hasUser($info['team_id'], $uid))
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $detail_params = [
            'check_uid' => $uid,
            'note' => $data['note'],
            'pass' => $data['pass'],
            'check_time' => date('Y-m-d')
        ];

        //检查是否已检视，如已检视，则覆盖；未检视，则追加
        $check_data = [];
        if($info['check_data']) 
        {
            $check_data = json_decode($info['check_data'], true);
        }

        $is_set = 0;
        if($check_data) foreach($check_data as &$item)
        {
            if($item['check_uid'] == $uid)
            {
                $item = $detail_params;
                $is_set = 1;
                break;
            }
        }
        if(!$is_set)
        {
            $check_data[] = $detail_params;
        }

        $params = [
            'check_data' => json_encode($check_data)
        ];
        WeekPlan::edit($params, $week_plan_id);

        return Utils::returnMsg(0, '修改成功');
    }

}
