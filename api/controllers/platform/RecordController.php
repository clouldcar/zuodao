<?php

namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;

use api\models\CommunicationRecord;
use api\models\UserInfo;

/**
* 学员沟通记录
*/
class RecordController extends BaseController
{   
    public $modelClass = '\api\models\CommunicationRecord';


    public function init()
    {
        parent::init();
        parent::checkPlatformUser();
    }

    /**
     * 沟通记录列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();
        $uid = isset($data['uid']) ? $data['uid'] : 0;
        $page = isset($data['page']) ? $data['page'] : 1;
        $page_size = 20;
        $platform_id = $this->platform_id;

        $data = CommunicationRecord::getList($this->platform_id, $uid, $page, $page_size);

        if($data['list'])
        {
            foreach($data['list'] as &$item)
            {
                $item['staff'] = UserInfo::getInfoByUID($item['staff_uid'], 1);
                $item['student'] = UserInfo::getInfoByUID($item['uid'], 1);
            }
        }

        return Utils::returnMsg(0, null, $data);
    }


    /**
     * 添加沟通记录
     * @return [type] [description]
     */
    public function actionCreate()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();

        //鉴权
        $user = UserInfo::getPlatformUserByUID($data['uid'], $this->platform_id);
        if(!$user)
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $data['platform_id'] = $this->platform_id;
        //员工ID
        $data['staff_uid'] = Yii::$app->user->id;

        $model = new CommunicationRecord();
        $model->setAttributes($data);

        if (!$model->validate()) 
        {
            return Utils::returnMsg(1, '参数有误');
        }

        if (!$model->save())
        {
            return Utils::returnMsg(0, '添加失败');
        }

        return Utils::returnMsg(0, '添加成功');
    }


     /*
     * 编辑沟通记录
     */
    public function actionEdit()
    {
        parent::checkPost();
        $data = Yii::$app->request->post();

        $model = CommunicationRecord::info($data['id']);

        if (!$model) 
        {
            return Utils::returnMsg(1, '沟通记录不存在');
        }

        //鉴权
        $user = UserInfo::getPlatformUserByUID($data['uid'], $this->platform_id);
        if(!$user)
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $data['updated_at'] = date('Y-m-d H:i:s', time());
        $model->setAttributes($data);
        if (!$model->validate()) 
        {
            return Utils::returnMsg(1, '参数有误');
        }

        if (!$model->save())
        {
            return Utils::returnMsg(0, '编辑失败');
        }

        return Utils::returnMsg(0, '编辑成功');
    }

    /**
     * 删除沟通记录
     */
    public function actionDelete()
    {
        //判断批量删除
        $ids = Yii::$app->request->get('id', 0);
        $ids = implode(',', array_unique((array)$ids));
        if (empty($ids)) {
            return $this->returnData = [
                'code' => 802,
                'msg' => '请选择要删除的数据',
            ];
        }
        $_where = 'id in (' . $ids . ')';
        
        if ((new CommunicationRecord())->updateCreordStatus($_where)) {
            return $this->returnData = [
                'code' => 1,
                'msg' => '删除沟通记录成功'
            ];
        } else {
            return $this->returnData = [
                'code' => 0,
                'msg' => '删除沟通记录失败',
            ];
        }
    }

}
