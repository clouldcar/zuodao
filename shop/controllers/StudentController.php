<?php

namespace shop\controllers;

use shop\models\User;
use Yii;
use shop\models\Student;

class StudentController extends BaseController
{

    public $modelClass = 'shop\models\Student';

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return $behaviors;
    }

    public function actionIndex()
    {
        $condition = ['status' => Student::STATUS_ACTIVE];
        return User::findAll($condition);
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
                        $this->returnData['msg'] = '添加学员成功';
                    } else {
                        $this->returnData['code'] = 0;
                        $this->returnData['msg'] = 'add 添加学员失败';
                    }
                }

            } else {
                $this->returnData['code'] = 804;
                $this->returnData['msg'] = $userModel->getErrors();

            }

            return $this->returnData;
        }
    }

    /*
     * 编辑学员
     */
    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            $data = $request->post();
            $model = Student::findOne([
                'stu_uid' => $data['stu_uid'],
                'status' => Student::STATUS_ACTIVE,
            ]);
            if (!$model) {
                return $this->returnData = [
                    'code' => 805,
                    'msg' => '学员不存在',
                ];
            }
            $data['updated_at'] = date('Y-m-d H:i:s', time());
            if ($model->load($data, '') && $model->validate()) {
                $model->setAttributes($data);
                if ($model->save()) {
                    $userModel = User::findOne([
                        'id' => $data['stu_uid'],
                        'status' => User::STATUS_ACTIVE,
                    ]);
                    if (!$userModel) {
                        return $this->returnData = [
                            'code' => 805,
                            'msg' => '学员不存在',
                        ];
                    }
                    $data['username'] = $data['stu_name'];
                    unset($data['stu_name']);
                    $userModel->setAttributes($data);
                    $userModel->save();
                    return $this->returnData = [
                            'code' => 1,
                            'msg' => '编辑学员成功',
                    ];                
                } else {
                    return $this->returnData = [
                            'code' => 0,
                            'msg' => '编辑学员失败',
                    ];
                }   
            }

        }

    }

    /**
     * 删除学员
     */
    public function actionDelete()
    {
        //判断批量删除
        $ids = Yii::$app->request->get('stu_uid', 0);
        $ids = implode(',', array_unique((array)$ids));
        if (empty($ids)) {
            return $this->returnData = [
                'code' => 802,
                'msg' => '请选择要删除的数据',
            ];
        }
        $_where = 'id in (' . $ids . ')';
        $where = 'stu_uid in (' . $ids . ')';
        if ((new User())->updateUserStatus($_where) && (new Student())->updateStudentStatus($where)) {
            return $this->returnData = [
                'code' => 1,
                'msg' => '删除学员成功'
            ];
        } else {
            return $this->returnData = [
                'code' => 0,
                'msg' => '删除学员失败',
            ];
        }
    }

}