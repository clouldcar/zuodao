<?php
namespace api\controllers\platform;
use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;
use api\models\User;
use api\models\Temporary;

//外部人员管理
class TemporaryController extends BaseController
{
    public function init()
    {
        parent::init();
        parent::checkPlatformUser();
    }

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

        $list = Temporary::getUsers($this->platform_id, $page, $page_size);
        if($list['list']) foreach($list['list'] as &$item)
        {
            $item = $this->completion($item);
        }

        return Utils::returnMsg(0, null, $list);
    }

    public function actionCreate()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();

        $data['platform_id'] = $this->platform_id;
        $data['ctime'] = date('Y-m-d H:i:s');

        if(is_array($data['skilful']))
        {
            $data['skilful'] = implode(',', $data['skilful']);
        }
            

        // if(isset($data['identity']) && $data['identity'])
        // {
        //     $data['identity'] = implode(',', $data['identity']);
        // }
        //查询是否是会员
        $user = User::findByUsername($data['phone']);
        if($user)
        {
            $data['uid'] = $user->id;
        }
        else
        {
            $data['uid'] = Utils::createIncrementId(Utils::ID_TYPE_USER);
        }

        $model = new Temporary();
        $model->setAttributes($data);
        if ($model->validate() && $model->save()) {
            return Utils::returnMsg(0, '创建成功');
        } else {
            return Utils::returnMsg(1, '创建失败');
        }
    }

    public function actionInfo()
    {
        parent::checkGet();

        $uid = Yii::$app->request->get('uid');

        $info = Temporary::getInfo($this->platform_id, $uid)->toArray();
        if(!$info)
        {
            return Utils::returnMsg(1, '记录不存在');
        }

        $info = $this->completion($info);

        return Utils::returnMsg(0, null, $info);

    }

    private function completion($info)
    {
        $info['gender_text'] = Temporary::GENDER_TEXT[$info['gender']];
        if($info['skilful']) {
            $skilful = [];
            foreach(explode(',', $info['skilful']) as $v)
            {
                $skilful[] = Temporary::SKILFUL_TEXT[$v];
            }
            $info['skilful_text'] = implode(', ', $skilful);
        }

        if($info['identity']) {
            $identity = [];
            foreach(explode(',', $info['identity']) as $v)
            {
                $identity[] = Temporary::IDENTITY_TEXT[$v];
            }
            $info['identity_text'] = implode(', ', $identity);
        }

        return $info;
    }

    public function actionEdit()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();

        if(isset($data['skilful']) && $data['skilful'])
        {
            $data['skilful'] = implode(',', $data['skilful']);
        }

        // if(isset($data['identity']) && $data['identity'])
        // {
        //     $data['identity'] = implode(',', $data['identity']);
        // }

        $ret = Temporary::edit($data['uid'], $data);

        if (!$ret) {
            return Utils::returnMsg(1, '修改失败');
            
        }

        return Utils::returnMsg(0, '修改成功');
    }

    public function actionRemove()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();

        $info = Temporary::getInfo($this->platform_id, $uid);
        if(!$info)
        {
            return Utils::returnMsg(1, '记录不存在');
        }

        $info->status = 1;
        if ($info->validate() && $info->save()) {
            return Utils::returnMsg(0, '修改成功');
        } else {
            return Utils::returnMsg(1, '修改失败');
        }
    }

    public function actionSearch()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();

        if(!isset($data['key']) && !$data['key'])
        {
            return Utils::returnMsg(1, '参数有误');
        }

        $list = Temporary::search($this->platform_id, $data['key']);

        return Utils::returnMsg(0, null, $list);
    }

}
