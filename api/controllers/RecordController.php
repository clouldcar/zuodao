<?php

namespace api\controllers;

use api\models\Record;
use Yii;


class RecordController extends BaseController
{
    public $modelClass = 'api\models\Record';
    public $platform_id;
    public $team_id;
    public $session;

    public function init()
    {   
        $this->session = Yii::$app->session;
        $platform_id = $this->session->get('platform_id');
        $this->platform_id = isset($platform_id) ?  $platform_id : null;
        $team_id = $this->session->get('team_id');
        $this->team_id = isset($team_id) ? $team_id : null;

        parent::init();
    }


    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return $behaviors;
    }

    public function actionIndex()
    {   

        if (Yii::$app->request->get('stu_uid')) {
            $condition['stu_uid'] = Yii::$app->request->get('stu_uid');
            $condition['order'] = Yii::$app->request->get('order') ? Yii::$app->request->get('order') : 'desc';
            $condition['page'] = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : '1';
            $condition['offset'] = Yii::$app->request->get('offset') ? Yii::$app->request->get('offset') : Record::PAGESIZE;
            return (new Record())->recordList($condition);
        } else {
            return false;
        }
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
                    $this->returnData['msg'] = '添加事件记录成功';
                } else {
                    $this->returnData['code'] = 0;
                    $this->returnData['msg'] = '添加事件记录失败';
                }
            }
        }

        return $this->returnData;
    }

    /**
     * 删除事件记录
     */
    public function actionDelete()
    {
        //判断批量删除
        $ids = Yii::$app->request->get('record_id', 0);
        $ids = implode(',', array_unique((array)$ids));
        if (empty($ids)) {
            return $this->returnData = [
                'code' => 802,
                'msg' => '请选择要删除的数据',
            ];
        }
        $_where = 'id in (' . $ids . ')';
        if ((new Record())->updateRecordtStatus($_where)) {
            return $this->returnData = [
                'code' => 1,
                'msg' => '删除事件记录成功'
            ];
        } else {
            return $this->returnData = [
                'code' => 0,
                'msg' => '删除事件记录失败',
            ];
        }
    }


}
