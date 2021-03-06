<?php
namespace api\models;

use yii;
use yii\data\Pagination;
use common\helpers\Utils;

/*
 * @name 团队model
 */

class Team extends  \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%team}}';
    }

    /*
     * @name 通过用户id 查看自己加入的团队列表
     */
    public function teamList($uid){

        $team_ids = [];
        $teamList = [];
        $query = $this->find()
                ->select('team_id')
                ->from(TeamUser::tableName())
                ->where(['uid' => $uid, 'grade' => 6, 'status' => 0])
                ->asArray()
                ->all();
        if($query) foreach($query as $item)
        {
            $team_ids[] = $item['team_id'];
        }

        if($team_ids)
        {
            //自己创建的
            $select = 'id, uid, name, platform_name, logo, ideal, create_time';
            $where = 'id in(' . implode(',', $team_ids) . ') and status = 0';
            $teamList =  $this->find()
                ->select($select)
                ->from(self::tableName() . ' as t')
                ->where($where)
                ->asArray()
                ->all();
        }

        if($teamList) foreach($teamList as &$team)
        {
            $team['total'] = TeamUser::find()->where(['team_id' => $team['id']])->count();
            $team['manager'] = UserInfo::find()->select('uid,real_name,avatar')->where(['uid' => $team['uid']])->one()->toArray();
        }

        return $teamList;
    }

    public static function isExists($uid, $name)
    {
        return self::find()->where(['uid' => $uid, 'name' => $name, 'status' => 0])->count();
    }

    //团队基础信息
    public function teamInfo($teamId)
    {
        $select = 'id,uid,name,platform_name,ideal,logo,visions_map,invite_code,invite_time,create_time';
        $result = $this->find()->select($select)->where(['id' => $teamId, 'status' => 0])->one()->toArray();
        if($result)
        {
            $result['total'] = count(TeamUser::membersList($teamId));

            $result['manager'] = UserInfo::getInfoByUID($result['uid'], 1);
        }

        return $result;
    }

    /*
     * @name 添加新的团队
     */
    public static function teamCreate($param){
        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $param)->execute();
        if($data){
            return true;
        }else{
            return false;
        }
    }



    /*
     * @name 编辑团队
     */
    public function teamEditor($param, $team_id){

        $data = Yii::$app->db->createCommand()->update(self::tableName(), $param, ['id' => $team_id])->execute();
        if($data){
            return true;
        }else{
            return false;
        }
    }

    /*
     * @name 删除团队
     */
    public function teamDelete($param){
        $data = Yii::$app->db->createCommand()->update(self::tableName(), $param)->execute();
        if($data){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 平台团队信息
     */
    public static function getInfoByName($name, $platform_id)
    {
        $where = ['name' => $name, 'platform_id' => $platform_id];
        return self::find()->where($where)->one();
    }

    public static function getInfoById($team_id)
    {
        return self::find()->where(['id' => $team_id, 'status' => 0])->one();
    }

    /**
     * 平台团队列表
     */
    public static function getTeamList($platform_id, $page, $page_size)
    {
        $where = ['platform_id' => $platform_id, 'status' => 0];
        
        //team info + user count
        $query = self::find()->select('count(u.uid) as user_total, t.*')
            ->from(self::tableName() . ' as t')
            ->leftJoin(UserInfo::tableName() . ' as u','t.id = u.team_id')
            ->where(['t.platform_id' => $platform_id])
            ->groupBy('t.id');

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