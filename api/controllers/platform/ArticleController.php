<?php
namespace api\controllers\platform;

use Yii;
use common\helpers\Utils;
use api\controllers\BaseController;

use common\models\CheckAuth;
use api\models\Article;
use api\models\ArticleNotic;
use api\models\PlatformTeam;
use api\models\UserInfo;

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
     * @name 平台下文章的列表
     * @param $platform_id
     * @return mixed
     */
    public function actionIndex(){
        parent::checkGet();

        $data = Yii::$app->request->get();
        $page = isset($data['page']) ? $data['page'] : 1;
        if($page < 1)
        {
            $page = 1;
        }
        $page_size = 20;

        $list = Article::getArticles($this->platform_id, $page, $page_size);
        if($list['list'])
        {
            foreach($list['list'] as &$item)
            {
                $item['user'] = UserInfo::getInfoByUID($item['uid'], 1);
            }
        }

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
        //验证数据是否有缺失
        if(!$data['title'] || !$data['content'] || !$data['cid'])
        {
            return Utils::returnMsg(1, '请检查参数');
        }

        //向表里插入数据
        $data['uid'] = Yii::$app->user->id;
        $data['platform_id'] = $this->platform_id;

        $params = [
            'title' => $data['title'],
            'content' => $data['content'],
            'uid' => Yii::$app->user->id,
            'type' => $data['type']
        ];
        $result = Article::add($data);
        if(!$result){
            return Utils::returnMsg(1, '添加失败');
        }
        return Utils::returnMsg(0, '添加成功');
    }

    /*
     * @name 文章的展示
     * @param article_id
     * @return mixed
     */
    public function actionInfo(){
        parent::checkGet();

        $id = Yii::$app->request->get('id');
        if(empty($id) || !isset($id)){
            return Utils::returnMsg(1, '缺少必要参数');
        }
        $info = Article::info($id);

        //鉴权
        if(!$info || $info->platform_id != $this->platform_id)
        {
            return Utils::returnMsg(1, '文章不存在');
        }

        return Utils::returnMsg(0, null , $info);
    }

    /*
     * @name 文章的编辑
     * @param 修改参数 和 必要的文章id
     * @return mixed
     */
    public function actionEdit(){
        parent::checkPost();

        $data = Yii::$app->request->post();
        //验证参数
        if(!$data['id'] || !$data['title'] || !$data['content'])
        {
            return Utils::returnMsg(1, '请检查参数');
        }

        //鉴权
        $info = Article::info($data['id']);
        if(!$info || $info->platform_id != $this->platform_id)
        {
            return Utils::returnMsg(1, '文章不存在');
        }

        //修改数据
        $result = Article::edit($data);
        
        if(!$result){
            return Utils::returnMsg(1, '修改失败');
        }
        
        return Utils::returnMsg(0, '修改成功');
    }

    public function actionRemove()
    {
        parent::checkGet();

        $id = Yii::$app->request->get('id');
        if(empty($id) || !isset($id)){
            return Utils::returnMsg(1, '缺少必要参数');
        }
        $info = Article::info($id);


        //鉴权
        if(!$info || $info->platform_id != $this->platform_id)
        {
            return Utils::returnMsg(1, '文章不存在');
        }

        $result = Article::remove($id);
        if(!$result){
            return Utils::returnMsg(1, '删除失败');
        }
        
        return Utils::returnMsg(0, '删除成功');
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