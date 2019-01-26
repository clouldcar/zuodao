<?php

namespace api\models;

use Yii;

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

	const STATUS_DELETED = 0;
    const STATUS_ACTIVE  = 1;    
    
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
            [['user_id', 'article_id', 'content'], 'required'],
            [['article_id'], 'integer'],
            [['content'], 'string'],
            [['created_at'], 'safe'],
            [['user_id'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '评论者user_id',
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
    	return $this->hasOne(User::className(), ['id' => 'user_id']);
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
    public function commentsList($order, $page, $offset)
    {
    	$data = static::find()->joinWith(['UserU', 'ArticleA'])
    			->select('shop_article_comments.*, shop_article.title, shop_user.username')
    			->where([
    				'shop_article.status' => Article::STATUS_ACTIVE,
    				'shop_article_comments' => ArticleComments::STATUS_ACTIVE,
    			])
    			->orderBy('shop_article_comments.created_at '.$order)
				->offset(($page-1)*$offset)
                ->limit($offset)
                ->asArray()
                ->all();    

        foreach ($data as $key => $value) {
			unset($value['UserU']);
            unset($value['ArticleA']);   
            $result[] = $value;
        }

        return $result;
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
