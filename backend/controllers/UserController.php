<?php
namespace app\controllers;

use app\models\forms\LoginForm;
use app\models\forms\RegisterForm;
use app\models\user\User;
use app\models\user\UserSearch;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBasicAuth;
use app\controllers\BaseApiController;

class UserController extends BaseApiController
{
    public $modelClass = "app\models\user\User";

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['login', 'register', 'search',],
        ];

        return $behaviors;
    }

    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
    
        return true;
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
      
      if (Yii::$app->request->isPost) {
        $postData = Yii::$app->request->getBodyParams();
        
        if ($model->load($postData, '')) {
          if ($model->validate() && $user = $model->register()) {
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
        
        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->getBodyParams();
            
            if ($model->load($postData, '') && $model->login()) {
                return [
                    'success' => true,
                    'auth_key' => Yii::$app->user->identity->auth_key,
                    'user_id' => Yii::$app->user->id
                ];
            }
            
            return [
                'success' => false,
                'errors' => $model->errors,
                'attributes' => $model->attributes
            ];
        }
        
        throw new \yii\web\BadRequestHttpException('Invalid request');
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
        $params = $request->getQueryParams();
        
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search($params);
        $models = $dataProvider->getModels();
       
        $result = array_map(function ($model) {

            $data = $model->toArray([], ['name', 'surname', 'login']);

            if (isset($data)) {
                unset($data['auth_key'], $data['password']);
            }
            return $data;
        }, $models);
        return $result;
    }
    
}