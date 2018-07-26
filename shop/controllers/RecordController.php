<?php

namespace shop\controllers;

use shop\models\Record;
use Yii;


class RecordController extends BaseController
{
    public $modelClass = 'shop\models\Record';

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
                    $this->returnData['msg'] = 'add record success';
                } else {
                    $this->returnData['code'] = 0;
                    $this->returnData['msg'] = 'add record fail';
                }
            }
        }

        return $this->returnData;
    }


}
