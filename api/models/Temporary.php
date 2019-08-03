<?php

namespace api\models;

use Yii;
use yii\data\Pagination;
use common\helpers\Utils;

/**
 * This is the model class for table "temporary".
 *
 * @property int $id
 * @property int $uid
 * @property int $platform_id
 * @property string $real_name
 * @property string $phone 电话
 * @property string $gender 性别，F女，M男
 * @property string $minorities 民族
 * @property string $graduate 毕业机构
 * @property string $designation 团队番号
 * @property string $skilful 擅长阶段: 1,2,3
 * @property string $identity 1导师，2总教练，3教练，4团长，5助教
 * @property int $status 0正常，1删除
 * @property string $ctime
 */
class Temporary extends \yii\db\ActiveRecord
{
    const GENDER_TEXT = [
        'F' => '女',
        'M' => '男'
    ];

    const SKILFUL_TEXT = [
        1 => '一阶段',
        2 => '二阶段',
        3 => '三阶段',
    ];

    const IDENTITY_TEXT = [
        1 => '导师',
        2 => '总教练', 
        3 => '教练',
        4 => '团长',
        5 => '助教'
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'temporary';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'platform_id', 'real_name', 'phone', 'gender', 'minorities', 'graduate', 'designation', 'skilful', 'identity', 'ctime'], 'required'],
            [['uid', 'platform_id'], 'integer'],
            [['ctime'], 'safe'],
            [['real_name', 'phone', 'gender', 'minorities', 'skilful', 'identity'], 'string', 'max' => 100],
            [['graduate', 'designation'], 'string', 'max' => 255],
            [['status'], 'string', 'max' => 4],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uid' => 'Uid',
            'platform_id' => 'Platform ID',
            'real_name' => 'Real Name',
            'phone' => '电话',
            'gender' => '性别，F女，M男',
            'minorities' => '民族',
            'graduate' => '毕业机构',
            'designation' => '团队番号',
            'skilful' => '擅长阶段: 1,2,3',
            'identity' => '1导师，2总教练，3教练，4团长，5助教',
            'status' => '0正常，1删除',
            'ctime' => 'Ctime',
        ];
    }

    public static function getUsers($platform_id, $page, $page_size)
    {
        $query = self::find()->where(['platform_id' => $platform_id])->orderBy('id desc');

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

    public static function getInfo($platform_id, $uid)
    {
        $where = ['uid' => $uid, 'platform_id' => $platform_id, 'status' => 0];

        return self::find()->where($where)->orderBy('id desc')->one();
    }

    public static function edit($uid, $data)
    {
        $result = Yii::$app->db->createCommand()->update(self::tableName(), $data, "uid=:uid", [':uid' => $uid])->execute();
        return $result;
    }

    public static function search($platform_id, $key)
    {
        $select = 'uid, real_name, phone';
        $where = ['platform_id' => $platform_id, 'status' => 0];

        return self::find()
            ->select($select)
            ->where($where)
            ->andFilterWhere(['like', 'real_name', $key])
            ->andFilterWhere(['like', 'phone', $key])
            ->all();
    }
}
