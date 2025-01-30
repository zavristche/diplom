<?php
namespace app\controllers;

use app\models\collection\Collection;
use app\models\collection\CollectionRecipe;
use yii\filters\AccessControl;
use Yii;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

class CollectionRecipeController extends ActiveController
{
    public $modelClass = 'app\models\collection\CollectionRecipe';

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
        unset($actions['create']); // Отключаем стандартный create
        return $actions;
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
            return ['success' => true, 'model' => $model, 'message' => 'Рецепт добавлен в коллекцию'];
        }

        return ['success' => false, 'errors' => $model->errors];
    }

}