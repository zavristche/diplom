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
                'multipart/form-data' => 'yii\web\MultipartFormDataParser'
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\user\User',
            'enableAutoLogin' => false,
            'enableSession' => false,
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
                    'levels' => ['error', 'warning', 'info'],
                    'logFile' => '@runtime/logs/app.log',
                ],
            ],
        ],
        'db' => $db,
        
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                'POST api/login' => 'user/login',
                'POST api/register' => 'user/register',
                'POST api/logout' => 'user/logout',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'user',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'POST search' => 'search',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['profile' => 'profile/profile'],
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'PATCH <id:\d+>' => 'update',
                        'DELETE <id:\d+>' => 'delete',
                        'GET <id:\d+>' => 'view',
                    ],
                ],
                
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'recipe',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET recipe' => 'index',
                        'POST search' => 'search',
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
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET collection' => 'index',
                        'GET collection/<id:\d+>' => 'view',
                        'POST collection/<id:\d+>' => 'create',
                        'POST search' => 'search',
                        'PATCH collection/<id:\d+>' => 'update',
                        'DELETE collection/<id:\d+>' => 'delete',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'collection-recipe',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'POST collection-recipe' => 'create',
                        'DELETE collection-recipe/<id:\d+>' => 'delete',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'recipe-mark',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'POST recipe-mark' => 'create',
                        'DELETE recipe-mark/<id:\d+>' => 'delete',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'recipe-reaction',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'POST recipe-reaction' => 'create',
                        'DELETE recipe-reaction' => 'delete',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'mark',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET' => 'index',
                    ],
                ],

                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'product',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET' => 'index',
                    ],
                ],
            ],
        ],
        'on beforeSend' => function ($event) {
            $event->sender->headers->add('Access-Control-Allow-Origin', '*');
        },
        'assetManager' => [
            'bundles' => [
                'yii\bootstrap\BootstrapAsset' => false,
                'yii\bootstrap\BootstrapPluginAsset' => false, 
            ],
        ],
        
    ],
    'as corsFilter' => [
        'class' => \yii\filters\Cors::class,
        'cors' => [
            'Origin' => ['http://localhost:5173'],
            'Access-Control-Request-Method' => ['GET', 'POST', 'PATCH', 'DELETE', 'OPTIONS'],
            'Access-Control-Allow-Credentials' => true,
        ],
    ],
    'params' => $params,
    'modules' => [
        'profile' => [
            'class' => 'app\modules\profile\Module',
            'defaultRoute' => 'profile',
        ],
        'modules' => [
            'admin' => [
                'class' => 'app\modules\admin\Module',
            ],
        ],
    ],
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
