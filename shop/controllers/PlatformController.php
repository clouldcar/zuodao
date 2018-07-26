<?php
namespace shop\controllers;


use common\models\CheckAuth;
use shop\models\InviteUser;
use shop\modelS\PlatformUser;
use shop\models\User;
use Yii;




/**
 * 后台平台员工管理控制器
 */
class PlatformController extends BaseController
{
    const  INVITE_STATUS_ON  = 1;
    const  INVITE_STATUS_OFF = 0;
    const  INVITE_TYPE_IN = 1;
    const  INVITE_TYPE_OUT = 0;
    const  USER_PASSWORD = 123456;
    const  PLATFORM_STATUS = 1;
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
     * @name 员工列表页
     * @param 平台id
     * @mixed
     */
    public function actionIndex()
    {

        $platform_id = $this->platform_id;
        //通过传入条件查询user信息 写在user model里
        $user_list = (new PlatformUser())->platformUsers($platform_id,0);
        if(!empty($user_list)){
            exit(json_encode(array('code'=>0,'message'=>'没用可用用户','data'=>[])));
        }
        exit(json_encode(array('code'=>200,'message'=>'成功','data'=>$user_list)));
    }

    /*
     * @name 给平台添加员工
     * @param platform_id user_id name phone quanxian status type
     * type 为1 是站内邀请
     * @mixed
     */
    public function actionAddEmployees(){

        //增加员工或者修改员工信息 使用user_id 来判断是否有
        //需要一个添加员工表
        $post = $this->platform_id;

        $param = [
            'platform_id'=> $post['platform_id'],
            'name'       => $post['name'],
            'phone'      => $post['phone'],
            'permissions'    => $post['permissions'],
            'status'     =>self::INVITE_STATUS_ON,
            'type'       =>self::INVITE_TYPE_OUT
        ];
        $db = Yii::$app->db;
        $tran = $db->beginTransaction();

        //可以用过user_id判断 也可以通过type 来判断是否是站内邀请
        if($post['type'] == 1){
            $param['status'] = self::INVITE_STATUS_OFF ;
            $param['type'] = self::INVITE_TYPE_IN;
            //向邀请缓存表插入数据
            $invite = (new InviteUser())->addInvite($param);
            if(!$invite){
                $tran->rollBack();
                exit(json_encode(array('code'=>0,'message'=>'邀请失败')));
            }

            $param_invite = [
                'invite_id'=> $invite,
                'type'       => 1,
                'content'      => $post['content'],
                'name'    => $post['name']
            ];

            //向扩展表插入数据
            $invite_instation = (new InviteUser())->addInviteExtends($param_invite);
            if(!$invite_instation){
                $tran->rollBack();
                exit(json_encode(array('code'=>0,'message'=>'邀请失败')));
            }
            $tran->commit();
            exit(json_encode(array('code'=>200,'message'=>'邀请成功')));
        }else{
            //准备向user表插入数据
            //准备向平台和用户权限关联表插入数据
            //这些插入数据都是非站内邀请的

            $invite = (new InviteUser())->addInvite($param);
            if(!$invite){
                $tran->rollBack();
                exit(json_encode(array('code'=>0,'message'=>'邀请失败')));
            }

            $param_user = [
                'username' => $post['name'],
                'password' => self::USER_PASSWORD,
                'phone'    =>  $post['name'],
            ];
            $user_id = (new User())->addUser($param_user);
            if(!$user_id){

                exit(json_encode(array('code'=>0,'message'=>'邀请失败')));
            }

            $param_platform = [
                'user_id'    =>$user_id,
                'platform'   =>$post['platform_id'],
                'permissions'=>$post['permissions'],
                'status'     =>self::PLATFORM_STATUS
            ];
            $platform = (new PlatformUser())->addEmployees($param_platform);
            if(!$platform){
                $tran->rollBack();
                exit(json_encode(array('code'=>0,'message'=>'邀请失败')));
            }
            $tran->commit();
            exit(json_encode(array('code'=>200,'message'=>'邀请成功')));
        }

    }

    /*
     * @name 返回平台下的学员信息
     * @param platform_id
     * @return mixed
     */
    public function platformUser(){
        $platform_id = \Yii::$app->request->get('platform_id');
        if(empty($platform_id)) exit(json_encode(array('code'=>0,'message'=>'缺少平台id')));

        $data = (new PlatformUser())->platformUsers($platform_id,1);
    }

}