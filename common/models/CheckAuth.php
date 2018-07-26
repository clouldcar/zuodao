<?php

namespace common\models;

use shop\models\PlatformUser;
use Yii;
use yii\web\Controller;


class CheckAuth extends Controller
{
    static $isAdmin = NULL;

    /*
     * @name 通过user_id 平台id验证是否有改平台权限 根据需要可以改成权限具体范围验证
     *
     */
    public static function isAuth(){
        if(self::$isAdmin === NULL) {
            $user_id = Yii::$app->session->get('user_id');
            $platform_id = Yii::$app->session->get('platform_id');
            $user_id = 1;
            $platform_id =1 ;
            if(empty($user_id) || empty($platform_id)){
                self::$isAdmin = false;
            }
            $param = [
                'user_id'=>$user_id,
                'platform_id'=>$platform_id
            ];
            $permissions = PlatformUser::find()->where($param)->asArray()->one();
            if($permissions){
                self::$isAdmin = true;
            }else{
                self::$isAdmin = false;
            }

        }
        return self::$isAdmin;
    }
}