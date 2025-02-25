<?php
namespace app\controllers;

use app\models\collection\Collection;
use app\models\collection\CollectionRecipe;
use yii\filters\AccessControl;
use Yii;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class CollectionRecipeController extends ActiveController
{
    public $modelClass = 'app\models\collection\CollectionRecipe';

    protected function findModel($id)
    {
        $model = CollectionRecipe::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Рецепт в коллекции не найден.');
        }
        return $model;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['index', 'view'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view',],
                ],
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'delete',],
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
        $collection_id = Yii::$app->request->post('collection_id');
        $recipe_id = Yii::$app->request->post('recipe_id');

        if (!$collection_id || !$recipe_id) {
            throw new BadRequestHttpException("Необходимо передать collection_id и recipe_id");
        }

        $collection = Collection::findOne(['id' => $collection_id]);
            
        if($collection->user_id !== Yii::$app->user->id){
            throw new \yii\web\UnauthorizedHttpException('Вы можете добавлять рецепты только свои коллекции');
        }

        if(CollectionRecipe::findOne(['recipe_id' => $recipe_id])){
            throw new \yii\web\ConflictHttpException('Рецепт уже есть в коллекции');
        }

        $model = new CollectionRecipe();
        $model->collection_id = $collection_id;
        $model->recipe_id = $recipe_id;

        if($model->save()){
            return ['success' => true, 'message' => 'Рецепт добавлен в коллекцию'];
        }

        throw new UnprocessableEntityHttpException(json_encode($model->errors, JSON_UNESCAPED_UNICODE));
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        if (!$model) {
            throw new NotFoundHttpException('Рецепт не найден в коллекции.');
        }

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Рецепт успешно удален из коллекции.',
            ];
        }
    
        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении рецепта из коллекции'); 
    }

}