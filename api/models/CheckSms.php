<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "check_sms".
 *
 * @property string $id
 * @property string $phone
 * @property integer $code
 * @property string $ctime
 */
class CheckSms extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'check_sms';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone'], 'required'],
            [['phone', 'ctime'], 'string'],
            [['code'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'phone' => 'Phone',
            'code' => 'Code',
            'ctime' => 'Create Time'
        ];
    }

    public static function get($phone){
        
        $res =  self::find()
        		->where('phone='.$phone.' and date(ctime) = curdate()')
        		->orderby('id desc');
        return $res->asArray()->all();
    }
    
}
