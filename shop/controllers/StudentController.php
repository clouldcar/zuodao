<?php

namespace shop\controllers;

use shop\models\User;
use Yii;
use shop\models\Student;

class StudentController extends BaseController
{

    public $modelClass = 'shop\models\Student';

    public function actionIndex()
    {
        return ['msg' => 'ok'];
    }

    /*
     * 添加学员
    */
    public function actionAdd()
    {
        $model = new Student();
        $userModel = new User();
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $model->setAttributes($data);
            $model->stu_qq = intval($data['stu_qq']);
            $stu_uid = createIncrementId();
            $model->stu_uid = (string)$stu_uid;

            $userModel->id = (string)$stu_uid;
            $userModel->type = 1;
            $userModel->username = $data['stu_name'];
            $userModel->setAttributes($data);

            if ($userModel->validate()) {
                if ($userModel->save()) {
                    if ($model->validate() && $model->save()) {
                        $this->returnData['code'] = 1;
                        $this->returnData['msg'] = 'add student success';
                    } else {
                        $this->returnData['code'] = 0;
                        $this->returnData['msg'] = 'add student fail';
                    }
                }

            } else {
                $this->returnData['code'] = 0;
                $this->returnData['msg'] = $userModel->getErrors();

            }

            return $this->returnData;
        }
    }

    /*
     * 编辑学员
     */
    public function actionUpdate($stu_uid)
    {
        $model = Student::findOne(['stu_uid' => $stu_uid]);
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            $data['updated_at'] = date('Y-m-d H:i:s');
            $model->setAttributes($data);


        }
    }

}