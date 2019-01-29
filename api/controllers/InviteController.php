<?php
namespace api\controllers;

use Yii;

class Invite extends BaseController
{
	public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ], $behaviors);
    }

	public function actionTeam()
	{
		$teamId = Yii::$app->request->get('id');
		$uid = Yii::$app->identity->id;
	}
}