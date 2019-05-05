<?php
namespace api\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use common\helpers\Utils;
use api\models\Team;
use api\models\TeamUser;
use api\models\Plan;
use api\models\PlanDetail;
use api\models\UserInfo;

/**
* 成就宣言
*/
class PlanController extends BaseController
{
    private $type = [
        1 => '事业',
        2 => '家庭',
        3 => '健康',
        4 => '学习',
        5 => '人际关系'
    ];

    public function init()
    {
        parent::init();
        parent::checkLogin();
    }



    /*
    public function actionGlobal() 
    {
        $uid = Yii::$app->user->id;

        $user_info = UserInfo::getInfoByUID($uid);
        $result['user'] = [
            'real_name' => $user_info->real_name,
            'gender' => ($user_info->gender == 'F') ? '女' : '男',
            'birthday' => date('Y年m月d日', strtotime($user_info->birthday)),
            'phone' => $user_info->phone,
            'avatar' => $user_info->avatar,
            'address' => $user_info->address,
            'work' => $user_info->work,
            'weixin' =>
        ];
    }
    */

    public function actionIndex()
    {

    }


    public function actionInfo()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();
        $id = $data['plan_id'];
        $uid = Yii::$app->user->id;
        $result = [];

        $info = Plan::info($id);

        //鉴权
        if($info['uid'] != $uid)
        {
            return Utils::returnMsg(1, '内容不存在');
        }
        
        //判断是否团队成员
        if(!TeamUser::hasUser($info['team_id'], $uid))
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $basics = json_decode($info['basics'], true);
        // if($basics['birthday'])
        // {
        //     $basics['birthday'] = date('Y年m月d日', strtotime($basics['birthday']));
        // }
        $result = [
            'id' => $id,
            'user' => $basics,
            'detail' => [
                'list' => json_decode($info['detail'], true),
                'team_ideal' => $info['team_ideal'],
                'vow' => $info['vow'],
                'idea' => $info['idea'],
                'personal' => 0,
                'service' => 0,
                'impel' => 0,
            ]
        ];


        /*
        $user_info = UserInfo::getInfoByUID($info['uid']);
        $result['user'] = [
            'real_name' => $user_info->real_name,
            'gender' => ($user_info->gender == 'F') ? '女' : '男',
            'birthday' => date('Y年m月d日', strtotime($user_info->birthday)),
            'phone' => $user_info->phone,
            'avatar' => $user_info->avatar,
            'address' => $user_info->address,
        ];

        //团队立场
        $teamModel = new Team();
        $team_info = $teamModel->teamInfo($team_id);
        $result['detail']['team_ideal'] = $team_info['ideal'];
        $result['detail']['vow'] = $user_info['vow'];
        $result['detail']['idea'] = $user_info['idea'];

        //成就宣言
        $plan_detail = PlanDetail::getList($id);

        //按类型排序
        ArrayHelper::multisort($plan_detail, 'sub_type');

        $arr = [];
        $inspire = [];
        $social_service = [];
        foreach($plan_detail as $v)
        {
            //个人成就
            if($v['type'] == 1)
            {
                $type_id = $v['sub_type'];
                if(isset($arr[$type_id]))
                {
                    $arr[$type_id]['detail'][] = $v;
                }
                else
                {
                    $arr[$type_id] = [
                        'name' => $this->type[$type_id],
                        'detail' => [$v]
                    ];
                }
            }

            //感召
            if($v['type'] == 2)
            {
                $inspire = $v;
            }

            //社服
            if($v['type'] == 3)
            {
                $social_service = $v;
            }
        }
        $result['detail']['plan'] = $arr;
        $result['detail']['inspire'] = $inspire;
        $result['detail']['social_service'] = $social_service;
        */

        

        return Utils::returnMsg(0, null, $result);
    }

    public function actionCreate()
    {
        parent::checkPost();
        $model = new Plan();
        $model2 = new PlanDetail();

        $data = Yii::$app->request->post();
        $uid = Yii::$app->user->id;

        if(!$data['team_id'] || empty($data['data']))
        {
            return Utils::returnMsg(1, '缺少必要参数');
        }


        //判断是否团队成员
        if(!TeamUser::hasUser($data['team_id'], $uid))
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $decode = json_decode($data['data'], true);
        $detail = $decode['detail'];

        //地址
        $address = [];
        if($decode['user']['city'])
        {
            foreach($decode['user']['city'] as $city)
            {
                $address[] = $city['name'];
            }
        }

        $decode['user']['address'] = implode(" ", $address);
        unset($decode['user']['city']);

        $params = [
            'id' => Utils::createIncrementId(Utils::ID_TYPE_PLAN),
            'uid' => $uid,
            'team_id' => $data['team_id'],
            'basics' => json_encode($decode['user']),
            'detail' => json_encode($detail['list']),
            'team_ideal' => $detail['team_ideal'],
            'vow' => $detail['vow'],
            'idea' => $detail['idea'],
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
        /*
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
        */

        return Utils::returnMsg(0, '添加成功');
    }
}
