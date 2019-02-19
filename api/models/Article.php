<?php

namespace api\models;

use yii;
use yii\data\Pagination;
use common\helpers\Utils;
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
    public static function add($param){
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
    public static function getArticles($platform_id, $page = 1, $page_size = 20){
        $query = self::find()
            ->select('a.*, u.real_name')
            ->from(['a' => self::tableName()])
            ->leftJoin(UserInfo::tableName() . ' as u', 'u.id = a.uid')
            ->where(['a.platform_id' => $platform_id])
            ->orderBy('a.id desc');


        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page-1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        return array_merge(
            ['list' => $list], 
            Utils::pagination($pages)
        );
    }

    /*
     * @name 文章信息
     * @param article_id
     * @return array()
     */
    public static function info($id){
        return self::find()->where(['id' => $id, 'status' => 0])->one();
    }

    /*
     * @name 文章的编辑
     * @param article_id
     * @return array()
     */
    public static function edit($data){
        $id = $data['id'];
        unset($data['id']);
        $res = Yii::$app->db->createCommand()->update(self::tableName(), $data, "id=:id", [':id' => $id])->execute();
        if(!$res)
        {
            return false;
        }
        return true;
    }

    public static function remove($id)
    {
        $data = ['status' => 1];
        $res = Yii::$app->db->createCommand()->update(self::tableName(), $data, "id=:id", [':id' => $id])->execute();
        if(!$res)
        {
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
    public function getComments($id, $page = 1, $page_size = 20)
    {
       $data = $this->find()
                ->from('shop_article_comments')
                ->select('shop_user.username, shop_article_comments.*')
                ->leftJoin('shop_user', 'shop_user.id = shop_article_comments.user_id')
                ->where([
                    'shop_article_comments.article_id' => $id,
                    'shop_article_comments.status' => ArticleComments::STATUS_ACTIVE
                ])
                ->orderBy('shop_article_comments.created_at desc')
                ->offset(($page-1)*$offset)
                ->limit($offset)
                ->asArray()
                ->all();
        return $data;
    }

}