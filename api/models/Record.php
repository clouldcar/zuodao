<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "{{%record}}".
 *
 * @property int $id
 * @property string $created_at 创建时间
 * @property string $content 记录内容
 * @property string $recorder 创建人姓名
 * @property int $recorder_id 创建人id
 */
class Record extends \yii\db\ActiveRecord
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
        return '{{%record}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'safe'],
            [['content', 'recorder', 'stu_uid'], 'required'],
            [['content'], 'string'],
            [['recorder_id'], 'integer'],
            [['recorder'], 'string', 'max' => 100],
            [['stu_uid'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => '创建时间',
            'content' => '记录内容',
            'recorder' => '创建人姓名',
            'recorder_id' => '创建人id',
            'stu_uid' => '对应学员id',
            'status' => '激活状态'
        ];
    }

    /**
     * 获取显示学生时间记录列表
     * @param  [type] $condition [显示条件]
     * @return [type]            [description]
     */
    public function recordList($condition)
    {
        return $data = static::find()
                ->select('*')
                ->where([
                    'status' => static::STATUS_ACTIVE,
                    'stu_uid' => $condition['stu_uid'],
                ])
                ->orderBy('updated_at '.$condition['order'])
                ->offset(($condition['page']-1) * $condition['offset'])
                ->limit($condition['offset'])
                ->asArray()
                ->all();
    }

    /**
    * 批量修改事件记录是否激活状态  
    * @param  [type] $where [description]
    * @return [type]        [description]
    */
    public function updateRecordtStatus($where)
    {
        $sql = "UPDATE `shop_record` SET status = :status WHERE ".$where;
        return Yii::$app->db->createCommand($sql, [':status' => static::STATUS_DELETED])->execute();
    }

}
