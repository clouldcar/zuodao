<?php

namespace api\models;

use yii;

/*
 * @name 用户团队关联Model
 */

class TeamUser extends  \yii\db\ActiveRecord
{
    const IDENTITY_TEXT = [
        0 => '学员',
        1 => '导师',
        2 => '总教练', 
        3 => '教练',
        4 => '团长',
        5 => '助教'
    ];
     //身份：学员
    const LEVEL_STUDENT = 0;
    //教练
    const LEVEL_COACH  = 1;

    //状态：正常
    const STATUS_NORMAL = 0;
    //删除
    const STATUS_DELETE = 1;
    //未审核

    public static function tableName()
    {
        return '{{%team_user}}';
    }

    /*
     * @name 增加成员
     */
    public static function addMember($data){
        /*
        if($data['type'] == self::ADD_BATCH){
            //批量添加
            unset($data['type']);
            $result = Yii::$app->db->createCommand()->batchInsert(self::tableName(), $data)->execute();

        }

        if($data['type'] == self::ADD_ALONE){
            //单独添加
            unset($data['type']);
            $result = Yii::$app->db->createCommand()->insert(self::tableName(), $data)->execute();
        }
        */
        $result = Yii::$app->db->createCommand()->insert(self::tableName(), $data)->execute();
        if(!$result){
            return false;
        }
        return true;
    }

    public static function batchAddMember($data)
    {
        $col = ['platform_id', 'team_id', 'uid', 'grade', 'permissions'];
        $num = Yii::$app->db->createCommand()->batchInsert(self::tableName(), $col, $data)->execute();
        return $num;
    }

    /*
    * @name 增加成员
    */
    public function deleteMembers($data){
        $result = Yii::$app->db->createCommand()->update(self::tableName(), $data)->execute();
        if(!$result){
            return false;
        }
        return true;
    }

    /*
     * @name 修改团队成员权限
     */

    public function editorMembers($data){
        $result = Yii::$app->db->createCommand()->update(self::tableName(), $data)->execute();
        if(!$result){
            return false;
        }
        return true;
    }

    /*
     * @name 展示团队下的成员信息
     */
    public static function membersList($teamId, $limit = null){
        $list = self::find()
            ->from(self::tableName() . ' as t')
            ->leftJoin(UserInfo::tableName() . ' as u','u.uid=t.uid')
            ->select('u.uid, u.real_name, u.avatar, t.identity, t.create_time')
            ->where(['t.team_id'=>$teamId]);


        if($limit)
        {
            $list = $list->limit($limit);
        }

        $list = $list->asArray()->all();
        
        return $list?$list:[];
    }

    public static function isTeamUser($team_id, $uid)
    {
        $result = $this->find()->where(['team_id' => $teamId, 'uid' => $uid])->count();

        return $result;
    }

    public function memberTotal($teamId)
    {
        $result = $this->find()->where(['team_id' => $teamId])->count();

        return $result;
    }

    public static function hasUser($teamId, $uid)
    {
        return self::find()->where(['team_id' => $teamId, 'uid' => $uid])->count();
    }



}