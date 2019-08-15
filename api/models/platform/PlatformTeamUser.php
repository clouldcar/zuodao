<?php

namespace api\models\platform;

use yii;
use yii\data\Pagination;
use common\helpers\Utils;
use api\models\Students;


/*
 * @name 用户团队关联Model
 */

class PlatformTeamUser extends  \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%team_user}}';
    }

    public static function getUsers($platform_id, $team_id, $filter = [], $page = 1, $page_size = 20)
    {
        $query = self::find()
            ->from(self::tableName() . ' as tu')
            ->leftJoin(Students::tableName() . ' as s','s.uid=tu.uid')
            ->select('tu.uid, tu.grade, tu.identity, s.real_name, s.avatar')
            ->where(['tu.team_id' => $team_id, 'tu.status' => 0, 's.status' => 0]);
        
        //条件
        if($filter)
        {
            if($filter['grade'] == 1)
            {
                $query->andWhere(['or', 'tu.grade=1', 'tu.grade=2']);
            }

            if($filter['grade'] && $filter['grade'] == 2)
            {
                $query->andWhere(['or', 'tu.grade=3', 'tu.grade=4']);
            }
            if($filter['grade'] && $filter['grade'] == 3)
            {
                $query->andWhere(['or', 'tu.grade=5', 'tu.grade=6']);
            }
        }
        

        $query->orderBy('tu.id desc');

        //分页
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

    /**
     * 检查是否为本平台学员
     */
    public static function checkPlatformkUser($platform_id, $ids)
    {
        $query = self::find()->where(['in', 'id', $ids]);

        $result = true;
        
        foreach($query->each() as $user){
            // 数据从服务端中以 100 个为一组批量获取，
            // 但是 $user 代表 user 表里的一行数据
            if($user['platform_id'] != $platform_id)
            {
                $result = false;
                break;
            }
        }

        return $result;
    }

    public static function addTeamUser($platform_id, $ids, $team_id, $grade)
    {
        foreach($ids as $uid)
        {
            $user = self::find()->where(['platform_id' => $platform_id, 'team_id' => $team_id, 'uid' => $uid, 'status' => 0])->one();
            if(!$user)
            {
                Yii::$app->db->createCommand()
                ->insert(
                    self::tableName(), 
                    ['platform_id' => $platform_id, 'team_id' => $team_id, 'uid' => $uid, 'grade' => $grade]
                )
                ->execute();

                Yii::$app->db->createCommand()
                ->update(
                    Students::tableName(),
                    ['grade' => $grade],
                    ['platform_id' => $platform_id, 'uid' => $uid, 'status' => 0]
                )
                ->execute();
            }
        }

        return true;
    }

    public static function updateUser($platform_id, $team_id, $ids, $params)
    {
        foreach($ids as $uid)
        {
            Yii::$app->db->createCommand()
                ->update(self::tableName(), $params, ['platform_id' => $platform_id, 'team_id' => $team_id, 'uid' => $uid])
                ->execute();
                
        }

        return true;
    }
}

