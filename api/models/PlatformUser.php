<?php

namespace api\models;

use yii;
use yii\data\Pagination;
use common\helpers\Utils;

/*
 * @name 用户平台关联Model
 */

class PlatformUser extends  \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%platform_user}}';
    }

    public function isExists($uid, $platform_id)
    {
        return $this->find()->where(['uid' => $uid, 'platform_id' => $platform_id])->count();
    }

    /*
     * @name 增加员工
     */
    public function addStaff($params){
        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $params)->execute();
        if(!$data){
            return false;
        }
        return true;
    }

    public static function updatePermission($params)
    {
        $data = Yii::$app->db->createCommand()->update(
            self::tableName(), 
            ['permissions' => $params['permissions']], 
            ['platform_id' => $params['platform_id'], 'uid' => $params['uid']]
        )->execute();
        if(!$data){
            return false;
        }
        return true;
    }

    public static function getUser($uid)
    {
        $select = 'platform_id,permissions';
        $where = ['uid' => $uid, 'status' => 0];

        return self::find()->select($select)->where($where)->one();
    }

    /*
     * @name 获取平台下的用户列表
     * @param platform_id 平台id $type  用户权限控制 0为所有用户
     * @return array()
     */
    public function getUsers($platform_id, $type, $page = 1, $page_size = 20){
        $where = [];

        if($type)
        {
            $where = ['p.permissions' => $type];
        }

        $query = $this->find()
            ->from(self::tableName() . ' as p')
            ->leftJoin(User::tableName() . ' as u','u.id = p.uid')
            ->select('p.uid, p.permissions, p.create_time, u.username, u.real_name, u.avatar')
            ->where(['p.platform_id' => $platform_id]);
        if($where)
        {
            $query = $query->andWhere($where);
        }

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