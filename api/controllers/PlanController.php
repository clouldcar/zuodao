<?php
namespace api\controllers;

use Yii;
use common\helpers\Utils;
use api\models\Plan;

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

		$detail = Plan::detail($id);

		//鉴权
		if($detail['uid'] != $uid && $detail['team_id'] != $team_id)
		{
			return Utils::returnMsg(1, '内容不存在');
		}

		return Utils::returnMsg(0, null, $detail);
	}

	public function actionCreate()
	{
		parent::checkPost();
		$data = Yii::$app->request->post();

		$uid = Yii::$app->user->id;

		//$objective = '{"1":[{"subject":"建团队","target":"40","unit":"人","weight":40},{"subject":"增加业绩","target":"40","unit":"万","weight":40}],"2":[{"subject":"夫妻关系","target":"40","unit":"人","weight":40},{"subject":"亲子关系","target":"40","unit":"人","weight":40}],"3":[{"subject":"减肥","target":"40","unit":"人","weight":40},{"subject":"看病","target":"40","unit":"人","weight":40}]}';

		$params = [
			'id' => Utils::createIncrementId(Utils::ID_TYPE_PLAN),
			'title' => $data['title'],
			'uid' => $uid,
			'team_id' => $data['team_id'],
			'objective' => $data['objective'],
			'inspire' => 3,
			'social_services' => 4
		];

		if(!Plan::add($params))
		{
			return Utils::returnMsg(1, '添加失败');
		}

		return Utils::returnMsg(0, '添加成功');
	}
}