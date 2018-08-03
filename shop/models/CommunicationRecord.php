<?php

namespace shop\models;

use Yii;

/**
 * This is the model class for table "{{%communication_record}}".
 *
 * @property int $id
 * @property string $staff_uid 统筹uid
 * @property string $student_uid 学员uid
 * @property int $communicate_type 沟通方式：电话（1）微信（2）邮件（3）
 * @property int $target 沟通目标：报读（1）建立链接（2）答疑（3）其它（4）
 * @property string $content 内容
 * @property string $result 结果
 * @property string $created_at 创建时间
 * @property string $updated_at 更新时间
 *
 * @property User $staffU
 * @property User $studentU
 */
class CommunicationRecord extends \yii\db\ActiveRecord
{

    const TYPE_PHONE = 1;
    const TYPE_WECHAT = 2;
    const TYPE_EMAIL = 3;

    public static $typeMap = [
        self::TYPE_PHONE => '电话',
        self::TYPE_WECHAT => '微信',
        self::TYPE_EMAIL => '邮箱',
    ];

    const TARGET_BAODU = 1;
    const TARGET_JIANLILIANJIE = 2;
    const TARGET_DAYI = 3;
    const TARGET_OTHER = 4;

    public static $targetMap = [
        self::TARGET_BAODU => '报读',
        self::TARGET_JIANLILIANJIE => '建立链接',
        self::TARGET_DAYI => '答疑',
        self::TARGET_OTHER => '其它',
    ];


    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            /**
             * 写库和更新库时，时间自动完成
             * 注意rules验证必填时可使用AttributeBehavior行为，model的EVENT_BEFORE_VALIDATE事件
             */
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => date('Y-m-d H:i:s'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%communication_record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['staff_uid', 'student_uid', 'communicate_type', 'target', 'content', 'result'], 'required'],
            [['content', 'result'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['staff_uid', 'student_uid'], 'string', 'max' => 20],
            [['communicate_type', 'target'], 'string', 'max' => 4],
            [['staff_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['staff_uid' => 'id']],
            [['student_uid'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['student_uid' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'staff_uid' => '统筹uid',
            'student_uid' => '学员uid',
            'communicate_type' => '沟通方式：电话（1）微信（2）邮件（3）',
            'target' => '沟通目标：报读（1）建立链接（2）答疑（3）其它（4）',
            'content' => '内容',
            'result' => '结果',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStaffU()
    {
        return $this->hasOne(User::className(), ['id' => 'staff_uid']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStudentU()
    {
        return $this->hasOne(User::className(), ['id' => 'student_uid']);
    }
}
