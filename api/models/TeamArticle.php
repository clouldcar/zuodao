<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "{{%team_article}}".
 *
 * @property int $platform_id
 * @property int $team_id
 * @property int $article_id
 * @property int $status
 */
class TeamArticle extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%team_article}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id', 'team_id', 'article_id', 'status'], 'required'],
            [['platform_id', 'team_id', 'article_id'], 'integer'],
            [['status'], 'string', 'max' => 4],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'platform_id' => 'Platform ID',
            'team_id' => 'Team ID',
            'article_id' => 'Article ID',
            'status' => 'Status',
        ];
    }

    public static function add($params)
    {
        $sql = "replace into " . self::tableName() . "(platform_id, team_id, article_id) values(" . $params['platform_id'] . ", " . $params['team_id'] . ", " . $params['article_id'] . ")";

        return Yii::$app->db->createCommand($sql)->execute();
    }
}
