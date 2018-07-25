<?php

namespace shop\models;

use Yii;

/**
 * This is the model class for table "{{%student}}".
 *
 * @property int $stu_id
 * @property string $stu_uid
 * @property string $stu_name 学生姓名
 * @property int $stu_qq QQ
 * @property string $stu_email 邮箱
 * @property string $stu_job 职业
 * @property string $reference 推荐人
 * @property string $created_at 添加时间
 * @property int $stu_status 上课状况（未上课0、一阶段1、二阶段2、三阶段3、毕业4）
 * @property string $stu_prefix
 *
 * @property User $stuU
 */
class Student extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%student}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['stu_name', 'unique'],
            [['stu_uid', 'stu_name'], 'required'],
            [['stu_qq'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['stu_uid'], 'string', 'max' => 20],
            [['stu_name', 'stu_job', 'reference'], 'string', 'max' => 255],
            [['stu_email'], 'string', 'max' => 100],
            [['stu_status', 'stu_prefix'], 'string', 'max' => 4],
            [['stu_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['stu_uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'stu_id' => 'Stu ID',
            'stu_uid' => 'Stu Uid',
            'stu_name' => '学生姓名',
            'stu_qq' => 'QQ',
            'stu_email' => '邮箱',
            'stu_job' => '职业',
            'reference' => '推荐人',
            'created_at' => '添加时间',
            'stu_status' => '上课状况（未上课0、一阶段1、二阶段2、三阶段3、毕业4）',
            'stu_prefix' => 'Stu Prefix',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStuU()
    {
        return $this->hasOne(User::className(), ['id' => 'stu_uid']);
    }
}
