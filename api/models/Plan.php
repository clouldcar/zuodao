<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "plan".
 *
 * @property int $id
 * @property int $uid uid
 * @property string $title 标题
 * @property string $content 内容，json
 * @property int $score 分数
 * @property int $status
 * @property string $ctime
 */
class Plan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'plan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uid', 'title'], 'required'],
            [['id', 'uid'], 'integer'],
            [['content'], 'string'],
            [['ctime'], 'safe'],
            [['title'], 'string', 'max' => 255],
            [['score', 'status'], 'string', 'max' => 4],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'uid',
            'title' => '标题',
            'content' => '内容，json',
            'score' => '分数',
            'status' => 'Status',
            'ctime' => 'Ctime',
        ];
    }
}
