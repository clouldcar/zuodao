<?php

namespace shop\controllers;

use Yii;
use shop\models\CommunicationRecord;

class CrecordController extends BaseController
{	
	public $modelClass = '\shop\models\CommunicationRecord';


    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return $behaviors;
    }

	/**
	 * 沟通记录列表
	 * @return [type] [description]
	 */
    public function actionIndex()
    {	
    	$order = Yii::$app->request->get('order') ? Yii::$app->request->get('order') : 'desc';
    	$page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : '1';
    	$offset = Yii::$app->request->get('offset') ? Yii::$app->request->get('offset') : CommunicationRecord::PAGESIZE;
        return (new CommunicationRecord())->crecordList($order, $page, $offset);
    }


    /**
     * 添加沟通记录
     * @return [type] [description]
     */
    public function actionAdd()
    {	
        $model = new CommunicationRecord();
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            if ($model->load($data, '') && $model->validate()) {
            	$model->setAttributes($data);
            	if ($model->save()) {
            		$this->returnData = [
            			'code' => 1,
            			'msg' => '添加沟通记录成功',
            		];
            	} else {
            		$this->returnData = [
            			'code' => 0,
            			'msg' => '添加沟通记录失败',
            		];
            	}
            }
        } else {
        	$this->returnData = [
            	'code' => 806,
            	'msg' => $model->getErrors(),
            ];
        } 

        return $this->returnData;

    }


     /*
     * 编辑沟通记录
     */
    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            $data = $request->post();
            $model = CommunicationRecord::findOne([
            	'id' => $data['id'],
            	'status' => CommunicationRecord::STATUS_ACTIVE,
            ]);

            if (!$model) {
                return $this->returnData = [
                    'code' => 807,
                    'msg' => '沟通记录不存在',
                ];
            }
            $data['updated_at'] = date('Y-m-d H:i:s', time());
            if ($model->load($data, '') && $model->validate()) {
            	$model->setAttributes($data);
	            if ($model->save()) {
	                return $this->returnData = [
	                        'code' => 1,
	                        'msg' => '编辑沟通记录成功',
	                ];                
	            } else {
	                return $this->returnData = [
	                        'code' => 0,
	                        'msg' => '编辑沟通记录失败',
	                ];
	            }
            } else {
            	return $this->returnData = [
            		'code' => 806,
            		'msg' => $model->getErrors(),
            	];
            }

        }
    }

    /**
     * 删除沟通记录
     */
    public function actionDelete()
    {
        //判断批量删除
        $ids = Yii::$app->request->get('id', 0);
        $ids = implode(',', array_unique((array)$ids));
        if (empty($ids)) {
            return $this->returnData = [
                'code' => 802,
                'msg' => '请选择要删除的数据',
            ];
        }
        $_where = 'id in (' . $ids . ')';
        
        if ((new CommunicationRecord())->updateCreordStatus($_where)) {
            return $this->returnData = [
                'code' => 1,
                'msg' => '删除沟通记录成功'
            ];
        } else {
            return $this->returnData = [
                'code' => 0,
                'msg' => '删除沟通记录失败',
            ];
        }
    }

}
