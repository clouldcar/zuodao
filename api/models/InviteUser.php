<?php

namespace api\models;

use yii;
/*
 * @name 用户平台关联Model
 */

class InviteUser extends  \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%invite_user}}';
    }

    /*
     * @name 插入邀请员工数据
     * @param
     * @return str
     */
    public function addInvite($param){
        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $param)->execute();
        if(!$data){
            return '';
        }
        return $str = Yii::$app->db->getLastInsertID();
    }

    /*
     * @name 插入邀请员工数据扩展表
     * @param
     * @return mixed
     */
    public function addInviteExtends($param){
        $data = Yii::$app->db->createCommand()->insert('shop_invite_extends', $param)->execute();
        if(!$data){
            return false;
        }
        return true;
    }



}