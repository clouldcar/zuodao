<?php
namespace common\models;

use Yii;
use yii\web\IdentityInterface;

class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    const SIGNSTATUS_BACKEND = 1;
    const SIGNSTATUS_FRONTEND = 2;
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE  = 1;
    public $auth_key;
    
    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['type', 'required'],
            [['username'], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 6],
            [['phone'], 'string', 'max' => 15],
            [['phone','username'], 'unique'],
            [['created_at', 'updated_at'], 'safe'],
            // [['created_at','updated_at'], 'default', 'value' => date('Y-m-d H:i:s')],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'phone' => 'Phone',
            'type' => 'Type',
            'avatar' => 'Avatar',
            'valicode' => 'Valicode',
            'status' => 'Status',
            'created_at' => 'Created_at',
            'updated_at' => 'Updated_at'
        ];
    }

    /**
     * 根据UID获取账号信息
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        // TODO: Implement findIdentityByAccessToken() method.
    }

    public function getId()
    {
    }

    public function getAuthKey()
    {

    }

    public function validateAuthKey($authKey)
    {
        // TODO: Implement validateAuthKey() method.
    }

    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }
}