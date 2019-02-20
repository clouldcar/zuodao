<?php
namespace common\models;

use Yii;
use yii\web\IdentityInterface;

class User extends \yii\db\ActiveRecord implements IdentityInterface
{
    const SIGNSTATUS_FRONTEND = 1;
    const SIGNSTATUS_BACKEND = 2;
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
            [['username', ], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 6],
            // [['created_at', 'updated_at'], 'safe'],
            [['created_at','updated_at'], 'default', 'value' => date('Y-m-d H:i:s')],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
            'password' => 'Password',
            'wx_unionid' => 'Wx_unionid',
            'type' => 'Type',
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
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    public function getId()
    {
        return $this->getPrimaryKey();
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