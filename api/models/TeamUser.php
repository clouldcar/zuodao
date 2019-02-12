<?php

namespace api\models;

use yii;

/*
 * @name 用户团队关联Model
 */

class TeamUser extends  \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return '{{%team_user}}';
    }

    /*
     * @name 增加成员
     */
    public function addMember($data){
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
            ->leftJoin(user::tableName() . ' as u','u.id=t.uid')
            ->select('u.id,u.real_name,u.avatar,t.permissions,t.create_time')
            ->where(['t.team_id'=>$teamId]);


        if($limit)
        {
            $list = $list->limit($limit);
        }

        $list = $list->asArray()->all();
        
        return $list?$list:[];
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