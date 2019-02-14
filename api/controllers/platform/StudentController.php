<?php

namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;
use api\models\User;
use api\models\UserInfo;
use api\models\Student;

class StudentController extends BaseController
{

    public $modelClass = 'api\models\Student';
    public $platform_id;

    public function init()
    {
        parent::init();
        $this->platform_id = Yii::$app->session['platform_id'];

        if(!$this->platform_id)
        {
            return Utils::returnMsg(1, '参数错误，请检查平台权限');
        }
    }

    /**
     * 显示学员
     * @return [type] [description]
     */
    public function actionIndex()
    {   
        parent::checkGet();

        $data = Yii::$app->request->get();

        $page = isset($data['page']) ? $data['page'] : 1;
        if($page < 1)
        {
            $page = 1;
        }
        $page_size = 20;

        $list = UserInfo::getPlatformUsers($this->platform_id, $page, $page_size);

        return Utils::returnMsg(0, null, $list);
    }

    /*
     * 添加学员
    */
    public function actionCreate()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();

        //检查学员是否存在
        $user_info = UserInfo::getPlatformUserByPhone($data['phone'], $this->platform_id);
        if($user_info)
        {
            return Utils::returnMsg(1, '学员已存在');
        }

        //查询是否是会员
        $user = User::findByUsername($data['phone']);
        if($user)
        {
            $data['uid'] = $user->id;
        }

        $data['platform_id'] = $this->platform_id;
        $data['ctime'] = date('Y-m-d H:i:s');

        $model = new UserInfo();
        $model->setAttributes($data);

        if ($model->validate() && $model->save()) {
            return Utils::returnMsg(0, '添加学员成功');
        } else {
            return Utils::returnMsg(1, '添加学员失败');
        }
    }

    /*
     * 编辑学员
     */
    public function actionEdit()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();

        //检查学员是否存在
        $user_info = UserInfo::getPlatformUserByUID($data['uid'], $this->platform_id);
        if(!$user_info)
        {
            return Utils::returnMsg(1, '学员不存在');
        }

        $data['platform_id'] = $this->platform_id;

        $model = new UserInfo();
        if ($model->load($data) && $model->validate()) 
        {
            $model->setAttributes($data);
            if ($model->save()) 
            {
                return returnMsg(0, '修改成功');             
            } else 
            {
                return Utils::returnMsg(1, '修改失败');
            }   
        }
        
        return Utils::returnMsg(1, '修改失败');
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