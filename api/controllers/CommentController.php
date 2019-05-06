<?php

namespace api\controllers;

use Yii;
use common\helpers\Utils;
use api\models\ArticleComments;

class CommentController extends BaseController
{	

	public $modelClass = 'api\models\ArticleComments';

    /**
     * 评论列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $data = Yii::$app->request->get();

    	$order = isset($data['order']) ? $data['order'] : 'desc';
    	$page = isset($data['page']) ? $data['page'] : '1';
    	$offset = isset($data['offset']) ? $data['offset'] : ArticleComments::PAGESIZE;
        return (new ArticleComments())->commentsList($order, $page, $offset);
    }

    /**
     * 评论
     * @return [type] [description]
     */
    public function actionCreate()
    {
        parent::checkPost();
        
        $data = Yii::$app->request->post();
        $uid = Yii::$app->user->id;

        $model = new ArticleComments();
        
        if ($model->load($data) && $model->validate()) {
        	$model->setAttributes($data);
        	if ($model->save()) {
                return Utils::returnMsg(0, '评论成功');
        	} else {
                return Utils::returnMsg(1, '评论失败');
        	}
        }

        return $this->returnData;
    }


    /**
     * 删除评论
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
        if ((new ArticleComments())->updateCommentStatus($_where)) {
            return $this->returnData = [
                'code' => 1,
                'msg' => '删除评论成功'
            ];
        } else {
            return $this->returnData = [
                'code' => 0,
                'msg' => '删除评论失败',
            ];
        }
    }

}
