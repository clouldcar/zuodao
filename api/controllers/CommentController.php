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

        foreach($list['list'] as &$item)
        {
            $item['owner'] = Yii::$app->user->id;
        }

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

        $data['uid'] = $uid;
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
    public function actionRemove()
    {
        //判断批量删除
        $id = Yii::$app->request->get('id');
        $uid = Yii::$app->user->id;

        if(!$id)
        {
            return Utils::returnMsg(1, '参数有误');
        }
        
        $info = ArticleComments::info($id);
        if($info['uid'] != $uid)
        {
            return Utils::returnMsg(1, '没有权限');
        }

        ArticleComments::remove($id);

        return Utils::returnMsg(0, '删除成功');
    }

}
