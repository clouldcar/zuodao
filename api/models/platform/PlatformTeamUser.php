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
                $query->andWhere(['or', 'grade=3', 'grade=4']);
            }
            if($filter['grade'] && $filter['grade'] == 3)
            {
                $query->andWhere(['or', 'grade=5', 'grade=6']);
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
}

