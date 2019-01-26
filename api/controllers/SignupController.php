<?php
namespace api\controllers;

use Yii;
use linslin\yii2\curl;
use api\models\User;

/**
 * 
 */
class SignupController extends BaseController
{
	public $modelClass = 'api\models\User';
	public $state;
    public $session;
    protected $appId = 'wx934cfb6cf3cb6015';
    protected $appSecret = '9ac93b963c6d6a9549626e0ecafc691f';
    protected $accessTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    protected $userInfoUrl = 'https://api.weixin.qq.com/sns/userinfo';

	public function behaviors()
    {
        $behaviors =  parent::behaviors();
        //解决跨域问题
        return ArrayHelper::merge([
            [
                'class' => Cors::className(),
            ],
        ], $behaviors);
    }


    public function init()
    {
        $this->session = Yii::$app->session;
    }

    /**
     * 获取微信callback url
     * @return [type] [description]
     */
    public function actionCallback()
    {      
        $model = new User();
        if (Yii::$app->request->isGet) {
            $data = Yii::$app->request->get();
            if (!empty($data['code']) || !empty($data['state'])) {
                //判断wx_state防止跨站跨站伪造url攻击
                if ($data['state'] != $this->session->get('wx_state')) {
                    return $this->returnData = [
                        'code' => 810,
                        'msg' => '请求不合法',
                    ];
                }
                $this->session->remove('wx_state');
                $curl = new curl\Curl();
                $wxResponse = $curl->setGetParams([
                    'appid' => $this->appId,
                    'secret' => $this->appSecret,
                    'code' => $data['code'],
                    'grant_type' => 'authorization_code',
                ])->get($accessTokenUrl);

                $wxResult = json_decode($wxResponse);
                if (isset($wxResult->errcode) && $wxResult->errcode > 0) {
                    return $this->returnData = [
                        'code' => 811,
                        'msg' => '微信请求授权错误',
                        'data' => $wxResult,
                    ];
                } else {
                    $response = $curl->setGetParams([
                        'access_token' => $wxResult->access_token,
                        'openid' => $wxResult->openid,
                        'lang' => 'zh_CN',
                    ])->get($userInfoUrl);
                    $result = json_decode($response);
                    $wxUser = User::findOne(['wx_unionid' => $result->unionid]);

                    if ($wxUser) {
                        //存在则登录
                        (new LoginForm())->setSession($insertData['username']);
                        return $this->returnData = [
                            'code' => 1,
                            'msg' => '登录成功',
                        ];
                    } else {
                        //不存在则创建新用户
                        $insertData = [
                            'id' => createIncrementId(),
                            'wx_unionid' => $result->unionid,
                            'username' => $result->nickname,
                            'avatar' => $result->headimgurl,
                            'type' => 2,
                        ];
                        if ($model->load($insertData, '') && $model->validate()) {
                            if ($model->save()) {
                                //设置session
                                (new LoginForm())->setSession($insertData['username']);

                                return $this->returnData = [
                                    'code' => 1,
                                    'msg' => '登录成功',
                                ];
                            } else {
                               return $this->returnData = [
                                    'code' => 0,
                                    'msg' => '登录失败',
                                ]; 
                            }
                        }
                    }
                }
            }
            

        }
    }

    public function actionGetstate()
    {
        $state = md5(uniqid(rand(), true));
        
        $session->set('wx_state', $state);
        
        return $this->returnData = [
            'code' => 1,
            'state' => $state,
        ];

    }

}