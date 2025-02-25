<?php
namespace app\controllers;

use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use app\models\recipe\RecipeSearch;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class RecipeController extends ActiveController
{
    public $modelClass = 'app\models\recipe\Recipe';

    public $enableCsrfValidation = false;

    protected function findModel($id)
    {
        $model = Recipe::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Рецепт не найден.');
        }
        return $model;
    }
     
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['index', 'view', 'search'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'search'],
                ],
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'delete'],
                    'roles' => ['@'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

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

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'update' || $action === 'delete') {
            if ($model->user_id !== \Yii::$app->user->id)
                throw new \yii\web\ForbiddenHttpException(sprintf('Вы можете выполнять это действие только с рецептами, которые вы создали.', $action));
        }
    }

    // public function actionCreate()
    // {
    //     $model = new Recipe();

    //     if(Yii::$app->request->isPost){
    //         if ($model->load(Yii::$app->request->post())){

    //             if ($model->validate()) {
    //                 $model->save();
    //             }        
    //         }
    //     }
    //     return [
    //         'attributes' => $model->attributes,
    //         'errors' => $model->errors,
    //     ];
    // }

    // public function actionCreate()
    // {
    //     $model = new Recipe();

    //     if (Yii::$app->request->isPost) {
    //         $data = Yii::$app->request->post();

    //         if ($model->load($data, '') && $model->validate()) {
    //             if (!$model->save()) {
    //                 throw new UnprocessableEntityHttpException(json_encode($model->errors, JSON_UNESCAPED_UNICODE));
    //             }

    //             // Обрабатываем теги (если переданы)
    //             if (!empty($data['tags'])) {
    //                 foreach ($data['tags'] as $tag_id) {
    //                     $recipeTag = new RecipeTag();
    //                     $recipeTag->recipe_id = $model->id;
    //                     $recipeTag->tag_id = $tag_id;
    //                     if (!$recipeTag->save()) {
    //                         throw new UnprocessableEntityHttpException(json_encode($recipeTag->errors, JSON_UNESCAPED_UNICODE));
    //                     }
    //                 }
    //             }

    //             // Обрабатываем ингредиенты (если переданы)
    //             if (!empty($data['ingredients'])) {
    //                 foreach ($data['ingredients'] as $ingredientData) {
    //                     $ingredient = new Ingredient();
    //                     $ingredient->recipe_id = $model->id;
    //                     $ingredient->name = $ingredientData['name'];
    //                     $ingredient->amount = $ingredientData['amount'];
    //                     if (!$ingredient->save()) {
    //                         throw new UnprocessableEntityHttpException(json_encode($ingredient->errors, JSON_UNESCAPED_UNICODE));
    //                     }
    //                 }
    //             }

    //             // Обрабатываем шаги (если переданы)
    //             if (!empty($data['steps'])) {
    //                 foreach ($data['steps'] as $stepData) {
    //                     $step = new RecipeStep();
    //                     $step->recipe_id = $model->id;
    //                     $step->order = $stepData['order'];
    //                     $step->description = $stepData['description'];
    //                     if (!$step->save()) {
    //                         throw new UnprocessableEntityHttpException(json_encode($step->errors, JSON_UNESCAPED_UNICODE));
    //                     }
    //                 }
    //             }

    //             return [
    //                 'success' => true,
    //                 'message' => 'Рецепт успешно создан',
    //                 'recipe' => $model->attributes,
    //             ];
    //         }
    //     }

    //     throw new BadRequestHttpException('Некорректные данные запроса.');
    // }

    public function actionSearch()
    {
        $request = Yii::$app->request;
        $params = $request->post();
    
        $searchModel = new RecipeSearch();
        $dataProvider = $searchModel->search($params);

        $models = $dataProvider->getModels();

        $result = array_map(function ($model) {

            $data = $model->toArray([], ['user', 'status', 'complexity', 'private', 'comments', 'likes', 'saved', 'marks', 'products', 'steps', 'collections', 'calendar_recipe']);
    
            if (isset($data['user'])) {
                unset($data['user']['auth_key'], $data['user']['password']);
            }
    
            return $data;
        }, $models);
    
        return $result;
    }

    
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Recipe::find()->with([
                'user',
                'marks',
                'products',
                'steps',
                'status', 
                'private', 
                'recipeReactions', 
                'collectionRecipes'
            ]),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'created_at' => SORT_DESC,
                ],
            ],
        ]);
    
        return $dataProvider; 
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
    
        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Рецепт и все связанные данные успешно удалены.',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении рецепта.'); 
    } 
}