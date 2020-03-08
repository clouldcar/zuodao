<?php

namespace api\controllers\platform;

use Yii;
use api\models\Ask;

class QuestionController extends \yii\web\Controller
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

        $list = Ask::getList($this->platform_id, $page, $page_size);

        if($list['list'])
        {
            foreach($list['list'] as &$item)
            {
                $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
            }
        }

        return Utils::returnMsg(0, null, $list);
    }

    public function actionInfo()
    {
    	parent::checkGet();

        $data = Yii::$app->request->get();

    	$info = Ask::getInfoByUid($this->platform_id, $data['uid']);

    	return Utils::returnMsg(0, null, $info);
    }

}
