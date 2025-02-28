<?php
namespace app\controllers;
use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use app\models\recipe\RecipeMark;
use app\models\recipe\RecipeReaction;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class RecipeReactionController extends ActiveController
{
    public $modelClass = 'app\models\recipe\RecipeReaction';

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
                    'actions' => ['create', 'delete'],
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

    public function actionCreate()
    {
        $recipe_id = Yii::$app->request->post('recipe_id');
        $user_id = Yii::$app->user->id;

        $model = new RecipeReaction();
        $model->user_id = $user_id;
        $model->recipe_id = $recipe_id;

        if($model->save()){
            return ['success' => true, 'model' => $model,];
        }

        return ['success' => false, 'errors' => $model->errors];
    }

    public function actionDelete($recipe_id)
    {
        $model = RecipeReaction::getReaction($recipe_id);
    
        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Лайк убран',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении реакции.'); 
    } 
}