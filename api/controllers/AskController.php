<?php

namespace api\controllers;

use Yii;
use api\models\Question;

class AskController extends BaseController {
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

    public function actionCreate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();

        if(empty($data['platform_id']) || empty($data['name']) || empty($data['phone']))
        {
            return Utils::returnMsg(1, '缺少必要参数');
        }

        $model = new Ask();
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
