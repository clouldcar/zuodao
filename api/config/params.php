<?php
return [
    'adminEmail' => 'admin@example.com',
    'baseUrl' => 'http://www.zuodao.club/',
    'aliyun' => [
    	'accessKeyId'=>'LTAIVBWvIkd7jKXi',
        'accessKeySecret'=>'Mw9k7bjpCVFZxBfS5fXKmfO8ayIkaW'
    ],
    'sms' => [
    	'regionId' => 'cn-hangzhou',
    	'product' => 'Dysmsapi',
    	'version' => '2017-05-25',
    	'action' => 'SendSms',
    	'method' => 'POST',
    	'SignName' => '做到',
    	'TemplateCode' => 'SMS_138650016'
    ],
    'oss' =>[
        'bucket' => 'zd-avatar',
        'endPoint' => 'oss-cn-beijing.aliyuncs.com',
        'url' => 'http://zd-avatar.oss-cn-beijing.aliyuncs.com/'
    ]
];
