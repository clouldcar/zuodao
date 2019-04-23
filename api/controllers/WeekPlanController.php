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
        $team_id = $data['team_id'];
        $uid = Yii::$app->user->id;

        if(!$data['team_id'])
        {
            return Utils::returnMsg(1, '缺少必要参数');
        }

        //判断是否团队成员
        if(!TeamUser::hasUser($data['team_id'], $uid))
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $list = WeekPlan::getList($team_id);

        if($list)
        {
            foreach($list as &$item)
            {
                $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
            }
        }


        return Utils::returnMsg(0, null, $list);
    }

    public function actionCreate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();
        $plan_detail_id = $data['plan_detail_id'];
        $uid = Yii::$app->user->id;


        $detail = PlanDetail::info($plan_detail_id);

        if(!$detail)
        {
            return Utils::returnMsg(1, '信息不存在');
        }


        $plan_id = $detail['plan_id'];
        //检查是否自己的成就宣言
        $plan_info = Plan::info($plan_id);
        if(!$plan_info || $plan_info['uid'] != $uid)
        {
            return Utils::returnMsg(1, '非法操作');
        }

        //如果已存在，则不走添加逻辑
        if($week_plan_info = WeekPlan::infoByPlanId($plan_id))
        {
            $week_plan_id = $week_plan_info['id'];
        }
        else
        {
            $params = [
                'id' => Utils::createIncrementId(Utils::ID_TYPE_WEEK_PLAN),
                'uid' => $uid,
                'team_id' => $data['team_id'],
                'plan_id' => $plan_id,
                'plan_detail_id' => $plan_detail_id,
                'team_id' => $data['team_id'],
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date']
            ];
            if(!WeekPlan::add($params))
            {
                return Utils::returnMsg(1, '添加失败');
            }
            $week_plan_id = $params['id'];
        }

        $detail_params = [
            'week_plan_id' => $week_plan_id,
            'target' => $data['target'],
            'unit' => $detail['unit']
        ];

        WeekPlanDetail::add($detail_params);

        return Utils::returnMsg(0, '添加成功');
    }

    public function actionInfo()
    {
        parent::checkGet();
        $data = Yii::$app->request->get();
        $uid = Yii::$app->user->id;
        $result = [];

        //检查是否自己的周计划
        $info = WeekPlan::info($data['week_plan_id']);
        if(!$info || $info['uid'] != $uid)
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $user_info = UserInfo::getInfoByUID($info['uid']);
        $result['user'] = [
            'real_name' => $user_info->real_name,
            'gender' => ($user_info->gender == 'F') ? '女' : '男',
            'birthday' => date('Y年m月d日', strtotime($user_info->birthday)),
            'phone' => $user_info->phone,
            'avatar' => $user_info->avatar,
            'address' => $user_info->address,
        ];

        $res = [];

        $detail = WeekPlanDetail::getList($data['week_plan_id']);

        return Utils::returnMsg(0, null, $detail);
    }

    public function actionEdit()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();
        $week_plan_id = $data['week_plan_id'];
        $week_plan_detail_id = $data['week_plan_detail_id'];
        $uid = Yii::$app->user->id;

        //检查是否自己的周计划
        $info = WeekPlan::info($data['week_plan_id']);
        if(!$info || $info['uid'] != $uid)
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $params = [
            'target' => $data['target']
        ];

        WeekPlan::edit($params, $week_plan_id);

        $detail_params = [
            'result' => $data['result'],
            'completion_ratio' => $data['completion_ratio'],
            'note1' => $data['note1']
        ];
        WeekPlanDetail::edit($detail_params, $week_plan_detail_id);

        return Utils::returnMsg(0, '修改成功');
    }

    public function actionValidate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();
        $plan_detail_id = $data['plan_detail_id'];
        $week_plan_detail_id = $data['week_plan_detail_id'];
        $uid = Yii::$app->user->id;

        //检查是否自己团队的周计划
        $detail = WeekPlanDetail::info($week_plan_detail_id);
        if(!$detail)
        {
            return Utils::returnMsg(1, '信息不存在');
        }

        $info = WeekPlan::info($plan_detail_id);
        if(!$info)
        {
            return Utils::returnMsg(1, '非法操作');
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
            'node2' => $data['node2'],
            'check_time' => date('Y-m-d')
        ];
        WeekPlanDetail::edit($detail_params, $week_plan_detail_id);

        return Utils::returnMsg(0, '修改成功');
    }

}
