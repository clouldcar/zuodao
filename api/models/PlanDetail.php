<?php

namespace api\models;

use Yii;

/**
 * This is the model class for table "plan_detail".
 *
 * @property int $id
 * @property int $plan_id
 * @property int $type 1:个人成就，2:感召，3:社服
 * @property int $sub_type 1,事业,2:家庭,3:健康,4:学习, 5:人际关系
 * @property string $name 名称
 * @property int $target 目标
 * @property string $unit 单位
 * @property int $weigth 权重
 */
class PlanDetail extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'plan_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'plan_id', 'type', 'sub_type', 'name', 'target', 'unit', 'weight'], 'required'],
            [['id', 'plan_id', 'target'], 'integer'],
            [['type', 'sub_type', 'weight'], 'string', 'max' => 4],
            [['name'], 'string', 'max' => 255],
            [['unit'], 'string', 'max' => 100],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'plan_id' => 'Plan ID',
            'type' => '1:个人成就，2:感召，3:社服',
            'sub_type' => '1,事业,2:家庭,3:健康,4:学习, 5:人际关系',
            'name' => '名称',
            'target' => '目标',
            'unit' => '单位',
            'weight' => '权重',
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

    public static function getList($plan_id)
    {
        return self::find()->where(['plan_id' => $plan_id])->orderBy('id asc')->all();
    }
}
