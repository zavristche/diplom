<?php
namespace app\controllers;

use app\models\forms\LoginForm;
use app\models\forms\RegisterForm;
use app\models\user\User;
use app\modules\profile\models\UserSearch;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBasicAuth;

class UserController extends ActiveController
{
    public $modelClass = "app\models\user\User";

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

    public function actionSearch()
    {
        $request = Yii::$app->request;
        $params = $request->post();
    
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($params);

        $models = $dataProvider->getModels();

        $result = array_map(function ($model) {

            $data = $model->toArray([], ['title', 'name']);
    
            if (isset($data)) {
                unset($data['auth_key'], $data['password']);
            }
    
            return $data;
        }, $models);
    
        return $result;
    }
    
}