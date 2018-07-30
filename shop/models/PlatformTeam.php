<?php

namespace shop\models;

use yii;

/*
 * @name 平台下的团队model
 */

class PlatformTeam extends  \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%platform_team}}';
    }

    /*
     * @name 通过平台id 查看平台下的团队列表
     */
    public function teamList($platformId){
        $teamList =  $this->find()->select('name,public,status,start_date,create_time')->where(['platform_id'=>$platformId])->asArray()->all();
        return $teamList?$teamList:[];
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