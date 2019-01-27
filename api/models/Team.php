<?php
namespace api\models;

use yii;

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
        $select = 'id,uid,name,platform_name,logo,create_time';
        $where = ['uid' => $uid, 'status' => 0];
        $teamList =  $this->find()->select($select)->where($where)->asArray()->all();

        if($teamList) 
        {
            foreach($teamList as &$team)
            {
                $team['total'] = TeamUser::find()->where(['team_id' => $team['id']])->count();
                $team['manager'] = User::find()->select('id,real_name,avatar')->where(['id' => $team['uid']])->one()->toArray();
            }
        }

        return $teamList;
    }

    //团队基础信息
    public function teamInfo($teamId)
    {
        $select = 'id,uid,name,platform_name,ideal,logo,visions_map,create_time';
        $result = $this->find()->select($select)->where(['id' => $teamId, 'status' => 0])->one()->toArray();
        if($result)
        {
            $result['total'] = TeamUser::find()->where(['team_id' => $teamId])->count();

            $result['manager'] = User::find()->select('id,real_name,avatar')->where(['id' => $result['uid']])->one()->toArray();
        }

        return $result;
    }

    /*
     * @name 添加新的团队
     */
    public function teamCreate($param){
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
    public function teamEditor($param){
        $data = Yii::$app->db->createCommand()->update(self::tableName(), $param)->execute();
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






}