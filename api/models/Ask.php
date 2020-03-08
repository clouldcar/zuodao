<?php

namespace api\models;

use Yii;
use yii\data\Pagination;
use common\helpers\Utils;

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

    public static function getList($platform, $page = 1, $page_size = 20)
    {
        $query = self::find()
            ->where(['platform_id' => $platform_id])
            ->orderBy('id desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page - 1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        return array_merge(
            ['list' => $list],
            Utils::pagination($pages)
        );
    }

    public static function info($id)
    {
        return self::findOne($id)->toArray();
    }

    public static function getInfoByUid($platform, $uid)
    {
        return self::find()->where(['platform_id' => $platform_id, 'uid' => $uid])->one();
    }

    public static function checkInfo($platform_id, $phone)
    {
        return self::find()->where(['platform_id' => $platform_id, 'phone' => $phone])->one();
    }
}
