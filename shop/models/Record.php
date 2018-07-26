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
