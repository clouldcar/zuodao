<?php

namespace api\models;

use yii;

/*
 * @name 用户团队关联Model
 */

class TeamUser extends  \yii\db\ActiveRecord
{
    const  ADD_BATCH  = 1;
    const  ADD_ALONE  = 0;

    public static function tableName()
    {
        return '{{%team_user}}';
    }

    /*
     * @name 增加成员
     */
    public function addMembers($data){
        $result = '';
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
    public function membersList($teamId){
        $list = $this->find()
            ->from('shop_team_user as t')
            ->leftJoin('shop_user as u','u.id=t.user_id')
            ->select('t.permissions,p.create_time,u.username,u.phone')
            ->where(['t.team_id'=>$teamId])
            ->asArray()
            ->all();
        return $list?$list:[];

    }



}