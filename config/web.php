<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'name' => 'AdminLTE Yii2 App',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    
    // 1. WAJIB: Memastikan seluruh aplikasi berjalan dalam zona waktu UTC murni
    'timeZone' => 'UTC',
    'language' => 'id-ID',

    // 2. Default route saat membuka URL root (/)
    'defaultRoute' => 'dashboard',

    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! Pastikan key ini berupa string acak yang unik untuk validasi cookie
            'cookieValidationKey' => 'yii2-adminlte-secure-key-2026',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['/auth/login'], // Otomatis redirect ke /auth/login jika belum login
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        
        // 3. Cache-busting otomatis untuk asset CSS & JS
        'assetManager' => [
            'appendTimestamp' => true,
        ],

        // 4. URL Manager dengan Shorthand Rules
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => [
                // Shorthand Auth & Menu Utama
                'login'     => 'auth/login',
                'register'  => 'auth/register',
                'logout'    => 'auth/logout',
                'dashboard' => 'dashboard/index',
                'profile'   => 'profile/index',
                'users'     => 'user/index',
                'settings'  => 'setting/index',

                // Rule dinamis umum parameter ID
                '<controller:[\w\-]+>/<id:\d+>' => '<controller>/view',
                '<controller:[\w\-]+>/<action:[\w\-]+>/<id:\d+>' => '<controller>/<action>',
                '<controller:[\w\-]+>/<action:[\w\-]+>' => '<controller>/<action>',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // Penyesuaian konfigurasi untuk mode 'dev'
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => \yii\gii\Module::class,
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;