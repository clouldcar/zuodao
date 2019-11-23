<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "ask".
 *
 * @property int $id
 * @property int $platform_id
 * @property string $name
 * @property int $phone
 * @property string $content
 * @property string $ctime
 */
class Ask extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'ask';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id', 'name', 'phone', 'content', 'ctime'], 'required'],
            [['platform_id', 'phone'], 'integer'],
            [['content'], 'string'],
            [['ctime'], 'safe'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'platform_id' => 'Platform ID',
            'name' => 'Name',
            'phone' => 'Phone',
            'content' => 'Content',
            'ctime' => 'Ctime',
        ];
    }

    public static info($id)
    {
        return self::find()->where(['id' => $id])->one()->toArray();
    }
}
