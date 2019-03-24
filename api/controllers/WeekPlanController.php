<?php

namespace api\controllers;

//周计划
class WeekPlanController extends \yii\web\Controller
{
    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionCreate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();
        $plan_id = $data['plan_id'];
        $uid = Yii::$app->user->id;

        //检查是否自己的成就宣言
        $detail = Plan::detail($plan_id);
        if(!$detail || $detail['uid'] != $uid)
        {
        	return Utils::returnMsg(1, '非法操作');
        }

        //$objective = '{"1":[{"subject":"建团队","target":"40","unit":"人","weight":40},{"subject":"增加业绩","target":"40","unit":"万","weight":40}],"2":[{"subject":"夫妻关系","target":"40","unit":"人","weight":40},{"subject":"亲子关系","target":"40","unit":"人","weight":40}],"3":[{"subject":"减肥","target":"40","unit":"人","weight":40},{"subject":"看病","target":"40","unit":"人","weight":40}]}';

        $params = [
            'id' => Utils::createIncrementId(Utils::ID_TYPE_WEEK_PLAN),
            'uid' => $uid,
            'team_id' => $data['team_id'],
            'plan_id' => $data['plan_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'content' => $data['content']
        ];

        if(!Plan::add($params))
        {
            return Utils::returnMsg(1, '添加失败');
        }

        return Utils::returnMsg(0, '添加成功');
    }

}
