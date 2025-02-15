<?php
namespace app\controllers;
use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use Yii;
use yii\rest\ActiveController;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;

class RecipeController extends ActiveController
{
    public $modelClass = 'app\models\recipe\Recipe';

    public $enableCsrfValidation = false;
     
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

    public function actionSearch()
    {
        $request = Yii::$app->request;
        $params = $request->post();

        $query = Recipe::find()->with([
            'user', 'recipeMarks', 'recipeProducts', 'steps', 'status', 'private', 'recipeReactions', 'collectionRecipes'
        ]);

        if (!empty($params['title'])) {
            $query->andWhere(['like', 'title', $params['title']]);
        }
        if (!empty($params['category'])) {
            $query->andWhere(['category_id' => $params['category']]);
        }
        if (!empty($params['difficulty'])) {
            $query->andWhere(['difficulty' => $params['difficulty']]);
        }
        if (!empty($params['user_id'])) {
            $query->andWhere(['user_id' => $params['user_id']]);
        }
        if (!empty($params['products'])) {
            $productIds = explode(',', $params['products']);
            $query->joinWith('products')->andWhere(['product.id' => $productIds]);
        }

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);
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

    // public function actionDelete(
    //     $model-
    //     // $this->findModel($id)->delete();

    //     return $this->redirect(['index']);
    // )

    // public function actionCreate()
    // {
    //     $request = Yii::$app->request;
    //     $transaction = Yii::$app->db->beginTransaction();

    //     try {
    //         // Создаем рецепт
    //         $recipe = new Recipe();
    //         $recipe->load($request->post(), '');
    //         if (!$recipe->save()) {
    //             throw new BadRequestHttpException('Ошибка при сохранении рецепта');
    //             return [
    //                 'attributes' => $model->attributes,
    //                 'errors' => $model->errors,
    //             ];
    //         }

    //         // Сохраняем шаги
    //         $steps = $request->post('steps', []);
    //         foreach ($steps as $stepData) {
    //             $step = new Step();
    //             $step->recipe_id = $recipe->id;
    //             $step->description = $stepData['description'];
    //             if (!$step->save()) {
    //                 throw new BadRequestHttpException('Ошибка при сохранении шага');
    //             }
    //         }

    //         // Привязываем метки
    //         $tags = $request->post('tags', []);
    //         $recipe->link('tags', $tags);

    //         // Привязываем продукты
    //         $products = $request->post('products', []);
    //         $recipe->link('products', $products);

    //         $transaction->commit();
    //         return $recipe;
    //     } catch (\Exception $e) {
    //         $transaction->rollBack();
    //         throw new BadRequestHttpException($e->getMessage());
    //     }
    // }
}