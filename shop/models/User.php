<?php

namespace shop\models;

use Yii;

class User extends \common\models\User
{
    //设置加密后的密码
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /*
     * @name 获取user信息
     * @param $param 查询条件 $select 查询字段
     * @return array();
     */
    public function userList($param,$select){
        return User::find()->select($select)->where($param)->asArray()->all();
    }

    /*
     * @name 想user表插入数据
     * @param value
     * @return str
     */
    public function addUser($param){
        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $param)->execute();
        if($data){
            return $user_id = Yii::$app->db->getLastInsertID();
        }else{
            return '';
        }
    }


}