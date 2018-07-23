<?php
namespace common\models;

use Yii;

class Admin extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return '{{%user}}';
    }

    public function rules()
    {
        return [
            ['type', 'required'],
            [['username'], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 6],
            [['phone'], 'string', 'max' => 15],
            [['phone','username'], 'unique'],
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

        ];
    }

}