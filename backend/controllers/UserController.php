<?php

namespace app\controllers;

use app\models\forms\LoginForm;
use app\models\forms\RegisterForm;
use app\models\user\User;
use app\models\user\UserSearch;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class UserController extends BaseApiController
{
    public $modelClass = "app\models\user\User";
    public $enableHttpCache = false; // Отключаем HTTP-кеширование для этого контроллера

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['login', 'register', 'search'],
        ];

        return $behaviors;
    }

    protected function findModel($id)
    {
        $model = User::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }
        return $model;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['index']); // Убираем стандартный actionIndex
        return $actions;
    }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => User::find(),
        ]);
    }

    public function actionCurrent()
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            throw new UnauthorizedHttpException('Пользователь не авторизован.');
        }

        return [
            'success' => true,
            'user' => $user->toArray([
                'id',
                'name',
                'surname',
                'login',
                'email',
                'avatar',
                'photo_header',
                'role_id',
            ]),
        ];
    }

    public function actionRegister()
    {
        $model = new RegisterForm();

        if ($model->load(Yii::$app->request->getBodyParams(), '') && $model->validate()) {
            if ($user = $model->register()) {
                return [
                    'success' => true,
                    'user' => [
                        'id' => $user->id,
                        'login' => $user->login,
                        'auth_key' => $user->auth_key,
                    ]
                ];
            }
        }

        return [
            'success' => false,
            'errors' => $model->errors,
            'attributes' => $model->attributes
        ];
    }

    public function actionLogin()
    {
        $model = new LoginForm();

        if ($model->load(Yii::$app->request->getBodyParams(), '') && $model->login()) {
            $user = $this->findModel(Yii::$app->user->id);
            return [
                'success' => true,
                'auth_key' => Yii::$app->user->identity->auth_key,
                'user' => $user->toArray(),
            ];
        }

        return [
            'success' => false,
            'errors' => $model->errors,
            'attributes' => $model->attributes
        ];
    }

    public function actionLogout()
    {
        $authHeader = Yii::$app->request->headers->get('Authorization');
        $authKey = $authHeader && preg_match('/Bearer\s+(.+)/', $authHeader, $matches) 
            ? $matches[1] 
            : null;

        $user = $authKey 
            ? User::findOne(['auth_key' => $authKey]) 
            : Yii::$app->user->identity;

        if ($user === null) {
            throw new UnauthorizedHttpException('Пользователь не авторизован.');
        }

        $user->auth_key = null;
        if ($user->save(false)) {
            Yii::$app->user->logout();
            return ['message' => 'Вы успешно вышли из системы.'];
        }

        throw new ServerErrorHttpException('Не удалось завершить сессию.');
    }

    public function actionSearch()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->getQueryParams());
        
        $dataProvider->query->select(['id', 'login', 'status', 'avatar']);
    
        $dataProvider->query->asArray();

        return $dataProvider->getModels();
    }
}