<?php
namespace api\controllers;

use Yii;
use common\helpers\Utils;
use api\models\ArticleCategory;

class CategoryController extends BaseController
{

    public function init()
    {
        parent::init();
        parent::checkLogin();
    }

    public function actionCreate()
    {
        parent::checkPost();

        $data = Yii::$app->request->post();

        if(!$data['name'] || !$data['team_id'])
        {
            return Utils::returnMsg(1, '参数有误');
        }

        if(ArticleCategory::getInfoByTeamId($data['name'], $data['team_id']))
        {
            return Utils::returnMsg(1, '分类已存在');
        }

        $params = [
            'id' => Utils::createIncrementId(Utils::ID_TYPE_ARTICLE_CATEGORY),
            'name' => $data['name'],
            'type' => ArticleCategory::TYPE_ID_TEAM,
            'team_id' => $data['team_id']
        ];

        $model = new ArticleCategory();
        $model->setAttributes($params);

        if ($model->validate() && $model->save()) {
            return Utils::returnMsg(0, '创建成功');
        } else {
            return Utils::returnMsg(1, '创建失败');
        }
    }

    public function actionList()
    {
        parent::checkGet();

        $data = Yii::$app->request->get();

        if(!$data['team_id'])
        {
            return Utils::returnMsg(1, '参数有误');
        }

        $model = new ArticleCategory();

        $list = $model->find()
                    ->select('id,name')
                    ->where(['team_id' => $data['team_id'], 'status' => 0])
                    ->orderBy('ctime asc')
                    ->all();

        return Utils::returnMsg(0, null, $list);

    }

}