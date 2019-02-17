<?php
namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;

use common\models\CheckAuth;
use api\models\Article;
use api\models\ArticleNotic;
use api\models\PlatformTeam;
use api\models\TeamUser;

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

    public function init()
    {
        parent::init();
        parent::checkPlatformUser();
    }

    /*
     * @name 文章的创建
     * @param user_id platform_id title content class type send_to feedback_way created_at
     * @return mixed
     */
    public function actionCreate(){
        parent::checkPost();
        $data = Yii::$app->request->post();
        //验证数据是否有缺失
        if(!$data['title'] || !$data['content'] || !$data['cid'])
        {
            return Utils::returnMsg(1, '请检查参数');
        }

        //向表里插入数据
        $data['user_id'] = Yii::$app->user->id;
        $data['platform_id'] = $this->platform_id;
        $result = Article::addArticle($data);
        if(!$result){
            Utils::returnMsg(1, '添加失败');
        }
        Utils::returnMsg(0, '添加成功');
    }

    /*
     * @name 平台下文章的列表
     * @param $platform_id
     * @return mixed
     */
    public function actionArticleList(){
        //或者用session
        $platform_id = \Yii::$app->request->get('platform_id');
        if(!empty($data['platform_id']) || !isset($data['platform_id'])){
            exit(json_encode(array('code'=>0,'message'=>'缺少必要参数')));
        }
        $list = (new Article())->articleList($platform_id);
        exit(json_encode(array('code'=>0,'data'=> $list?$list:[])));
    }

    /*
     * @name 文章的展示
     * @param article_id
     * @return mixed
     */
    public function actionArticleInfo(){
        $id = \Yii::$app->request->get('id');
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