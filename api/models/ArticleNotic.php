<?php

namespace api\models;

use yii;

/*
 * @name 文章通知
 */

class ArticleNotic extends  \yii\db\ActiveRecord
{
    /*
     * @name 文章的通知列表
     */
    public function noticeList($article_id){

        $list =  $this->find()
            ->from('shop_article_notic as a')
            ->select('a.id,a.user_id,a.type,a.status,a.is_read,a.is_confitm,a.created_at,a.updated_at')
            ->leftJoin('shop_user as u','u.id=a.user_id')
            ->where(['article_id'=>$article_id])
            ->asArray()
            ->all();
        return $list?$list:[];
    }

    /*
     * @name 确认或通知更新状态
     * @param article_id user_id type
     * @return mixed
     */
    public function confirmNotice($data){
        $result = '';
        if($data['type'] == 1){
            $param = [
                'is_read' =>1
            ];
            $result = Yii::$app->db->createCommand()->update(self::tableName(), $param)->execute();
        }
        if($data['type'] == 2){
            $param = [
                'is_read' =>1,
                'is_confitm'=>$data['confirm']
            ];
            $result = Yii::$app->db->createCommand()->update(self::tableName(), $param)->execute();
        }
        if(!$result){
            return false;
        }
        return true;
    }




}