<?php

namespace api\models;

use Yii;
use yii\data\Pagination;
use common\helpers\Utils;

/**
 * This is the model class for table "{{%article_comments}}".
 *
 * @property int $id
 * @property string $user_id 评论者user_id
 * @property int $article_id 文章ID
 * @property string $content 评论内容
 * @property string $created_at 创建时间
 */
class ArticleComments extends \yii\db\ActiveRecord
{   

    const STATUS_DELETED = 1;
    const STATUS_ACTIVE  = 0;    
    
    const PAGESIZE = 10;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%article_comments}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'article_id', 'content'], 'required'],
            [['article_id'], 'integer'],
            [['content'], 'string'],
            [['created_at'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => '评论者uid',
            'article_id' => '文章ID',
            'content' => '评论内容',
            'created_at' => '创建时间',
        ];
    }

    /**
     * 与user表关联
     * @return [type] [description]
     */
    public function getUserU()
    {
        return $this->hasOne(User::className(), ['id' => 'uid']);
    }

    /**
     * 与article表关联
     * @return [type] [description]
     */
    public function getArticleA()
    {
        return $this->hasOne(Article::className(), ['id' => 'article_id']);
    }

    /**
     * 获取所有评论列表
     * @param  [type] $order  [description]
     * @param  [type] $page   [description]
     * @param  [type] $offset [description]
     * @return [type]         [description]
     */
    public static function getList($article_id, $page = 1, $page_size = 20)
    {

        $query = self::find()
            ->where(['article_id' => $article_id, 'status' => self::STATUS_ACTIVE])
            ->orderBy('id desc');

        $countQuery = clone $query;
        // echo $countQuery->createCommand()->sql;exit;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page - 1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        foreach ($list as &$item) {
            $item['owner'] Yii::$app->user->id;
            $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
        }

        return array_merge(
            ['list' => $list],
            Utils::pagination($pages)
        );
    }

    public static function info($id)
    {
        return self::find()->where(['id' => $id, 'status' => 0])->asArray()->one();
    }

    public static function remove($id)
    {
        $sql = "UPDATE " . self::tableName() . " SET status = :status WHERE id = :id";
        return Yii::$app->db->createCommand($sql, [':status' => static::STATUS_DELETED, ':id' => $id])->execute();
    }

    /**
    * 批量评论是否激活状态  
    * @param  [type] $where [description]
    * @return [type]        [description]
    */
    public function updateCommentStatus($where)
    {
        $sql = "UPDATE `shop_article_comments` SET status = :status WHERE ".$where;
        return Yii::$app->db->createCommand($sql, [':status' => static::STATUS_DELETED])->execute();
    }
}
