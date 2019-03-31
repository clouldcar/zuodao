<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "week_plan_detail".
 *
 * @property int $id
 * @property int $week_plan_id
 * @property int $target
 * @property string $unit
 * @property int $result
 * @property int $completion_ratio 总完成度
 * @property string $node1
 * @property int $check_uid
 * @property string $node2
 * @property string $check_time
 * @property int $status
 */
class WeekPlanDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'week_plan_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['week_plan_id', 'target', 'unit', 'result', 'completion_ratio', 'node1', 'check_uid', 'node2'], 'required'],
            [['week_plan_id', 'target', 'result', 'check_uid'], 'integer'],
            [['node1', 'node2'], 'string'],
            [['check_time'], 'safe'],
            [['unit'], 'string', 'max' => 255],
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
            'week_plan_id' => 'Week Plan ID',
            'target' => 'Target',
            'unit' => 'Unit',
            'result' => 'Result',
            'completion_ratio' => '总完成度',
            'node1' => 'Node1',
            'check_uid' => 'Check Uid',
            'node2' => 'Node2',
            'check_time' => 'Check Time',
            'status' => 'Status',
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

    public static function getList($week_plan_id)
    {
        return self::find()->where(['week_plan_id' => $week_plan_id, 'status' => 0])->orderBy('id asc')->all();
    }

    public static function info($week_plan_detail_id)
    {
        return self::find()->where(['id' => $week_plan_detail_id, 'status' => 0])->one()->toArray();
    }
}
