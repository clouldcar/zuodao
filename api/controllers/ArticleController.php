<?php
namespace api\controllers;

use Yii;
use common\models\CheckAuth;
use common\helpers\Utils;
use api\models\Article;
use api\models\ArticleCategory;
use api\models\ArticleNotic;
use api\models\PlatformTeam;
use api\models\TeamUser;
use api\models\TeamArticle;

use api\models\ArticleComments;

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
class ArticleController extends BaseController
{



    /*
     * @name 团队下文章的列表
     * @param $team_id
     * @return mixed
     */
    public function actionIndex(){
        $data = Yii::$app->request->get();
        $page = isset($data['page']) ? $data['page'] : 1;
        if($page < 1)
        {
            $page = 1;
        }
        $page_size = 20;

        if(!isset($data['team_id']) || !isset($data['cid'])){
            return Utils::returnMsg(1, '缺少必要参数');
        }
        $list = Article::getListByTeamId($data['team_id'], $data['cid'], $page, $page_size);

        return Utils::returnMsg(0, null, $list);
    }

    /*
     * @name 文章的创建
     * @param user_id platform_id title content class type send_to feedback_way created_at
     * @return mixed
     */
    public function actionCreate(){
        parent::checkPost();
        $data = Yii::$app->request->post();
        $uid = Yii::$app->user->id;
        //验证数据是否有缺失
        if(empty($data['team_id']) || empty($data['title']) || empty($data['cid']) || empty($data['content']))
        {
            return Utils::returnMsg(1, '参数不正确');
        }

        //判断是否团队成员
        if(!TeamUser::hasUser($data['team_id'], $uid))
        {
            return Utils::returnMsg(1, '非法操作');
        }

        $team_id = $data['team_id'];
        unset($data['team_id']);

        //向表里插入数据
        $data['uid'] = $uid;
        $data['type'] = ArticleCategory::TYPE_ID_TEAM;
        $article_id = Article::add($data);
        if(!$article_id)
        {
            return Utils::returnMsg(1, '添加失败');
        }

        $params = [
            'team_id' => $team_id,
            'article_id' => $article_id
        ];
        $model = new TeamArticle();
        $model->setAttributes($params);
        $model->save();
        
        return Utils::returnMsg(0, '添加成功');
    }

    /*
     * @name 文章的展示
     * @param article_id
     * @return mixed
     */
    public function actionArticleInfo(){
        $id = Yii::$app->request->get('id');
        if(!empty($id) || !isset($id)){
            exit(json_encode(array('code'=>0,'message'=>'缺少必要参数')));
        }
        $list = (new Article())->articleInfo($id);
        exit(json_encode(array('code'=>0,'data'=> $list?$list:[])));
    }

    /*
     * @name 文章的编辑
     * @param 修改参数 和 必要的文章id
     * @return mixed
     */
    public function actionArticleEditor(){
        $data = \Yii::$app->request->post();
        //验证参数

        //修改数据
        $result = (new Article())->articleEditor($data);
        if($result == 100){
            exit(json_encode(array('code'=>100,'message'=>'不存在该文章')));
        }
        if(!$result){
            exit(json_encode(array('code'=>0,'message'=>'修改失败')));
        }
        exit(json_encode(array('code'=>200,'message'=>'修改成功')));

    }

    /*
     * @name 通知列表
     * @name article_id
     * @return mixed
     */
    public function actionNoticeList(){
        $article_id =  \Yii::$app->request->get('article_id');
        if(empty($article_id) || !isset($article_id)){
            exit(json_encode(array('code'=>0,'message'=>'缺少必要参数')));
        }
        $list = (new ArticleNotic())->noticeList($article_id);
        if(!$list){
            exit(json_encode(array('code'=>0,'message'=>'查询失败','data'=>[])));
        }
        exit(json_encode(array('code'=>200,'message'=>'修改成功','data'=>$list)));
    }

    /*
     * @name 用户确认 用户 通知
     * @type 1 用户已读
     *       2 用户确认
     * @return mixed
     */
    public function actionConfirmNotice(){
        $data =  \Yii::$app->request->post();
        if(empty($data['article_id']) || !isset($data['article_id']) || empty($data['user_id']) || !isset($data['user_id'])){
            exit(json_encode(array('code'=>0,'message'=>'缺少必要参数')));
        }
        $result = (new ArticleNotic())->confirmNotice($data);
        if(!$result){
            exit(json_encode(array('code'=>0,'message'=>'确认失败','data'=>'')));
        }
        exit(json_encode(array('code'=>200,'message'=>'确认成功','data'=>'')));
    }

    /**
     * 获取当前文章下所有评论列表
     * @return [type] [description]
     */
    public function actionComments()
    {   
        $article_id = Yii::$app->request->get('article_id');
        $order = Yii::$app->request->get('order') ? Yii::$app->request->get('order') : 'desc';
        $page = Yii::$app->request->get('page') ? Yii::$app->request->get('page') : '1';
        $offset = Yii::$app->request->get('offset') ? Yii::$app->request->get('offset') : ArticleComments::PAGESIZE;
        return (new Article())->getArticleComments($article_id,$order,$page,$offset);
    }

}