<?php

namespace api\models;

use Yii;
use yii\data\Pagination;
use common\helpers\Utils;

/**
 * This is the model class for table "{{%article_category}}".
 *
 * @property int $id
 * @property string $name
 * @property int $platform_id
 * @property int $team_id
 */
class ArticleCategory extends \yii\db\ActiveRecord
{
    const TYPE_ID_PLATFORM = 1;
    const TYPE_ID_GRAD = 2;
    const TYPE_ID_TEAM = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%article_category}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'platform_id'], 'required'],
            [['id', 'platform_id', 'team_id'], 'integer'],
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
            'name' => 'Name',
            'platform_id' => 'Platform ID',
            'team_id' => 'Team ID',
        ];
    }

    public static function getInfo($name, $platform_id)
    {
        $where = [
            'name' => $name,
            'platform_id' => $platform_id,
            'status' => 0
        ];
        return self::find()->where($where)->one();
    }

    public static function getCategories($platform_id, $page, $page_size)
    {
        $query = self::find()->where(['platform_id' => $platform_id, 'status' => 0])->orderBy('id desc');

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
    }

    public static function getCategoriesByType($type, $page, $page_size)
    {
        $query = self::find()->select('id,name')->where(['type' => $type, 'status' => 0])->orderBy('ctime asc');

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
    }


}
