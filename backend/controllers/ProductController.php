<?php
namespace app\controllers;
use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use app\models\mark\Mark;
use app\models\product\Product;
use app\models\recipe\RecipeReaction;
use Yii;
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class ProductController extends ActiveController
{
    public $modelClass = 'app\models\product\Product';

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
        $models = Product::find()
        ->joinWith('type')
        ->orderBy(['product_type.title' => SORT_ASC, 'title' => SORT_ASC])
        ->asArray()
        ->all();

        $result = [];
        foreach ($models as $model) {
            $result[$model['type']['title']][] = $model;
        }

        return $result;
    }
}