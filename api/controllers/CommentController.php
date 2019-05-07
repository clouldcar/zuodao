<?php

namespace api\controllers;

use Yii;
use common\helpers\Utils;
use api\models\ArticleComments;

class CommentController extends BaseController
{	

    /**
     * 评论列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $data = Yii::$app->request->get();

        $article_id = $data['article_id'];
    	$page = isset($data['page']) ? $data['page'] : '1';
        $page_size = 20;

        if(!$article_id)
        {
            return Utils::returnMsg(1, '参数有误');
        }

        $list = ArticleComments::getList($article_id, $page, $page_size);

        return Utils::returnMsg(0, null, $list);
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

        $data['user_id'] = $uid;
        $model = new ArticleComments();
        $model->setAttributes($data);
        if (!$model->validate())
        {
            return Utils::returnMsg(1, $model->getErrorSummary(true)[0]);
        }
        
        if (!$model->save())
        {
            return Utils::returnMsg(1, '评论失败');
        }

        return Utils::returnMsg(0, '评论成功');

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
