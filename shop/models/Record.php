<?php

namespace shop\models;

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
            [['content', 'recorder'], 'required'],
            [['content'], 'string'],
            [['recorder_id'], 'integer'],
            [['recorder'], 'string', 'max' => 100],
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
        ];
    }
}
