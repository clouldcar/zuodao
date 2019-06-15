<?php
namespace api\controllers;

use Yii;

use linslin\yii2\curl;
use common\helpers\Utils;
use api\models\User;
use api\models\LoginForm;

class WechartController extends BaseController
{
    private $appId = 'wx0c2876cfb615aa3e';
    private $appSecret = 'e91e5a852a2f1bad7d3fd835f68b18aa';
    private $accessTokenUrl = 'https://api.weixin.qq.com/sns/oauth2/access_token';
    private $userInfoUrl = 'https://api.weixin.qq.com/sns/userinfo';

    public function actionAuth() {
        parent::checkGet();

        $data = Yii::$app->request->get();

        //临时票据code
        if(!isset($data['code']) || empty($data['state'])) 
        {
            return Utils::returnMsg(1, "参数错误，请重试");
        }

        //判断wx_state防止跨站跨站伪造url攻击
        if ($data['state'] != Yii::$app->session->get('wx_state')) {
            return Utils::returnMsg(1, "非法请求，请重试");
        }
        //清除state
        Yii::$app->session->remove('wx_state');

        //获取access_token、openID
        $curl = new curl\Curl();
        $wxResponse = $curl->setGetParams([
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'code' => $data['code'],
            'grant_type' => 'authorization_code',
        ])->get($this->accessTokenUrl);
        $wxResult = json_decode($wxResponse);

        if (isset($wxResult->errcode) && $wxResult->errcode > 0) {
            return Utils::returnMsg(1, '微信请求授权错误', $wxResult);
        }

        //获取用户信息
        $response = $curl->setGetParams([
            'access_token' => $wxResult->access_token,
            'openid' => $wxResult->openid,
            'lang' => 'zh_CN',
        ])->get($this->userInfoUrl);
        $result = json_decode($response);

        //检查用户表
        $user = User::findOne(['wx_unionid' => $result->unionid]);

        //已存在->登录
        if ($user) {
            $result = Yii::$app->user->login($user, 3600 * 24* 30);
            if($result)
            {
                if(empty(Yii::$app->user->identity->updated_at))
                {
                    return $this->redirect(Yii::$app->params['baseUrl'] . '/member/supplement');
                }
                else
                {
                    return $this->redirect(Yii::$app->params['baseUrl'] . '/');
                }
            }
            else
            {
                die('登录失败，请重试');
            }
        }

        //不存在，注册
        $insertData = [
            'id' => Utils::createIncrementId(Utils::ID_TYPE_USER),
            // 'real_name' => $result->nickname,
            // 'avatar' => $result->headimgurl,
            'wx_unionid' => $result->unionid,
            'type' => 1,
        ];

        $model = new User();
        $model->setAttributes($insertData);
        if ($model->validate()) 
        {
            $model->addUser($insertData);
            Yii::$app->user->login($model, 3600 * 24* 30);
            return $this->redirect(Yii::$app->params['baseUrl'] . '#/member/supplement');
        }

        die('登录失败');
    }

    //获取state
    public function actionGetState()
    {
        $state = md5(uniqid(rand(), true));
        
        Yii::$app->session->set('wx_state', $state);

        return Utils::returnMsg(0, null, $state);
    }
}