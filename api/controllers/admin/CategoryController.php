<?php
namespace api\controllers\admin;

use api\controllers\BaseController;
use api\models\ArticleCategory;
use common\helpers\Utils;
use Yii;

/**
 * 后台平台文章控制器
 * 1 文章的展示
 * 2 文章的创建
 * 3 文章的编辑
 * 4 文章的列表
 * 5 通知列表
 * 6 点击确认
 * 这里的通知是直接插入数据在缓存表里 然后通过点击来解决问题
 */
class CategoryController extends BaseController {

	public function init() {
		parent::init();
		parent::checkPlatformUser();
	}

	public function actionCreate() {
		parent::checkPost();

		$data = Yii::$app->request->post();

		if (!$data['name']) {
			return Utils::returnMsg(1, '分类名称不能为空');
		}

		if (ArticleCategory::getOperateInfo($data['name'])) {
			return Utils::returnMsg(1, '分类已存在');
		}

		$params = [
			'id' => Utils::createIncrementId(Utils::ID_TYPE_ARTICLE_CATEGORY),
			'name' => $data['name'],
			'type' => ArticleCategory::TYPE_ID_OPERATE,
		];

		$model = new ArticleCategory();
		$model->setAttributes($params);

		if ($model->validate() && $model->save()) {
			return Utils::returnMsg(0, '创建成功');
		} else {
			return Utils::returnMsg(1, '创建失败');
		}
	}

	public function actionList() {
		parent::checkGet();

		$data = Yii::$app->request->get();
		$page = isset($data['page']) ? $data['page'] : 1;

		if ($page < 1) {
			$page = 1;
		}
		$page_size = 20;

		$model = new ArticleCategory();

		// $list = $model->find()
		// 	->select('id,name')
		// 	->where(['type' => 4, 'status' => 0])
		// 	->orderBy('ctime asc')
		// 	->all();
			
		$list = ArticleCategory::getCategoriesByType(4, $page, $page_size);

		return Utils::returnMsg(0, null, $list);

	}

}