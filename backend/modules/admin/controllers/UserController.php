<?php
namespace app\modules\admin\controllers;

use app\models\forms\LoginForm;
use app\models\forms\RegisterForm;
use app\models\Role;
use app\models\user\User;
use app\modules\admin\models\BlockForm;
use app\modules\admin\models\UserSearch;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBasicAuth;
use yii\web\NotFoundHttpException;

class UserController extends ActiveController
{
    public $modelClass = "app\models\user\User";

    public $enableCsrfValidation = false;

    protected function findModel($id)
    {
        $model = User::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Пользователь не найден.');
        }
        return $model;
    }
     
    public function behaviors()
    {
        $behaviors = parent::behaviors();
    
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
        ];
        
        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['block', 'delete', 'index', 'view'],
                    'roles' => ['@'],
                ],
            ],
        ];
        
        return $behaviors;
    }

    public function beforeAction($action)
    {
        try {
            return parent::beforeAction($action);
        } catch (\yii\web\UnauthorizedHttpException $e) {
            throw new \yii\web\UnauthorizedHttpException('Это действие доступно только авторизированным пользователям');
        }
    }
    
    public function checkAccess($action, $model = null, $params = [])
    {
        if (Yii::$app->user->identity->role_id !== Role::getOne('admin')) {
            throw new \yii\web\NotFoundHttpException('Страница не найдена');
        }
    }

    public function actionIndex()
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

    public function actionBlock($id)
    {
        $model = new BlockForm();
        $data = \Yii::$app->request->bodyParams;
        $model->load($data, '');
        $block = $model->block($id);

        return $block;
    }
    
}