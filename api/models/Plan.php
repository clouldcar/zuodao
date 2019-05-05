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
 * @property int $uid
 * @property int $team_id
 * @property string $title
 * @property int $status
 * @property string $ctime
 */
class Plan extends \yii\db\ActiveRecord
{
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
            [['uid', 'team_id', 'title', 'status'], 'required'],
            [['uid', 'team_id'], 'integer'],
            [['ctime'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 4],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'team_id' => 'Team ID',
            'title' => 'Title',
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

    public static function info($id)
    {
        $where = ['id' => $id, 'status' => 0];
        
        $query = self::find()->where($where)->asArray()->one();

        return $query;
    }

    public static function listByUid($uid, $page = 1, $page_size = 20)
    {
        $select = 'id, uid, team_id, title, score, ctime';
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
            $item['title'] = $item['user']['real_name'] . "成就宣言 " . date('Y-m-d', strtotime($item['ctime']));
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
