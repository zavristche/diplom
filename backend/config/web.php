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

                //Пользователь
                'POST api/login' => 'user/login',
                'POST api/register' => 'user/register',
                'POST api/logout' => 'user/logout',
                // 'api/admin/recipe' => 'admin/recipe/index',

                //Пользователь / поиск
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'user',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET search' => 'search',
                        'POST validate-field' => 'validateField',
                        'GET current' => 'current',
                    ],
                ],

                //Профиль
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['profile' => 'profile/profile'],
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET <id:\d+>' => 'view',
                    ],
                ],

                //Настройки / Аккаунт
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => ['profile/setting/account' => 'profile/setting/user'],
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'PATCH <id:\d+>' => 'update',
                    ],
                ],

                //Настройки / Метки
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'profile/setting/preference-mark',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'POST' => 'create',
                    ],
                ],

                //Настройки / Продукты
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'profile/setting/preference-product',
                    'pluralize' => false,
                    'prefix' => 'api',
                ],

                //Админ-панель / Рецепты
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'admin/recipe',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'PATCH <id:\d+>/cancel' => 'cancel',
                        'POST <id:\d+>/apply' => 'apply',
                        'GET' => 'index',
                        'GET <id:\d+>' => 'view',
                    ],
                ],

                //Админ-панель / Юзеры
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'admin/user',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'PATCH <id:\d+>/block' => 'block',
                        'POST <id:\d+>/unblock' => 'unblock',
                        'GET <id:\d+>' => 'view',
                    ],
                ],

                //Рецепт
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'recipe',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET search' => 'search',
                        'GET create-data' => 'create-data',
                        'GET random' => 'random',
                    ],
                ],

                //Реакции рецепта
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'recipe-reaction',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'POST check' => 'check',
                        'POST' => 'create', // POST /api/recipe-reaction → actionCreate
                        'DELETE' => 'delete', // DELETE /api/recipe-reaction → actionDelete
                    ],
                ],

                //Рецепт в коллекции
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

                //Комментарий
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'comment',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        // 'GET search' => 'search',
                    ],
                ],

                //Коллекция
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'collection',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET search' => 'search',
                        'GET create-data' => 'create-data',
                    ],
                ],

                //Реакции коллекции
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'collection-reaction',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'POST check' => 'check',
                        'DELETE' => 'delete',
                    ],
                ],

                //Поиск
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'search',
                    'pluralize' => false,
                    'prefix' => 'api',
                    'extraPatterns' => [
                        'GET data' => 'data',
                    ],
                ],
            ],
        ],
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
            'Access-Control-Request-Headers' => ['Content-Type', 'Authorization'],
            'Access-Control-Allow-Credentials' => false,
            'Access-Control-Max-Age' => 86400,
            'Access-Control-Expose-Headers' => ['X-Pagination-Total-Count'],
        ],
    ],
    'params' => $params,
    'modules' => [
        'profile' => [
            'class' => 'app\modules\profile\Module',
            // 'defaultRoute' => 'profile',
        ],
        'admin' => [
            'class' => 'app\modules\admin\Module',
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
