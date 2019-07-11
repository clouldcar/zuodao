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
            $uid = $user->id;
        }
        else
        {
            $uid = Utils::createIncrementId(Utils::ID_TYPE_USER);
        }

        $params = [
            'uid' => $uid,
            'real_name' => $data['real_name'],
            'phone' => $data['phone'],
            'platform_id' => $this->platform_id,
            'ctime' => date('Y-m-d H:i:s')
        ];

        if(isset($data['grade']) && !empty($data['grade']))
        {
            $params['grade'] = $data['grade'];
        }

        if(isset($data['team_id']) && $data['team_id'] !== '')
        {
            $params['team_id'] = $data['team_id'];
        }

        if(isset($data['birthday']) && !empty($data['birthday']))
        {
            $params['birthday'] = $data['birthday'];
        }

        if(isset($data['city']) && !empty($data['city']))
        {
            $params['city'] = $data['city'];
        }

        $model = new UserInfo();
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

        $info = UserInfo::getPlatformUserByUID($uid, $this->platform_id);
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
        $user_info = UserInfo::getPlatformUserByUID($data['uid'], $this->platform_id);
        
        if(!$user_info)
        {
            return Utils::returnMsg(1, '学员不存在');
        }

        if(isset($data['real_name']) && !empty($data['real_name']))
        {
            $user_info->real_name = $data['real_name'];
        }

        if(isset($data['phone']) && !empty($data['phone']))
        {
            $user_info->phone = $data['phone'];
        }

        if(isset($data['grade']) && !empty($data['grade']))
        {
            $user_info->grade = $data['grade'];
        }

        if(isset($data['team_id']) && $data['team_id'] !== '')
        {
            $user_info->team_id = $data['team_id'];
        }

        if(isset($data['birthday']) && !empty($data['birthday']))
        {
            $user_info->birthday = $data['birthday'];
        }

        if(isset($data['city']) && !empty($data['city']))
        {
            $user_info->city = $data['city'];
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