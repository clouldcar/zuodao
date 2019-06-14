<?php
namespace api\controllers;

use Yii;
use common\helpers\Utils;
use api\models\Article;
use api\models\ArticleCategory;

/**
* 老P专区
*/

class GraduateController extends BaseController
{

    public function actionGlobal()
    {
        parent::checkGet();
        $data = Yii::$app->request->get();
        $uid = Yii::$app->user->id;
        $type = ArticleCategory::TYPE_ID_GRAD;

        $category = ArticleCategory::getCategoriesByType($type, 1, 10);

        $result[
            'categories' => $category['list']
        ];

        return Utils::returnMsg(0, null, $result);
    }

    public function actionIndex()
    {
        parent::checkGet();
        $data = Yii::$app->request->get();
        $uid = Yii::$app->user->id;

        $page = isset($data['page']) ? $data['page'] : 1;
        if ($page < 1) {
            $page = 1;
        }
        $page_size = 20;

        if (!isset($data['cid']) || !$data['cid']) {
            return Utils::returnMsg(1, '缺少必要参数');
        }
        $list = Article::getListByCId($data['cid'], $page, $page_size);

        return Utils::returnMsg(0, null, $list);
    }
}