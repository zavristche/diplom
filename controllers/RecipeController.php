<?php
namespace app\controllers;

use app\models\Recipe;
use Yii;

class RecipeController extends ApiController
{
    public function actionIndex()
    {
        $recipes = Recipe::find()->all();
        return $this->successResponse($recipes);
    }

    public function actionView($id)
    {
        $recipe = Recipe::findOne($id);
        if (!$recipe) {
            return $this->errorResponse('Recipe not found', 404);
        }
        return $this->successResponse($recipe);
    }

    public function actionCreate()
    {
        $model = new Recipe();
        $model->load(Yii::$app->request->post(), '');
        if ($model->save()) {
            return $this->successResponse($model, 'Recipe created successfully');
        }
        return $this->errorResponse('Failed to create recipe', 422);
    }

    public function actionUpdate($id)
    {
        $model = Recipe::findOne($id);
        if (!$model) {
            return $this->errorResponse('Recipe not found', 404);
        }

        $model->load(Yii::$app->request->post(), '');
        if ($model->save()) {
            return $this->successResponse($model, 'Recipe updated successfully');
        }
        return $this->errorResponse('Failed to update recipe', 422);
    }

    public function actionDelete($id)
    {
        $model = Recipe::findOne($id);
        if (!$model) {
            return $this->errorResponse('Recipe not found', 404);
        }

        if ($model->delete()) {
            return $this->successResponse([], 'Recipe deleted successfully');
        }
        return $this->errorResponse('Failed to delete recipe', 422);
    }
}
