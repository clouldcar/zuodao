<?php

namespace shop\models;

use yii;

/*
 * @name 用户平台关联Model
 */

class PlatformUser extends  \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%platform_user}}';
    }

    /*
     * @name 获取平台下的用户列表
     * @param platform_id 平台id $type  用户权限控制 0为所有用户
     * @return array()
     */
    public function platformUsers($platform_id,$type){
        if(!empty($type)){
            $where = [1==1];
        }else{
            $where = ['p.permissions'=>$type];
        }
        $list = $this->find()
            ->from('shop_platform_user as p')
            ->leftJoin('shop_user as u','u.id=p.user_id')
            ->select('p.permissions,p.create_time,u.username,u.phone')
            ->where(['p.platform_id'=>$platform_id])
            ->andWhere($where)
            ->asArray()
            ->all();
        return $list?$list:[];
    }

    /*
     * @name 增加用户权限
     */

    public function addEmployees($param){
        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $param)->execute();
        if(!$data){
            return false;
        }
        return true;
    }


}