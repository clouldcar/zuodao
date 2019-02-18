<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

//跨域session域名配置,获取当前主机名
$host_array = explode('.', $_SERVER["HTTP_HOST"]);
//针对com域名，获取顶级域名
if (count($host_array) == 3) {
    define('DOMAIN', $host_array[1] . '.' . $host_array[2]);
}else
{
    define('DOMAIN', 'zuodao.lg');
}

define('DOMAIN_HOME', 'www.' . DOMAIN);
define('DOMAIN_API', 'api.' . DOMAIN);

return [
    'id' => 'app-zuodao',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'Asia/Shanghai',
    'controllerNamespace' => 'api\controllers',
    'bootstrap' => ['log'],
    'modules' => [],
    'components' => [
        'request' => [
            'csrfParam' => '_csrf-zuodao',
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            // 'identityCookie' => ['name' => '_identity-zuodao', 'httpOnly' => true],
            'identityCookie' => ['name' => '_identity-zuodao', 'httpOnly' => true, 'domain' => '.' . DOMAIN],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'cookieParams' => ['domain' => '.' . DOMAIN, 'lifetime' => 0],
            'timeout' => 3600,
            'name' => 'advanced-zuodao',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
//            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'Student',
                    'pluralize'=> false,
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'CommunicationRecord',
                    'pluralize'=> false,
                ],
                // "<controller:\w+>/<action:\w+>/<id:\d+>" => "<controller>/<action>",
                // "<controller:\w+>/<action:\w+>" => "<controller>/<action>"

            ],
        ],
        'AliyunOss' => [
            'class' => 'api\components\AliyunOss',
        ],

    ],
    'params' => $params,
];
