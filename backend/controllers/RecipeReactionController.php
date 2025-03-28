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
use app\controllers\BaseApiController;

class RecipeReactionController extends BaseApiController
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
            throw new \yii\web\UnauthorizedHttpException('Это действие доступно только авторизированным пользователям');
        }
    }

    public function actionCreate()
    {
        $data = Yii::$app->request->getBodyParams();
        $recipe = Recipe::findOne($data['recipe_id']);

        if($recipe->user_id == Yii::$app->user->id){
            return [
                'success' => false,
                'message' => 'Вы не можете лайкнуть свой собственный рецепт'
            ];
        }

        $model = new RecipeReaction([
            'recipe_id' => $data['recipe_id'],
            'user_id' => Yii::$app->user->id,
        ]);

        if($model->save()){
            return ['success' => true, 'model' => $model->attributes,];
        }

        return ['success' => false, 'errors' => $model->errors, $model->attributes];
    }

    public function actionDelete()
    {
        $data = Yii::$app->request->getBodyParams();
        $model = RecipeReaction::findOne(['recipe_id' => $data['recipe_id'], 'user_id' => Yii::$app->user->id]);
        
        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Лайк убран',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении реакции.'); 
    } 
}