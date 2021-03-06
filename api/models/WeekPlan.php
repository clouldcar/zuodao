<?php

namespace api\models;

use Yii;
use yii\data\Pagination;
use common\helpers\Utils;

/**
 * This is the model class for table "week_plan".
 *
 * @property int $id
 * @property int $uid
 * @property int $plan_id
 * @property int $plan_detail_id
 * @property int $team_id
 * @property string $name
 * @property int $target
 * @property string $unit
 * @property string $start_date
 * @property string $end_date
 * @property int $result
 * @property int $completion_ratio 总完成度
 * @property string $note1
 * @property int $check_uid
 * @property string $node2
 * @property string $check_time
 * @property int $status
 * @property string $ctime
 */
class WeekPlan extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'week_plan';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'uid', 'plan_id', 'team_id', 'start_time', 'end_time', 'detail'], 'required'],
            [['id', 'uid', 'plan_id', 'team_id', 'target', 'result', 'check_uid'], 'integer'],
            [['start_time', 'end_time', 'check_time', 'ctime'], 'safe'],
            [['name', 'note1', 'node2'], 'string', 'max' => 255],
            [['unit'], 'string', 'max' => 100],
            [['completion_ratio', 'status'], 'string', 'max' => 4],
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
            'plan_id' => 'Plan ID',
            'start_time' => 'Start Date',
            'end_time' => 'End Date',
            'name' => 'Name',
            'target' => 'Target',
            'unit' => 'Unit',
            'result' => 'Result',
            'completion_ratio' => '总完成度',
            'note1' => 'Note1',
            'check_uid' => 'Check Uid',
            'node2' => 'Node2',
            'check_time' => 'Check Time',
            'status' => 'Status',
            'ctime' => 'Ctime',
        ];
    }

    public static function add($params)
    {
        $data = Yii::$app->db->createCommand()->insert(self::tableName(), $params)->execute();
        if(!$data){
            return false;
        }
        
        return true;
    }

    public static function edit($params, $week_plan_id)
    {
        $res = Yii::$app->db->createCommand()->update(self::tableName(), $params, "id=:id", [':id' => $week_plan_id])->execute();
        if (!$res) {
            return false;
        }
        return true;
    }

    public static function getList($team_id, $page = 1, $page_size = 20)
    {
        $query = self::find()->where(['team_id' => $team_id, 'status' => 0])->orderBy('ctime desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page-1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        foreach($list as &$item)
        {
            $item['detail'] = json_decode($item['detail'], true);
            $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
            $item['title'] = $item['user']['real_name'] . "的周计划";
        }

        return array_merge(
            ['list' => $list], 
            Utils::pagination($pages)
        );
    }

    public static function getListByUID($uid, $page = 1, $page_size = 20)
    {
        $query = self::find()->where(['uid' => $uid, 'status' => 0])->orderBy('ctime desc');

        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $page_size]);
        $pages->setPage($page-1);

        $list = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->asArray()
            ->all();

        foreach($list as &$item)
        {
            $item['detail'] = json_decode($item['detail'], true);
            $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
            $item['title'] = $item['user']['real_name'] . "的周计划";
        }

        return array_merge(
            ['list' => $list], 
            Utils::pagination($pages)
        );
    }

    public static function info($week_plan_id)
    {
        $info = self::find()->where(['id' => $week_plan_id])->asArray()->one();

        $info['detail'] = json_decode($info['detail'], true);

        return $info;
    }

    public function infoByPlanId($plan_id)
    {
        return self::find()->where(['plan_id' => $plan_id])->asArray()->one();
    }
}
