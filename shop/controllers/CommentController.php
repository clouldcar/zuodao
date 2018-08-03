<?php

namespace shop\controllers;

use Yii;
use shop\models\ArticleComments;

class CommentController extends BaseController
{	

	public $modelClass = 'shop\models\ArticleComments';

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return $behaviors;
    }

    /**
     * 获得后台展示评论列表
     * @return [type] [description]
     */
    public function actionIndex()
    {
    	$order = Yii::$app->request->get('order') ? Yii::$app->request->get('order') : 'desc';
    	$page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : '1';
    	$offset = Yii::$app->request->get('offset') ? Yii::$app->request->get('offset') : ArticleComments::PAGESIZE;
        return (new ArticleComments())->commentsList($order, $page, $offset);
    }

    /**
     * 评论
     * @return [type] [description]
     */
    public function actionAdd()
    {	
        $model = new ArticleComments();
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            if ($model->load($data, '') && $model->validate()) {
            	$model->setAttributes($data);
            	if ($model->save()) {
            		$this->returnData = [
            			'code' => 1,
            			'msg' => '评论成功',
            		];
            	} else {
            		$this->returnData = [
            			'code' => 0,
            			'msg' => '评论失败',
            		];
            	}
            }
        } else {
        	$this->returnData = [
            	'code' => 806,
            	'msg' => $model->getErrors(),
            ];
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
