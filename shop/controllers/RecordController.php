<?php

namespace shop\controllers;

use shop\models\Record;
use Yii;


class RecordController extends BaseController
{
    public $modelClass = 'shop\models\Record';

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return $behaviors;
    }

    public function actionIndex()
    {
        return ['msg' => 'ok'];
    }

    /**
     * 添加记录
     * @return array
     */
    public function actionAdd()
    {
        $model = new Record();
        if (Yii::$app->request->isPost) {
            if ($model->load(Yii::$app->request->post(), '')) {
                if ($model->validate() && $model->save()) {
                    $this->returnData['code'] = 1;
                    $this->returnData['msg'] = '添加事件记录成功';
                } else {
                    $this->returnData['code'] = 0;
                    $this->returnData['msg'] = '添加事件记录失败';
                }
            }
        }

        return $this->returnData;
    }


}
