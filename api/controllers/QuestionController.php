<?php

namespace api\controllers;

use Yii;
use api\models\Ask;
use api\models\UserInfo;
use common\helpers\Utils;

class AskController extends BaseController
{
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($this->enableCsrfValidation) {
                Yii::$app->getRequest()->getCsrfToken(true);
            }
            return true;
        }

        return false;
    }

    public function actionIndex()
    {
        return $this->render('index');
    }

    public function actionInfo()
    {
        parent::checkGet();
        $data = Yii::$app->request->get();

        if(empty($data['id']))
        {
            return Utils::returnMsg(1, '缺少必要参数');
        }

    }

    public function actionCreate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();

        if(empty($data['platform_id']) || empty($data['name']) || empty($data['phone']))
        {
            return Utils::returnMsg(1, '缺少必要参数');
        }

        if(Ask::checkInfo($data['platform_id'], $data['phone']))
        {
            return Utils::returnMsg(1, '您已经填写过此问卷');
        }

        //uid
        $user = UserInfo::getInfoByPhone($data['phone']);

        $model = new Ask();
        if($user) {
            $model->uid = $user->uid;
        }
        $model->platform_id = $data['platform_id'];
        $model->name = $data['name'];
        $model->phone = $data['phone'];
        unset($data['platform_id']);
        unset($data['name']);
        unset($data['phone']);
        $model->content = json_encode($data);
        $model->ctime = date('Y-m-d H:i:s');
        $model->isNewRecord = true;
        if(!$model->save())
        {
            return Utils::returnMsg(1, '创建失败，请重试');
        }


        return Utils::returnMsg(0, '创建成功');
    }

}
