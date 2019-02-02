<?php

namespace api\models;

use Yii;

class User extends \common\models\User
{   

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            /**
             * 写库和更新库时，时间自动完成
             * 注意rules验证必填时可使用AttributeBehavior行为，model的EVENT_BEFORE_VALIDATE事件
             */
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    //设置加密后的密码
    public function setPassword($password)
    {
        $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

    /**
     * 获取用户的包括用户的基本信息以及平台权限及团队之间的相关关系
     * @param  [string] $username [用户名]
     * @return [array]           [description]
     */
    public function getUserAllInfo($uid)
    {
        $user = static::findOne(['id' => $uid]);
        
        $userInfo = $user->attributes;
        $userWithPlatformInfo = PlatformUser::find()->select('platform_id')->where(['user_id'=> $userInfo['id']])->asArray()->one();
        $userWithTeamInfo = TeamUser::find()->select('team_id')->where(['user_id'=> $userInfo['id']])->asArray()->one();
        
        $userWithPlatformInfo = empty($userWithPlatformInfo) ? [] : $userWithPlatformInfo;
        $userWithTeamInfo = empty($userWithTeamInfo) ? [] : $userWithTeamInfo;
        
        return array_merge($userInfo, $userWithPlatformInfo, $userWithTeamInfo);
    }

   /**
    * 批量修改用户是否激活状态
    * @param  [type] $where [description]
    * @return [type]        [description]
    */
    public function updateUserStatus($where)
    {
        $sql = "UPDATE `shop_user` SET status = :status WHERE ".$where;
        return Yii::$app->db->createCommand($sql, [':status' => static::STATUS_DELETED])->execute();
    }

    /*
     * @name 获取user信息
     * @param $param 查询条件 $select 查询字段
     * @return array();
     */
    public function userList($param, $select){
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