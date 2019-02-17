<?php

namespace api\models;

use yii;

/*
 * @name 文章详情
 */

class Article extends  \yii\db\ActiveRecord
{
    /*
     * @name 增加文章
     * @param
     * @return mixed
     */
    public static function addArticle($param){
        $db = Yii::$app->db;
        $tran = $db->beginTransaction();

        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $param)->execute();
        if(!$data){
            $tran->rollBack();
            return false;
        }
        if($param == 2){
            $result = Yii::$app->db->createCommand()->batchInsert(self::tableName(), $param)->execute();
            if(!$result){
                $tran->rollBack();
                return false;
            }
        }
        $tran->commit();
        return true;
    }

    /*
     * @name 文章列表
     * @param platform_id
     * @return array()
     * 是否需要分类显示?
     */
    public function articleList($platform_id){
        $list =  $this->find()
            ->from('shop_article as a')
            ->select('u.name,a.id,a.user_id,a.platform_id,a.title,a.content,a.class,a.type,a.send_to,a.feedback_way,a.created_at')
            ->leftJoin('shop_user as u','u.id=a.user_id')
            ->where(['platform_id'=>$platform_id])
            ->asArray()
            ->all();
        return $list?$list:[];
    }

    /*
     * @name 文章信息
     * @param article_id
     * @return array()
     */
    public function articleInfo($id){
        $list =  $this->find()
            ->from('shop_article as a')
            ->select('u.name,a.id,a.user_id,a.platform_id,a.title,a.content,a.class,a.type,a.send_to,a.feedback_way,a.created_at')
            ->leftJoin('shop_user as u','u.id=a.user_id')
            ->where(['id'=>$id])
            ->asArray()
            ->all();
        return $list?$list:[];
    }

    /*
     * @name 文章的编辑
     * @param article_id
     * @return array()
     */
    public function articleEditor($data){
        //文章是否存在
        $exists =  $this->find()->select('id')->where(['id'=>$data['id']])->asArray()->one();
        if(empty($exists)){
            return 100;
        }
        $data = Yii::$app->db->createCommand()->update(self::tableName(), $data)->execute();
        if(!$data){
            return false;
        }
        return true;
    }

    /**
     * 获取当前文章下所有评论列表
     * @param  [type] $id     [description]
     * @param  [type] $order  [description]
     * @param  [type] $page   [description]
     * @param  [type] $offset [description]
     * @return [type]         [description]
     */
    public function getArticleComments($id,$order,$page,$offset)
    {
       $data = $this->find()
                ->from('shop_article_comments')
                ->select('shop_user.username, shop_article_comments.*')
                ->leftJoin('shop_user', 'shop_user.id = shop_article_comments.user_id')
                ->where([
                    'shop_article_comments.article_id' => $id,
                    'shop_article_comments.status' => ArticleComments::STATUS_ACTIVE
                ])
                ->orderBy('shop_article_comments.created_at '.$order)
                ->offset(($page-1)*$offset)
                ->limit($offset)
                ->asArray()
                ->all();
        return $data;
    }

}