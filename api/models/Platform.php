<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "{{%platform}}".
 *
 * @property int $id 平台id
 * @property int $uid
 * @property string $name 平台名称
 * @property string $nickname 平台简称
 * @property int $platform_type 1LP,2IN,3TA
 * @property string $principal 平台负责人
 * @property string $principal_id 平台负责人user_id
 * @property string $mobile 手机号
 * @property string $address 地址
 * @property string $create_time
 * @property string $logo
 * @property string $email 邮箱
 * @property string $introduct 介绍
 * @property string $certificate 证明材料
 * @property string $application date 申请日期
 * @property int $audit_status 审核状态 1通过
 * @property string $aduit_date 审核日期
 * @property string $end_date 结束日期
 * @property string $banner
 */
class Platform extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%platform}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uid', 'certificate'], 'required'],
            [['id', 'uid'], 'integer'],
            [['create_time', 'application date', 'aduit_date', 'end_date'], 'safe'],
            [['name', 'nickname', 'principal', 'principal_id'], 'string', 'max' => 100],
            [['platform_type', 'audit_status'], 'string', 'max' => 3],
            [['mobile'], 'string', 'max' => 11],
            [['address', 'logo', 'introduct'], 'string', 'max' => 200],
            [['email'], 'string', 'max' => 30],
            [['certificate'], 'string', 'max' => 255],
            [['banner'], 'string', 'max' => 250],
            [['id'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '平台id',
            'uid' => 'Uid',
            'name' => '平台名称',
            'nickname' => '平台简称',
            'platform_type' => '1LP,2IN,3TA',
            'principal' => '平台负责人',
            'principal_id' => '平台负责人user_id',
            'mobile' => '手机号',
            'address' => '地址',
            'create_time' => 'Create Time',
            'logo' => 'Logo',
            'email' => '邮箱',
            'introduct' => '介绍',
            'certificate' => '证明材料',
            'application date' => '申请日期',
            'audit_status' => '审核状态 1通过',
            'aduit_date' => '审核日期',
            'end_date' => '结束日期',
            'banner' => 'Banner',
        ];
    }

    public static function getInfoByUID($uid)
    {
        return self::find()->where(['uid' => $uid, 'audit_status' => 1])->one();
    }
}
