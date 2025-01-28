<?php
namespace app\controllers;

use app\models\forms\LoginForm;
use app\models\forms\RegisterForm;
use app\models\user\User;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;

class UserController extends ActiveController
{
    public $modelClass = "app\models\user\User";

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['login', 'register',],
        ];

        return $behaviors;
    }

    //BASIC AUTH
    // public function behaviors()
    // {
    //     $behaviors = parent::behaviors();

    //     $behaviors['authenticator'] = [
    //         'class' => \yii\filters\auth\HttpBasicAuth::class,
    //         'auth' => [User::class, 'findIdentityByBasicAuth'], // Метод модели User/
    //         'except' => ['login', 'register', 'logout'], // Исключаем метод login
    //     ];

    //     return $behaviors;
    // }

    public function actionIndex()
    {
        return new ActiveDataProvider([
            'query' => User::find(),
        ]);
    }

    protected function serializeData($data)
    {
        if ($data instanceof User) {
            return $data;
        }
        return $data;

    }

    public function actionRegister()
    {
        $model = new RegisterForm();

        if(Yii::$app->request->isPost){
            if ($model->load(Yii::$app->request->post())){

                if ($model->validate() && $user = $model->register()) {
                    return [
                        'id' => $user->id,
                        'login' => $user->login,
                        'auth_key' => $user->auth_key,
                    ];
                }        
            }
        }
        return [
            'attributes' => $model->attributes,
            'errors' => $model->errors,
        ];
    }

    public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return [
                'auth_key' => Yii::$app->user->identity->auth_key,
            ];

        }
        $model->password = '';
        return [
            // $model->load(Yii::$app->request->post()),
            'attributes' => $model->attributes,
            'errors' => $model->errors,
        ];
    }

    public function actionLogout()
    {
        $user = Yii::$app->user->identity;
        if ($user === null) {
            throw new \yii\web\UnauthorizedHttpException('Пользователь не авторизован.');
        }
        if ($user) {
            $user->auth_key = null;
            $user->save(false);
            Yii::$app->user->logout();
            return ['message' => 'Вы успешно вышли из системы.'];
        }
    
        throw new \yii\web\ServerErrorHttpException('Не удалось завершить сессию.');
    }

    //BASIC AUTH
    // public function actionLogin()
    // {
    //     $username = Yii::$app->request->post('username');
    //     $password = Yii::$app->request->post('password');
    
    //     $authHeader = Yii::$app->request->headers->get('Authorization');
    //     if ($authHeader !== null && preg_match('/^Basic\s+(.*)$/i', $authHeader, $matches)) {
    //         list($username, $password) = explode(':', base64_decode($matches[1]), 2);
    //     }
    
    //     $user = User::findByUsername($username);
    
    //     if ($user === null || !$user->validatePassword($password)) {
    //         throw new \yii\web\UnauthorizedHttpException('Invalid username or password.');
    //     }
    
    //     $token = Yii::$app->security->generateRandomString();
    //     $user->auth_key = $token;
    //     $user->save(false);
    
    //     return [
    //         // 'login' => $user->login,
    //         // 'password' => $user->password,
    //         'auth_key' => $user->authKey,
    //     ];
    // }
    
}