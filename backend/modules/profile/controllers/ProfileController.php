<?php

namespace app\modules\profile\controllers;

use app\models\user\User;
use app\modules\profile\models\UpdateForm;
use Yii;
use yii\filters\AccessControl;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class ProfileController extends ActiveController
{
    public $modelClass = "app\models\user\User";

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['DELETE'],
                'update' => ['PATCH'],
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['index', 'view', 'search'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                ],
                [
                    'allow' => true,
                    'actions' => ['update', 'delete'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['delete']);
        unset($actions['update']);
        unset($actions['create']);

        return $actions;
    }

    public function beforeAction($action)
    {
        try {
            return parent::beforeAction($action);
        } catch (\yii\web\UnauthorizedHttpException $e) {
            // Обработка исключения с кастомным сообщением
            throw new \yii\web\UnauthorizedHttpException('Это действие доступно только авторизированным пользователям');
        }
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'update') {
            if ($model->id !== \Yii::$app->user->id)
                throw new \yii\web\ForbiddenHttpException(sprintf('Доступ запрещен', $action));
        }
    }

    public function actionUpdate($id)
    {
        $model = new UpdateForm(['id' => $id]);
        $data = \Yii::$app->request->bodyParams;
        $model->load($data, '');
        $user = $model->update($id);

        return $user;
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            unset($model['auth_key']);
            unset($model['password']);
            return $model;
        }
        throw new NotFoundHttpException('Страница не найдена');
    }
}
