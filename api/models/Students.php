<?php

namespace api\models;

use Yii;
use yii\data\Pagination;
use common\helpers\Utils;

/**
 * This is the model class for table "students".
 *
 * @property int $id 用户ID
 * @property int $platform_id
 * @property int $grade 0未上课，1、3、5未上课阶段，2、4、6已上课
 * @property string $phone 手机
 * @property string $real_name 姓名
 * @property string $gender 性别，F女，M男
 * @property int $age
 * @property string $avatar 头像
 * @property string $birthday 生日
 * @property string $minorities 民族
 * @property string $marriage 婚姻状况，0未婚，1已婚
 * @property string $qualifications 学历
 * @property string $pin 身份证号
 * @property string $email 邮箱
 * @property string $qq qq
 * @property string $wchart 微信
 * @property string $work 工作单位
 * @property string $job 工作岗位
 * @property string $city
 * @property string $address
 * @property int $reference 推荐人
 * @property int $status
 * @property string $ctime
 */
class Students extends \yii\db\ActiveRecord
{

    const GRADE_TEXT = [
        0 => '未上课',
        1 => '一阶段未上课',
        2 => '一阶段毕业',
        3 => '二阶段未上课',
        4 => '二阶段毕业',
        5 => '三阶段未上课',
        6 => '三阶段毕业',
    ];

    const GENDER_TEXT = [
        'F' => '女',
        'M' => '男'
    ];

    const MARRIAGE_TEXT = [
        0 => '未婚',
        1 => '已婚',
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'students';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['uid', 'age', 'ctime'], 'required'],
            [['uid', 'platform_id', 'age', 'reference'], 'integer'],
            [['ctime'], 'safe'],
            [['grade', 'status'], 'string', 'max' => 4],
            [['phone', 'real_name', 'gender', 'birthday', 'minorities', 'marriage', 'city'], 'string', 'max' => 100],
            [['avatar', 'qualifications', 'pin', 'email', 'qq', 'wchart', 'work', 'job', 'address'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '用户ID',
            'platform_id' => 'Platform ID',
            'grade' => '0未上课，1、3、5未上课阶段，2、4、6已上课',
            'phone' => '手机',
            'real_name' => '姓名',
            'gender' => '性别，F女，M男',
            'age' => 'Age',
            'avatar' => '头像',
            'birthday' => '生日',
            'minorities' => '民族',
            'marriage' => '婚姻状况，0未婚，1已婚',
            'qualifications' => '学历',
            'pin' => '身份证号',
            'email' => '邮箱',
            'qq' => 'qq',
            'wchart' => '微信',
            'work' => '工作单位',
            'job' => '工作岗位',
            'city' => 'City',
            'address' => 'Address',
            'reference' => '推荐人',
            'status' => 'Status',
            'ctime' => 'Ctime',
        ];
    }

    public static function getUsersByPlatform($platform_id, $filter = [], $page, $page_size)
    {
        $where = ['platform_id' => $platform_id];
        if($filter['team_id'])
        {
            $where['team_id'] = $filter['team_id'];
        }
        $query = self::find()->where($where);
        if($filter['grade'] && $filter['grade'] == 1)
        {
            $query->addWhere(['or', 'grade=1', 'grade=2']);
        }
        if($filter['grade'] && $filter['grade'] == 2)
        {
            $query->addWhere(['or', 'grade=3', 'grade=4']);
        }
        if($filter['grade'] && $filter['grade'] == 3)
        {
            $query->addWhere(['or', 'grade=5', 'grade=6']);
        }

        $query->orderBy('id desc');

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

    public static function getUserByPhone($platform_id, $phone)
    {
        $where = ['phone' => $phone, 'platform_id' => $platform_id, 'status' => 0];

        return self::find()->where($where)->orderBy('id desc')->one();
    }


    public static function getUserBasicByUID($platform_id, $uid) 
    {
        $select = 'uid, real_name, phone, gender, avatar';
        $where = ['uid' => $uid, 'platform_id' => $platform_id, 'status' => 0];

        return self::find()->select($select)->where($where)->orderBy('id desc')->one();
    }

    public static function getUserByUID($platform_id, $uid) 
    {
        $where = ['uid' => $uid, 'platform_id' => $platform_id, 'status' => 0];

        return self::find()->where($where)->orderBy('id desc')->one();
    }

    public static function getInfoByPhone($phone){
        $where = ['phone' => $phone, 'status' => 0];
        return self::find()->where()->orderBy('id desc')->one();
    }

    public static function edit($uid, $data)
    {
        $result = Yii::$app->db->createCommand()->update(self::tableName(), $data, "uid=:uid", [':uid' => $uid])->execute();
        return $result;
    }

    /**
     * 检查是否为本平台学员
     */
    public static function checkPlatformUser($platform_id, $ids)
    {
        $query = self::find()->where(['in', 'id', $ids]);

        $result = true;
        
        foreach($query->each() as $user){
            // 数据从服务端中以 100 个为一组批量获取，
            // 但是 $user 代表 user 表里的一行数据
            if($user['platform_id'] != $platform_id)
            {
                $result = false;
                break;
            }
        }

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
