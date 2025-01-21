<?php
namespace app\controllers;

use app\models\Recipe;
use app\models\User;
use Yii;
use yii\rest\ActiveController;
use yii\web\Response;

class RecipeController extends ActiveController
{
    public $modelClass = "app\models\Recipe";

    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            // 'auth' => function($username, $password){
            //     $user = User::find()->where(['login' => $username])->one();
            //     if($user && $user->validatePassword($password)) {
            //         return $user;
            //     }
            //     return null;
            // }
        ];
        return $behaviors;
    }

    // public function behaviors()
    // {
    //     $behaviors = parent::behaviors();
    //     $behaviors['contentNegotiator'] = [
    //         'class' => \yii\filters\ContentNegotiator::class,
    //         'formats' => [
    //             'application/json' => Response::FORMAT_JSON,
    //         ],
    //     ];
    //     return $behaviors;
    // }

    // protected function successResponse($data, $message = 'OK')
    // {
    //     return [
    //         'status' => 'success',
    //         'message' => $message,
    //         'data' => $data,
    //     ];
    // }

    // protected function errorResponse($message = 'Error', $code = 400)
    // {
    //     Yii::$app->response->statusCode = $code;
    //     return [
    //         'status' => 'error',
    //         'message' => $message,
    //     ];
    // }

    // public function __construct($modelClass = "app\models\Recipe")
    // {
    //     parent::__construct($modelClass);
    // }
    // public function actionIndex()
    // {
    //     $recipes = Recipe::find()->all();
    //     return $this->successResponse($recipes);
    // }

    // public function actionView($id)
    // {
    //     $recipe = Recipe::findOne($id);
    //     if (!$recipe) {
    //         return $this->errorResponse('Recipe not found', 404);
    //     }
    //     return $this->successResponse($recipe);
    // }

    // public function actionCreate()
    // {
    //     $model = new Recipe();
    //     $model->load(Yii::$app->request->post(), '');
    //     if ($model->save()) {
    //         return $this->successResponse($model, 'Recipe created successfully');
    //     }
    //     return $this->errorResponse('Failed to create recipe', 422);
    // }

    // public function actionUpdate($id)
    // {
    //     $model = Recipe::findOne($id);
    //     if (!$model) {
    //         return $this->errorResponse('Recipe not found', 404);
    //     }

    //     $model->load(Yii::$app->request->post(), '');
    //     if ($model->save()) {
    //         return $this->successResponse($model, 'Recipe updated successfully');
    //     }
    //     return $this->errorResponse('Failed to update recipe', 422);
    // }

    // public function actionDelete($id)
    // {
    //     $model = Recipe::findOne($id);
    //     if (!$model) {
    //         return $this->errorResponse('Recipe not found', 404);
    //     }

    //     if ($model->delete()) {
    //         return $this->successResponse([], 'Recipe deleted successfully');
    //     }
    //     return $this->errorResponse('Failed to delete recipe', 422);
    // }
}
