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



    public function actionGlobal() 
    {
        $uid = Yii::$app->user->id;

        // $identicon = new \common\helpers\Identicon();
        // print_r($identicon->createUserImg($uid));exit;

        $user_info = UserInfo::getInfoByUID($uid);
        $result['user'] = [
            'real_name' => $user_info->real_name,
            'gender' => ($user_info->gender == 'F') ? '女' : '男',
            'birthday' => date('Y年m月d日', strtotime($user_info->birthday)),
            'phone' => $user_info->phone,
            'avatar' => $user_info->avatar,
            'address' => $user_info->address,
            'work' => '',
            'weixin' => ''
        ];

        return Utils::returnMsg(0, null, $result);
    }

    public function actionIndex()
    {
        parent::checkGet();
        $data = Yii::$app->request->get();
        $page = isset($data['page']) ? $data['page'] : 1;
        $page_size = 20;

        $uid = Yii::$app->user->id;

        if(isset($data['team_id']) && !$data['team_id'])
        {
            return Utils::returnMsg(1, '缺少必要参数');
        }

        if(isset($data['team_id']))
        {
            //判断是否团队成员
            if(!TeamUser::hasUser($data['team_id'], $uid))
            {
                return Utils::returnMsg(1, '非法操作，您不是此团队成员');
            }

            $list = Plan::listByTeamId($data['team_id'], $page);
        }
        else
        {
            $list = Plan::listByUid($uid, $page);
        }


        return Utils::returnMsg(0, null, $list);
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
                'inspire' => $info['inspire'],
                'social_service' => $info['social_service'],
                'personal' => 0,
                'service' => 0,
                'impel' => 0,
            ]
        ];

        return Utils::returnMsg(0, null, $result);
    }

    public function actionCreate()
    {
        parent::checkPost();

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

        $params = [
            'id' => Utils::createIncrementId(Utils::ID_TYPE_PLAN),
            'uid' => $uid,
            'team_id' => $data['team_id'],
            'basics' => json_encode($decode['user']),
            'detail' => json_encode($detail['list']),
            'team_ideal' => $detail['team_ideal'],
            'vow' => $detail['vow'],
            'idea' => $detail['idea'],
            'inspire' => $detail['inspire'],
            'social_service' => $detail['social_service']
        ];

        if(!Plan::add($params))
        {
            return Utils::returnMsg(1, '添加失败');
        }

        return Utils::returnMsg(0, '添加成功');
    }
}
