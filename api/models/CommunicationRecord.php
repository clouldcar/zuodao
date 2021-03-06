<?php

namespace api\models;

use Yii;
use yii\data\Pagination;
use common\helpers\Utils;
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
    const STATUS_DELETED = 1;
    const STATUS_ACTIVE  = 0;    
    
    const PAGESIZE = 10;

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
            [['staff_uid', 'uid', 'type', 'target', 'content', 'result'], 'required'],
            [['content', 'result'], 'string'],
            [['created_at', 'updated_at'], 'safe'],
            [['platform_id', 'staff_uid', 'uid'], 'integer'],
            [['type', 'target'], 'string', 'max' => 4]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform_id' => '平台id',
            'staff_uid' => '统筹uid',
            'student_uid' => '学员uid',
            'type' => '沟通方式：电话（1）微信（2）邮件（3）',
            'target' => '沟通目标：报读（1）建立链接（2）答疑（3）其它（4）',
            'content' => '内容',
            'result' => '结果',
            'status' => '状态',
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
        return $this->hasOne(Student::className(), ['stu_uid' => 'student_uid']);
    }

    /**
     * 获取所有沟通列表
     * @param  [type] $order  [排序]
     * @param  [type] $limit  [页数]
     * @param  [type] $page_size [分页数]
     * @return [type]         [数据]
     */
    public static function getList($platform_id, $uid = 0, $page = 1, $page_size)
    {
        $where = ['platform_id' => $platform_id, 'status' => 0];
        if($uid)
        {
            $where['uid'] = $uid;
        }
        $query = self::find()->where($where)->orderBy('id desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page-1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        return array_merge(
            ['list' => $list], 
            Utils::pagination($pages)
        );

        /*
        $data = static::find()
                ->joinWith(['staffU', 'studentU'])
                ->select('shop_communication_record.*,shop_user.username, shop_student.stu_name')
                ->where(['shop_communication_record.status' => static::STATUS_ACTIVE])
                ->orderBy('shop_communication_record.updated_at '.$order)
                ->offset(($page-1)*$offset)
                ->limit($offset)
                ->asArray()
                ->all();
        foreach ($data as $key => $value) {
            unset($value['staffU']);
            unset($value['studentU']);
            $value['communicate_type'] = static::$typeMap[$value['communicate_type']];
            $value['target'] = static::$targetMap[$value['target']];
            $result[] = $value;
        }

        return $result;
        */
    }

    public static function info($id)
    {
        return self::find()->where(['id' => $id, 'status' => self::STATUS_ACTIVE])->one();
    }

    /**
    * 批量修改沟通是否激活状态  
    * @param  [type] $where [description]
    * @return [type]        [description]
    */
    public function updateCreordStatus($where)
    {
        $sql = "UPDATE `shop_communication_record` SET status = :status WHERE ".$where;
        return Yii::$app->db->createCommand($sql, [':status' => static::STATUS_DELETED])->execute();
    }

}
