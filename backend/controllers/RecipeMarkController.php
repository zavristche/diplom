<?php
namespace app\controllers;
use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use app\models\recipe\RecipeMark;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use app\controllers\BaseApiController;

class RecipeMarkController extends BaseApiController
{
    public $modelClass = 'app\models\recipe\RecipeMark';

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
    
    public function actionAdd()
    {
        $mark_id = Yii::$app->request->post('mark_id');
        $recipe_id = Yii::$app->request->post('recipe_id');

        if (!$mark_id || !$recipe_id) {
            throw new BadRequestHttpException("Необходимо передать collection_id и recipe_id");
        }

        $recipe = Recipe::findOne(['id' => $recipe_id]);
            
        if($recipe->user_id !== Yii::$app->user->id){
            throw new \yii\web\UnauthorizedHttpException('Вы можете добавлять метки только в свои рецепты');
        }

        if(RecipeMark::findOne(['recipe_id' => $recipe_id])){
            throw new \yii\web\ConflictHttpException('Метка уже добавлена');
        }

        $model = new RecipeMark();
        $model->mark_id = $mark_id;
        $model->recipe_id = $recipe_id;

        if($model->save()){
            return ['success' => true, 'model' => $model,];
        }

        return ['success' => false, 'errors' => $model->errors];
    }
}