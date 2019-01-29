<?php
namespace api\controllers;


use Yii;

use common\helpers\Utils;
use yii\helpers\ArrayHelper;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\filters\Cors;
use api\models\Team;
use api\models\TeamUser;


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
class TeamController extends BaseController
{
    //身份：学员
    const LEVEL_STUDENT = 0;
    //教练
    const LEVEL_COACH  = 1;

    //状态：正常
    const STATUS_NORMAL = 0;
    //删除
    const STATUS_DELETE = 1;
    //未审核
    const STATUS_UNAUDITED = 2;


    public $session;
    public $user;
    public $platform_id;

    public function behaviors()
    {
        $behaviors =  parent::behaviors();
        return ArrayHelper::merge([
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ], $behaviors);
    }

    /*
     * @name 平台下团队的展示
     * @param 验证了用户权限后查看平台下的团队列表
     * @mixed
     */
    public function actionIndex()
    {
        $uid = Yii::$app->user->id;
        $model = new Team();
        $list = $model->teamList($uid);

        return Utils::returnMsg(0, null, $list);
    }

    /*
     * @name 创建团队
     * @return mixed
     */
    public function actionCreate()
    {
        //TODO 没有做去重逻辑
        $data = Yii::$app->request->post();

        if(empty($data['platform_name']) || empty($data['name']))
        {
            return Utils::returnMsg('1', '缺少必要参数');
        }
        $data['id'] = Utils::createIncrementId(Utils::ID_TYPE_TEAM);
        $data['uid'] = Yii::$app->user->id;
        $result = (new Team())->teamCreate($data);
        if(!$result)
        {
            return Utils::returnMsg('1', '创建失败');
        }

        //增加团队管理员
        $member = array(
            'team_id' => $data['id'],
            'uid' => $data['uid'],
            'permissions' => self::LEVEL_STUDENT,
            'status' => self::STATUS_NORMAL
        );

        (new TeamUser())->addMember($member);

        return Utils::returnMsg('0', '创建成功');
    }

    public function actionDetail()
    {
        $data = Yii::$app->request->get();
        if(empty($data['id']))
        {
            return Utils::returnMsg('1', '缺少必要参数');
        }

        //团队表
        $teamInfo = (new Team())->teamInfo($data['id']);
        if(!$teamInfo)
        {
            //TODO 404处理
            return Utils::redirectMsg('404');
        }

        //是否是管理员
        $teamInfo['is_manager'] = $this->isManager($teamInfo['uid']);

        //团队成员
        $teamInfo['members'] = TeamUser::membersList($data['id']);

        return Utils::returnMsg(0, null, $teamInfo);
    }

    //是否是管理员
    private function isManager($teamUid)
    {
        return ($teamUid == Yii::$app->user->id) ? 1 : 0;
    }


    /*
     * @name 编辑平台下团队的信息
     * @param team_id name public start_date
     * @return mixed
     */
    public function actionEditorTeam(){
        $data = $this->request->post();
        if(empty($data['id']))
        {
            //TODO 403处理
            return Utils::redirectMsg('403');
        }

        //团队信息
        $model = new Team();
        $teamInfo = $model->getInfo($data['id']);
        //TODO 管理员权限验证
        if(!$this->isManager($teamInfo['uid']))
        {
            //TODO 403处理
            return Utils::redirectMsg('403');
        }

        //要修改的字段
        $params = array(
            'platform_name' => $data['platform_name'],
            'ideal' => $data['ideal'],
            'logo' => $data['logo'],
            'visions_map' => $data['visions_map']
        );

        $result = $model->teamEditor($params);
        if(!$result){
            return Utils::returnMsg(0, '失败');
        }

        return Utils::returnMsg(0, '成功');
    }

    /*
     * @name 平台下团队的删除 不只是删除团队 还有删除团队的成员
     * @param  team_id
     * @return mixed
     */

    /*
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
    */






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
     * @param id
     * @return mixed
     */
    public function actionMemberList(){
        $teamId = Yii::$app->request->get('id');

        if(!$teamId) {
            //TODO 403处理
            return Utils::redirectMsg('403');
        }

        //团队信息
        $model = new Team();
        $teamInfo = $model->teamInfo($teamId);
        //TODO 管理员权限验证
        if(!$this->isManager($teamInfo['uid']))
        {
            //TODO 403处理
            return Utils::redirectMsg('403');
        }

        $list = (new TeamUser())->membersList($teamId);
        
        return Utils::returnMsg(0, null, $list);
    }

    /**
    * 修改成员身份
    */
    public function actionMemberLevel()
    {
        $teamId = Yii::$app->request->post('id');
        $uid = Yii::$app->request->post('uid');
        if(!$teamId || !$uid) {
            //TODO 403处理
            return Utils::redirectMsg('403');
        }

        //团队信息
        $model = new Team();
        $teamInfo = $model->teamInfo($teamId);
        //TODO 管理员权限验证
        if(!$this->isManager($teamInfo['uid']))
        {
            //TODO 403处理
            return Utils::redirectMsg('403');
        }

        //判断是否团队成员

        //检查权限内容
        $permissions = [0,1,2];

        //修改

    }

}