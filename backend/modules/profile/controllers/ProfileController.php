<?php

namespace app\modules\profile\controllers;

use app\models\user\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\controllers\BaseApiController;

class ProfileController extends BaseApiController
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
