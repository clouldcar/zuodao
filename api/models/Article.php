<?php

namespace api\models;

use yii;
use yii\data\Pagination;
use common\helpers\Utils;

/*
 * @name 文章详情
 */

class Article extends \yii\db\ActiveRecord {
    const STATUS_ACTIVE = 0;

    //推荐
    const RECOMMENT_1 = 1;
    const RECOMMENT_2 = 2;
    const RECOMMENT_3 = 3;
    //老P首页
    const RECOMMENT_4 = 4;


    const TYPE_4 = 4;


    const SEND_TEXT = [
        0 => '不限',
        1 => '团队',
        2 => '毕业专区'
    ];

    const TYPE_TEXT = [
        0 => '文章',
        1 => '通知'
    ];
    /*
     * @name 增加文章
     * @param
     * @return mixed
    */
    public static function add($param) {
        $db = Yii::$app->db;

        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $param)->execute();
        if (!$data) {
            return false;
        }

        return Yii::$app->db->getLastInsertID();
    }

    /*
     * @name 文章列表
     * @param platform_id
     * @return array()
     * 是否需要分类显示?
    */
    public static function getArticles($platform_id, $page = 1, $page_size = 20) {
        $query = self::find()
            ->where(['platform_id' => $platform_id, 'status' => 0])
            ->orderBy('id desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page - 1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        foreach($list as &$item)
        {
            $item['send_to_text'] = self::SEND_TEXT[$item['send_to']];
            $item['type_text'] = self::TYPE_TEXT[$item['type']];
        }

        return array_merge(
            ['list' => $list],
            Utils::pagination($pages)
        );
    }

    /*
     * @name 文章列表
     * @param team_id
     * @return array()
    */
    public static function getListByTeamId($team_id, $cid, $page = 1, $page_size = 20) {
        $query = self::find()
            ->select('id,uid, cid, title, cover_image, content, created_at')
            ->from(self::tableName() . ' as a')
            ->leftJoin(TeamArticle::tableName() . ' as ta', 'ta.article_id = a.id')
            ->where(['ta.team_id' => $team_id, 'a.cid' => $cid, 'a.status' => '0'])
            ->orderBy('a.id desc');

        $countQuery = clone $query;
        // echo $countQuery->createCommand()->sql;exit;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page - 1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        foreach ($list as &$item) {
            $item['desc'] = Utils::truncateUtf8String(strip_tags($item['content']), 60);
            unset($item['content']);
            $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
        }

        return array_merge(
            ['list' => $list],
            Utils::pagination($pages)
        );
    }

    /*
     * @name 文章列表
     * @param cid
     * @return array()
    */
    public static function getListByCId($cid, $page = 1, $page_size = 20) {
        $query = self::find()
            ->select('id,uid,title,cid,cover_image, content, created_at')
            ->from(self::tableName() . ' as a')
            ->leftJoin(TeamArticle::tableName() . ' as ta', 'ta.article_id = a.id')
            ->where(['a.cid' => $cid, 'a.status' => '0'])
            ->orderBy('a.id desc');

        $countQuery = clone $query;
        // echo $countQuery->createCommand()->sql;exit;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page - 1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        foreach ($list as &$item) {
            // $item['desc'] = substr(strip_tags($item['content']), 0, 60);
            $item['desc'] = Utils::truncateUtf8String(strip_tags($item['content']), 240);
            unset($item['content']);
            $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
        }

        return array_merge(
            ['list' => $list],
            Utils::pagination($pages)
        );
    }
    /*
     * @name 文章列表
     * @param recomment
     * @return array()
    */
    public static function getListByRecommend($recommend, $page = 1, $page_size = 20) {
        $query = self::find()
            ->select('id,uid,title,cid,cover_image, content, created_at')
            ->from(self::tableName() . ' as a')
            ->leftJoin(TeamArticle::tableName() . ' as ta', 'ta.article_id = a.id')
            ->where("a.recommend like '%" . $recommend . "%' and a.status = 0")
            ->orderBy('a.id desc');

        $countQuery = clone $query;
        // echo $countQuery->createCommand()->sql;exit;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page - 1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        if($list) foreach ($list as &$item) {
            $item['desc'] = Utils::truncateUtf8String(strip_tags($item['content']), 240);
            unset($item['content']);
            // $item['desc'] = '移动小号手机卡0月租卡无线流量卡电话卡视频卡号码卡4g卡上网卡,中国移动4G无线上网卡24G包年卡路由器随身mifi纯流量卡全国电信';
            $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
        }

        return array_merge(
            ['list' => $list],
            Utils::pagination($pages)
        );
    }

    //已推荐列表

    public static function getListByType($type, $page = 1, $page_size = 20)
    {
        $query = self::find()
            ->select('a.id,a.uid,title,cid,cover_image,recommend, created_at')
            ->from(self::tableName() . ' as a')
            ->leftJoin(ArticleCategory::tableName() . ' as c', 'c.id = a.cid')
            ->where(['c.type' => $type, 'a.status' => '0', 'c.status' => '0'])
            ->orderBy('a.id desc');

        $countQuery = clone $query;
        // echo $countQuery->createCommand()->sql;exit;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page - 1);

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
     * @name 文章列表
     * @param cid
     * @return array()
    */
    public static function getList($page = 1, $page_size = 20) {
        $query = self::find()
            ->select('id,uid,title,cid,created_at')
            ->from(self::tableName() . ' as a')
            ->leftJoin(TeamArticle::tableName() . ' as ta', 'ta.article_id = a.id')
            ->where(['a.status' => '0'])
            ->orderBy('a.id desc');

        $countQuery = clone $query;
        // echo $countQuery->createCommand()->sql;exit;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page - 1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();
        foreach ($list as &$item) {
            $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
        }

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
    public static function info($id) {
        $info = self::find()->where(['id' => $id, 'status' => 0])->asArray()->one();
        $info['cid'] = (int) $info['cid'];
        $info['type'] = (int) $info['type'];
        $info['send_to'] = (int) $info['send_to'];

        if($info)
        {
            $info['user'] = UserInfo::getInfoByUID($info['uid'], 1);
        }

        return $info;
    }

    /*
             * @name 文章的编辑
             * @param article_id
             * @return array()
    */
    public static function edit($data) {
        $id = $data['id'];
        unset($data['id']);
        $res = Yii::$app->db->createCommand()->update(self::tableName(), $data, "id=:id", [':id' => $id])->execute();
        if (!$res) {
            return false;
        }
        return true;
    }

    public static function remove($id) {
        $data = ['status' => 1];
        $res = Yii::$app->db->createCommand()->update(self::tableName(), $data, "id=:id", [':id' => $id])->execute();
        if (!$res) {
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
    public function getComments($id, $page = 1, $page_size = 20) {
        $data = $this->find()
            ->from('shop_article_comments')
            ->select('shop_user.username, shop_article_comments.*')
            ->leftJoin('shop_user', 'shop_user.id = shop_article_comments.user_id')
            ->where([
                'shop_article_comments.article_id' => $id,
                'shop_article_comments.status' => ArticleComments::STATUS_ACTIVE,
            ])
            ->orderBy('shop_article_comments.created_at desc')
            ->offset(($page - 1) * $offset)
            ->limit($offset)
            ->asArray()
            ->all();
        return $data;
    }

}