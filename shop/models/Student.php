<?php

namespace shop\models;

use Yii;

/**
 * This is the model class for table "{{%student}}".
 *
 * @property int $stu_id
 * @property string $stu_uid 学员 uid
 * @property int team_id 所属团队id
 * @property int platform_id 所属平台id
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
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE  = 1;

     const PAGESIZE  = 10;
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
        return '{{%student}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['stu_name', 'unique'],
            // [['stu_uid', 'stu_name', 'team_id', 'platform_id'], 'required'],
            [['stu_uid', 'stu_name'], 'required'],
            [['stu_qq', 'team_id', 'platform_id'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['stu_uid'], 'string', 'max' => 20],
            [['stu_name', 'stu_job', 'reference'], 'string', 'max' => 255],
            [['stu_email'], 'string', 'max' => 100],
            [['stu_status', 'stu_prefix'], 'integer', 'max' => 4],
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
            'team_id' => '所属团队id',
            'platform_id' => '所属平台id',
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

    /**
     * 获取显示学生列表
     * @param  [type] $condition [显示条件]
     * @return [type]            [description]
     */
    public function studentList($condition)
    {
        return $data = static::find()
                ->select('*')
                ->where([
                    'status' => static::STATUS_ACTIVE,
                    'team_id' => $condition['team_id'],
                    'platform_id' => $condition['platform_id'],
                ])
                ->orderBy('updated_at '.$condition['order'])
                ->offset(($condition['page']-1) * $condition['offset'])
                ->limit($condition['offset'])
                ->asArray()
                ->all();

    }

    
    /**
    * 批量修改学员是否激活状态  
    * @param  [type] $where [description]
    * @return [type]        [description]
    */
    public function updateStudentStatus($where)
    {
        $sql = "UPDATE `shop_student` SET status = :status WHERE ".$where;
        return Yii::$app->db->createCommand($sql, [':status' => static::STATUS_DELETED])->execute();
    }
}
