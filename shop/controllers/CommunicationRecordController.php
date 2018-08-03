<?php

namespace shop\controllers;

use Yii;
use shop\models\CommunicationRecord;


class CommunicationRecordController extends BaseController
{
    public $modelClass = '\shop\models\CommunicationRecord';

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return $behaviors;
    }
    
    public function actionIndex()
    {
        return ['msg' => 'ok'];
    }

    public function actionAdd()
    {
        $model = new CommunicationRecord();
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            print_r($data);
            print_r($model);
        }

    }

}
