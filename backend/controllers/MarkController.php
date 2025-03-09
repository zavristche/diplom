<?php
namespace app\controllers;
use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use app\models\mark\Mark;
use app\models\recipe\RecipeReaction;
use Yii;
use yii\rest\ActiveController;
use yii\web\Link;
use yii\web\NotFoundHttpException;
use app\controllers\BaseApiController;

class MarkController extends BaseApiController
{
    public $modelClass = 'app\models\mark\Mark';

    public $enableCsrfValidation = false;
     
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function getLinks()
    {
        $url = Yii::$app->params['frontendUrl'];
        return [
            Link::REL_SELF => [
                'method' => 'GET',
                'href' => $url . '/recipe/' . $this->id,
            ],
            'edit' => [
                'method' => 'PATCH',
                'href' => $url . '/recipe/' . $this->id,
            ],
            'delete' => [
                'method' => 'DELETE',
                'href' => $url . '/recipe/' . $this->id,
            ],
        ];
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['update']);
        unset($actions['index']);

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

    public function actionIndex()
    {
        $models = Mark::find()
        ->joinWith('type')
        ->orderBy(['mark_type.title' => SORT_ASC, 'title' => SORT_ASC])
        ->asArray()
        ->all();

        $result = [];
        foreach ($models as $model) {
            $result[$model['type']['title']][] = $model;
        }

        return $result;
    }
}