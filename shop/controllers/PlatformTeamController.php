<?php
namespace shop\controllers;


use common\models\CheckAuth;
use shop\models\PlatformTeam;
use shop\models\TeamUser;
use Yii;




/**
 * 后台平台团队管理控制器
 * 1 平台下团队的展示
 * 2 平台下团队的创建
 * 3 平台下团队的编辑
 * 4 平台下团队的增加人员
 * 5 平台下团队人员的删除
 * 6 编辑平台下团队人员权限
 * 7 平台下团队人员的列表
 */
class PlatformTeamController extends BaseController
{

    public $session;
    public $user;
    public $platform_id;
    public function init()
    {

        $this->session = \Yii::$app->session;

        $this->user = $this->session->get('user_id');
        $this->platform_id = $this->session->get('platform_id');
        $this->user  = 1;
        if(!$this->user ){
            exit(json_encode(array('code'=>0,'message'=>'请先登录账号')));
        }
        $isAuth = CheckAuth::isAuth();
        if(!$isAuth){
            exit(json_encode(array('code'=>0,'message'=>'此账号无权限')));
        }

        parent::init();
    }

    /*
     * @name 平台下团队的展示
     * @param 验证了用户权限后查看平台下的团队列表
     * @mixed
     */
    public function actionIndex()
    {
        //通过用户去查平台的信息?还是get接受
        $platformId= $this->request->get('platformId');
        $teamList = (new PlatformTeam())->teamList($platformId);
        exit(json_encode(array('code'=>200,'data'=>$teamList)));
    }

    /*
     * @name 平台下团队的创建
     * @param platform_id name public start_date
     * @return mixed
     */
    public function actionCreateTeam(){
        $data = $this->request->post();
        if(empty($data['platform_id']) || empty($data['name']) || empty($data['public']) || empty($data['start_date'])){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'缺少必要参数')));
        }
        $result = (new PlatformTeam())->teamCreate($data);
        if(!$result){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'失败')));
        }
        exit(json_encode(array('code'=>200,'data'=>'','message'=>'成功')));
    }


    /*
     * @name 编辑平台下团队的信息
     * @param team_id name public start_date
     * @return mixed
     */
    public function actionEditorTeam(){
        $data = $this->request->post();
        if(empty($data['team_id'])){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'缺少必要参数')));
        }
        $result = (new PlatformTeam())->teamEditor($data);
        if(!$result){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'失败')));
        }
        exit(json_encode(array('code'=>200,'data'=>'','message'=>'成功')));
    }

    /*
     * @name 平台下团队的删除 不只是删除团队 还有删除团队的成员
     * @param  team_id
     * @return mixed
     */

    public function actionDeleteTeam(){
        $teamId = $this->request->post('team_id');
        if(empty($data['team_id'])){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'缺少必要参数')));
        }
        $result = (new PlatformTeam())->teamDelete($data);
        if(!$result){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'失败')));
        }
        exit(json_encode(array('code'=>200,'data'=>'','message'=>'成功')));


    }






    /*
     * @name 平台下的团队新增成员
     * @param user_id team_id
     * type 1 批量添加 0 单独添加
     * @return mixed
     */
    public function actionAddMembers()
    {
        $data = $this->request->post();
        if(empty($data['user_id']) || empty($data['team_id']) || empty($data['type']) || empty($data['permissions'])){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'缺少必要参数')));
        }
        //批量添加和单独添加
        $result = (new TeamUser())->addMembers($data);
        if(!$result){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'失败')));
        }
        exit(json_encode(array('code'=>200,'data'=>'','message'=>'成功')));
    }

    /*
     * 平台下删除成员
     * @param user_id team_id
     * @type 1批量删除 2单独删除
     * @return mixed
     */
    public function actionDeleteMembers()
    {
        $data = $this->request->post();
        if (empty($data['user_id']) || empty($data['team_id']) || empty($data['type'])) {
            exit(json_encode(array('code' => 100, 'data' => '', 'message' => '缺少必要参数')));
        }
        //批量添加和单独添加
        $result = (new TeamUser())->deleteMembers($data);
        if (!$result) {
            exit(json_encode(array('code' => 100, 'data' => '', 'message' => '失败')));
        }
        exit(json_encode(array('code' => 200, 'data' => '', 'message' => '成功')));
    }

     /*
      * @name 编辑团队成员权限
      * @param user_id team_id
      * @type 1批量编辑 2单独编辑
      * @return mixed
      */

    public function actionEditorMembers()
    {
        $data = $this->request->post();
        //验证参数需要写一个公共model了
        if(empty($data['user_id']) || empty($data['team_id']) || empty($data['type']) || empty($data['permissions'])){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'缺少必要参数')));
        }
        $result = (new TeamUser())->editorMembers($data);
        if (!$result) {
            exit(json_encode(array('code' => 100, 'data' => '', 'message' => '失败')));
        }
        exit(json_encode(array('code' => 200, 'data' => '', 'message' => '成功')));

    }

    /*
     * @name 团队下成员信息列表
     * @param team_id
     * @return mixed
     */
    public function actionMembersList(){
        $teamId = $this->request->post('team_id');
        if(empty($data['platform_id'])){
            exit(json_encode(array('code'=>100,'data'=>'','message'=>'缺少必要参数')));
        }

        $list = (new TeamUser())->membersList($teamId);
        if (!$list) {
            exit(json_encode(array('code' => 100, 'data' => '', 'message' => '失败')));
        }
        exit(json_encode(array('code' => 200, 'data' => $list, 'message' => '成功')));

    }

}