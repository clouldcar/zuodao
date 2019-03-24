<?php

namespace api\models;

use Yii;
use yii\data\Pagination;
use common\helpers\Utils;

use api\models\UserInfo;

/**
 * This is the model class for table "plan".
 *
 * @property int $id
 * @property int $uid uid
 * @property string $title 标题
 * @property string $content 内容，json
 * @property int $score 分数
 * @property int $status
 * @property string $ctime
 */
class Plan extends \yii\db\ActiveRecord
{

    const type = [
        '1' => '事业',
        '2' => '家庭',
        '3' => '健康',
        '4' => '人际关系',
        '5' => '学习成长'
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'plan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uid', 'title'], 'required'],
            [['id', 'uid'], 'integer'],
            [['content'], 'string'],
            [['ctime'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['score', 'status'], 'string', 'max' => 4],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'uid',
            'title' => '标题',
            'content' => '内容，json',
            'score' => '分数',
            'status' => 'Status',
            'ctime' => 'Ctime',
        ];
    }

    public static function add($params)
    {
        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $params)->execute();
        if(!$data){
            return false;
        }
        
        return true;
    }

    public static function detail($id)
    {
        $select = 'id,uid,team_id,title,objective,inspire,social_services,score,ctime';
        $where = ['id' => $id, 'status' => 0];
        
        $query = self::find()->select($select)->where($where)->asArray()->one();

        $uid = $query['uid'];
        $objective = json_decode($query['objective'], 1);

        $obj = [];
        $obj2 = [];
        foreach ($objective as $key => $value) {
            $obj['name'] = self::type[$key];
            $obj['content'] = $objective[$key];

            $obj2[] = $obj;

        }
        $query['user'] = UserInfo::getInfoByUID($uid, 1);
        $query['objective'] = $obj2;

        return $query;
    }

    public static function listByUid($uid, $page = 1, $page_size = 20)
    {
        $select = 'id,uid,team_id,title,score,ctime';
        $where = ['uid' => $uid, 'status' => 0];
        $query = self::find()->select($select)->where($where)->orderBy('ctime desc');


        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page-1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        foreach($list as &$item)
        {
            $item['user'] = UserInfo::getInfoByUID($uid, 1);
        }

        return array_merge(
            ['list' => $list], 
            Utils::pagination($pages)
        );
    }

    public static function listByTeamId($team_id, $page = 1, $page_size = 20)
    {
        $select = 'id,uid,team_id,title,score,ctime';
        $where = ['team_id' => $team_id, 'status' => 0];
        $query = self::find()->select($select)->where($where)->orderBy('ctime desc');


        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page-1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        foreach($list as &$item)
        {
            $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
        }

        return array_merge(
            ['list' => $list], 
            Utils::pagination($pages)
        );
    }
}
