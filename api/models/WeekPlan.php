<?php

namespace api\models;

use Yii;

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
            [['id', 'uid', 'plan_id', 'plan_detail_id', 'team_id'], 'required'],
            [['id', 'uid', 'plan_id', 'plan_detail_id', 'team_id', 'target', 'result', 'check_uid'], 'integer'],
            [['start_date', 'end_date', 'check_time', 'ctime'], 'safe'],
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
            'plan_detail_id' => 'Plan Detail ID',
            'team_id' => 'Team ID',
            'name' => 'Name',
            'target' => 'Target',
            'unit' => 'Unit',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
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

    public static function getList($team_id)
    {
        return self::find()->where(['team_id' => $team_id, 'status' => 0])->orderBy('ctime ASC')->asArray()->all();
    }

    public static function info($week_plan_id)
    {
        return self::find()->where(['id' => $week_plan_id])->one()->toArray();
    }

    public function infoByPlanId($plan_id)
    {
        return self::find()->where(['plan_id' => $plan_id])->one()->toArray();
    }
}
