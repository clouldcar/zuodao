<?php
namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;

use api\models\ArticleCategory;

class ArticleCategoryController extends BaseController
{
	public function init()
    {
        parent::init();
        parent::checkPlatformUser();
    }

    public function actionIndex()
    {
    	parent::checkGet();

    	$data = Yii::$app->request->get();

    	$page = isset($data['page']) ? $data['page'] : 1;
        if($page < 1)
        {
            $page = 1;
        }
        $page_size = 20;

        $list = ArticleCategory::getCategories($this->platform_id, $page, $page_size);

        return Utils::returnMsg(0, null, $list);
    }

    public function actionCreate()
    {
    	parent::checkPost();
        $data = Yii::$app->request->post();

        if(!$data['name'])
        {
        	return Utils::returnMsg(1, '参数有误');
        }

        if(ArticleCategory::getInfo($data['name'], $this->platform_id))
        {
        	return Utils::returnMsg(1, '分类已存在');
        }

        $params = [
        	'name' => $data['name'],
        	'platform_id' => $this->platform_id
        ];

        $model = new ArticleCategory();
        $model->setAttributes($params);

        if ($model->validate() && $model->save()) {
            return Utils::returnMsg(0, '创建成功');
        } else {
            return Utils::returnMsg(1, '创建失败');
        }
    }

    public function actionInfo()
    {

    }

    public function actionEdit()
    {

    }

    public function actionDelete()
    {

    }
}