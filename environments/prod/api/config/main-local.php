<?php
return [
    'components' => [
    	'db' => [
            'class' => 'yii\db\Connection',
            'dsn' => 'mysql:host=localhost;dbname=zuodao',
            'username' => 'zuodao',
            'password' => 'QjYOWkaPXdSDN90F',
            'charset' => 'utf8',
            'tablePrefix' => 'shop_'
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'enableSession' => true
        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '',
        ],
    ],
];
