<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'language' => 'ru-RU',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'ADSFSDFSDF',
            'baseUrl' => '',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\user\User',
            'enableAutoLogin' => false,
            'enableSession' => false, // Отключаем сессии для REST API
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => true,
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
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                'POST login' => 'user/login',
                'POST register' => 'user/register',
                'POST logout' => 'user/logout',
                'PATCH profile/<id:\d+>' => 'user/update',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'recipe',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET recipe' => 'index',
                        'GET recipe/<id:\d+>' => 'view',
                        'POST recipe' => 'create',
                        'PATCH recipe/<id:\d+>' => 'update',
                        'DELETE recipe/<id:\d+>' => 'delete',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'collection',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'GET collection' => 'index',
                        'GET collection/<id:\d+>' => 'view',
                        'POST collection/<id:\d+>' => 'create',
                        'PATCH collection/<id:\d+>' => 'update',
                        'DELETE collection/<id:\d+>' => 'delete',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'collection-recipe',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST collection-recipe' => 'create',
                        'DELETE collection-recipe/<id:\d+>' => 'delete',
                    ],
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'recipe-mark',
                    'pluralize' => false,
                    'extraPatterns' => [
                        'POST recipe-mark' => 'create',
                        'DELETE recipe-mark/<id:\d+>' => 'delete',
                    ],
                ],
            ],
        ],
        'on beforeSend' => function ($event) {
            $event->sender->headers->add('Access-Control-Allow-Origin', '*');
        },
        'assetManager' => [
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => false, // Отключает CSS Bootstrap
                'yii\bootstrap\BootstrapPluginAsset' => false, 
            ],
        ],
        
    ],
    'as corsFilter' => [
        'class' => \yii\filters\Cors::class,
        'cors' => [
            'Origin' => ['http://localhost:5173'], // Адрес фронтенда
            'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
            'Access-Control-Allow-Credentials' => true,
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        'allowedIPs' => ['*'],
    ];
}

return $config;
