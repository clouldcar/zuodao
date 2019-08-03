<?php

namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;
use api\models\User;
use api\models\UserInfo;
use api\models\Student;
use api\models\Students;

class StudentController extends BaseController
{

    public $modelClass = 'api\models\Student';

    public function init()
    {
        parent::init();
        parent::checkPlatformUser();
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

        $list = Students::getUsersByPlatform($this->platform_id, $page, $page_size);

        return Utils::returnMsg(0, null, $list);
    }

    /*
     * 添加学员
    */
    public function actionCreate()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();
        $uid = Yii::$app->user->id;

        //检查学员是否存在
        $user_info = Students::getUserByPhone($data['phone'], $this->platform_id);
        if($user_info)
        {
            return Utils::returnMsg(1, '学员已存在');
        }

        //查询是否是会员
        $user = User::findByUsername($data['phone']);
        if($user)
        {
            $student_uid = $user->id;
        }
        else
        {
            $student_uid = Utils::createIncrementId(Utils::ID_TYPE_USER);
        }

        $params = array_merge($data, [
            'uid' => $student_uid,
            'platform_id' => $this->platform_id,
            'real_name' => $data['real_name'],
            'phone' => $data['phone'],
            'ctime' => date('Y-m-d H:i:s')
        ]);


        $model = new Students();
        $model->setAttributes($params);

        if ($model->validate() && $model->save()) {
            return Utils::returnMsg(0, '添加学员成功');
        } else {
            return Utils::returnMsg(1, '添加学员失败');
        }
    }

    public function actionInfo()
    {
        parent::checkGet();

        $uid = Yii::$app->request->get('uid');

        $info = Students::getUserByUID($this->platform_id, $uid);
        if(!$info)
        {
            return Utils::returnMsg(1, '记录不存在');
        }

        return Utils::returnMsg(0, null, $info);
    }

    /*
     * 编辑学员
     */
    public function actionEdit()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();

        if(!$data['uid'])
        {
            return Utils::returnMsg(1, '参数有误');
        }

        //检查学员是否存在
        $user_info = Students::getUserByUID($this->platform_id, $data['uid']);
        
        if(!$user_info)
        {
            return Utils::returnMsg(1, '学员不存在');
        }

        if(!$user_info->validate()) 
        {
            return Utils::returnMsg(1, '修改失败');
        }

        if(!$user_info->save()) 
        {
            return Utils::returnMsg(1, '修改失败');
        } 
        
        return Utils::returnMsg(0, '修改成功');  
    }

    /**
     * 删除学员
     */
    public function actionRemove()
    {
        //判断批量删除
        $data = Yii::$app->request->post();

        $info = Students::getUserByUID($this->platform_id, $uid);
        if(!$info)
        {
            return Utils::returnMsg(1, '记录不存在');
        }

        $info->status = 1;

        if(!$info->save()) 
        {
            return Utils::returnMsg(1, '删除学员成功');
        } 
        
        return Utils::returnMsg(0, '删除学员成功');  
    }

    public function actionSearch()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();

        if(!isset($data['key']) && !$data['key'])
        {
            return Utils::returnMsg(1, '参数有误');
        }

        $list = Students::search($this->platform_id, $data['key']);

        return Utils::returnMsg(0, null, $list);
    }

}