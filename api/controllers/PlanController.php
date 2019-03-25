<?php
namespace api\controllers;

use Yii;
use common\helpers\Utils;
use api\models\TeamUser;
use api\models\Plan;
use api\models\PlanDetail;

/**
* 成就宣言
*/
class PlanController extends baseController
{

    public function init()
    {
        parent::init();
        parent::checkLogin();
    }

    public function actionIndex()
    {

    }


    public function actionInfo()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();
        $id = $data['plan_id'];
        $team_id = $data['team_id'];
        $uid = Yii::$app->user->id;

        $info = Plan::info($id);

        //鉴权
        if($info['uid'] != $uid && $info['team_id'] != $team_id)
        {
            return Utils::returnMsg(1, '内容不存在');
        }

        $info['detail'] = PlanDetail::getList($id);

        return Utils::returnMsg(0, null, $info);
    }

    public function actionCreate()
    {
        parent::checkPost();
        $model = new \api\models\Plan();
        $model2 = new \api\models\PlanDetail();

        $data = Yii::$app->request->post();
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

        $params = [
            'id' => Utils::createIncrementId(Utils::ID_TYPE_PLAN),
            'uid' => $uid,
            'team_id' => $data['team_id'],
            'title' => $data['title']
        ];

        if(!Plan::add($params))
        {
            return Utils::returnMsg(1, '添加失败');
        }

        /*
        $data['name'][个人成就][事业][0]
        $data['name'][个人成就][事业][1]
        $data['name'][个人成就][家庭][0]
        $data['name'][感召][0][0]
        $data['name'][社服][0][0]
        */
        foreach($data['name'] as $type => $list)
        {
            foreach($list as $sub_type => $item)
            {
                for($i = 0; $i < count($item); $i++)
                {
                    $plan_detail = [
                        'plan_id' => $params['id'],
                        'type' => $type,
                        'sub_type' => $sub_type,
                        'name' => $data['name'][$type][$sub_type][$i],
                        'target' => $data['target'][$type][$sub_type][$i],
                        'unit' => $data['unit'][$type][$sub_type][$i],
                        'weight' => $data['weight'][$type][$sub_type][$i]
                    ];


                    PlanDetail::add($plan_detail);
                }
            }
        }

        return Utils::returnMsg(0, '添加成功');
    }
}