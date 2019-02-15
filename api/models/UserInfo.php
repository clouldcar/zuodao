<?php

namespace api\models;

use Yii;
use yii\data\Pagination;

/**
 * This is the model class for table "shop_user_info".
 *
 * @property int $id
 * @property int $uid
 * @property int $platform_id
 * @property string $phone
 * @property string $real_name
 * @property string $avatar
 * @property string $birthday
 * @property string $province
 * @property string $city
 * @property string $district
 * @property string $address
 * @property string $ctime
 */
class UserInfo extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['platform_id', 'phone', 'real_name', 'ctime'], 'required'],
            [['uid', 'platform_id', 'team_id', 'grade'], 'integer'],
            [['ctime'], 'safe'],
            [['phone', 'real_name', 'birthday', 'province', 'city', 'district', 'address'], 'string', 'max' => 100],
            [['avatar'], 'string', 'max' => 255],
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
            'team_id' => 'Team ID',
            'grade' => 'Grade',
            'phone' => 'Phone',
            'real_name' => 'Real Name',
            'avatar' => 'Avatar',
            'birthday' => 'Birthday',
            'province' => 'Province',
            'city' => 'City',
            'district' => 'District',
            'address' => 'Address',
            'ctime' => 'Ctime',
        ];
    }

    /**
     * @inheritdoc
     * @return UserInfoQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new UserInfoQuery(get_called_class());
    }

    public static function getInfoByUID($uid)
    {
        return self::find()->where(['uid' => $uid])->orderBy('id desc')->one();
    }

    public static function getPlatformUserByPhone($phone, $platform_id)
    {
        $where = ['phone' => $phone, 'platform_id' => $platform_id];

        return self::find()->where($where)->orderBy('id desc')->one();
    }

    public static function getPlatformUserByUID($uid, $platform_id)
    {
        $where = ['uid' => $uid, 'platform_id' => $platform_id];

        return self::find()->where($where)->orderBy('id desc')->one();
    }

    public static function getPlatformUsers($platform_id, $page, $page_size)
    {
        $query = self::find()->where(['platform_id' => $platform_id]);

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

    /**
     * 批量修改团队信息
     * $ids user_info.id
     * $platform_id 平台ID
     * $team_id 团队ID
     * $grade 年级/阶段
     */
    public static function updateTeamInfo($ids, $platform_id, $team_id = null, $grade = null)
    {
        $values = [];
        foreach($ids as $id)
        {
            $str = "($id";
            $str .= $team_id ? ", $team_id" : "";
            $str .= $grade ? ", $grade" : "";
            $str .= ")";
            $values[] = $str;
        }
        $cloumn = "id";
        $cloumn .= $team_id ? ", team_id" : "";
        $cloumn .= $grade ? ", grade" : "";
        $sql = "replace into " . self::tableName() . "($cloumn) values" . implode(',', $values);
        
        if(Yii::$app->db->createCommand($sql)->execute())
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * 检查是否为本平台学员
     */
    public static function checPlatformkUser($platform_id, $ids)
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

}
