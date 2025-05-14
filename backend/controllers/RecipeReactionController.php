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
                    'roles' => ['@'], // Только авторизованные пользователи
                    'actions' => ['create', 'delete', 'check'], // Разрешённые действия
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
        
        // Проверяем, существует ли рецепт
        $recipe = Recipe::findOne($data['recipe_id']);
        if (!$recipe) {
            throw new NotFoundHttpException('Рецепт не найден.');
        }

        // Проверяем, не лайкает ли пользователь свой собственный рецепт
        if ($recipe->user_id == Yii::$app->user->id) {
            return [
                'success' => false,
                'message' => 'Вы не можете лайкнуть свой собственный рецепт'
            ];
        }

        if (Yii::$app->user->identity->isAdmin) {
            return [
                'success' => false,
                'message' => 'Администратор не может ставить реакцию',
            ];
        }

        // Проверяем, не существует ли уже реакция
        $existingReaction = RecipeReaction::findOne(['recipe_id' => $data['recipe_id'], 'user_id' => Yii::$app->user->id]);
        if ($existingReaction) {
            return [
                'success' => false,
                'message' => 'Вы уже лайкнули этот рецепт'
            ];
        }

        // Начинаем транзакцию
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new RecipeReaction([
                'recipe_id' => $data['recipe_id'],
                'user_id' => Yii::$app->user->id,
            ]);

            if ($model->save()) {
                // Увеличиваем likes в модели Recipe
                $recipe->likes = $recipe->likes + 1;
                if (!$recipe->save(false)) { // Сохраняем без валидации
                    throw new \yii\web\ServerErrorHttpException('Не удалось обновить количество лайков.');
                }

                $transaction->commit();
                return ['success' => true, 'model' => $model->attributes];
            }

            $transaction->rollBack();
            return ['success' => false, 'errors' => $model->errors, 'attributes' => $model->attributes];
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new \yii\web\ServerErrorHttpException('Ошибка при добавлении реакции: ' . $e->getMessage());
        }
    }

    public function actionDelete()
    {
        $data = Yii::$app->request->getBodyParams();
        
        // Проверяем, существует ли рецепт
        $recipe = Recipe::findOne($data['recipe_id']);
        if (!$recipe) {
            throw new NotFoundHttpException('Рецепт не найден.');
        }

        // Находим реакцию
        $model = RecipeReaction::findOne(['recipe_id' => $data['recipe_id'], 'user_id' => Yii::$app->user->id]);
        if (!$model) {
            throw new NotFoundHttpException('Реакция не найдена.');
        }

        // Начинаем транзакцию
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->delete()) {
                // Уменьшаем likes в модели Recipe
                $recipe->likes = $recipe->likes > 0 ? $recipe->likes - 1 : 0; // Не допускаем отрицательное значение
                if (!$recipe->save(false)) { // Сохраняем без валидации
                    throw new \yii\web\ServerErrorHttpException('Не удалось обновить количество лайков.');
                }

                $transaction->commit();
                return [
                    'success' => true,
                    'message' => 'Лайк убран',
                ];
            }

            $transaction->rollBack();
            throw new \yii\web\ServerErrorHttpException('Ошибка при удалении реакции.');
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw new \yii\web\ServerErrorHttpException('Ошибка при удалении реакции: ' . $e->getMessage());
        }
    }

    public function actionCheck()
    {
        $data = Yii::$app->request->getBodyParams();
        $model = RecipeReaction::findOne(['recipe_id' => $data['recipe_id'], 'user_id' => $data['user_id']]);

        return [
            'success' => true,
            'isLiked' => !is_null($model), // true, если лайк есть, false, если нет
        ];
    }
}