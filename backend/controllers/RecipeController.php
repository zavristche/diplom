<?php
namespace app\controllers;

use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use app\models\recipe\RecipeMark;
use app\models\recipe\RecipeProduct;
use app\models\recipe\RecipeSearch;
use app\models\StatusContent;
use app\models\Step;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;
use app\controllers\BaseApiController;
use app\models\Comment;
use app\models\Complexity;
use app\models\mark\Mark;
use app\models\mark\MarkType;
use app\models\Measure;
use app\models\PrivateType;
use app\models\product\Product;
use app\models\product\ProductType;

class RecipeController extends BaseApiController
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
            'except' => ['index', 'view', 'search', 'create-data', 'random'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'search', 'create-data', 'random'],
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

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionSearch()
    {
        $request = Yii::$app->request;
        $params = $request->getQueryParams();
    
        $searchModel = new RecipeSearch();
        $dataProvider = $searchModel->search($params);
    
        $models = $dataProvider->getModels();
    
        $result = array_map(function ($model) {
            $data = $model->toArray([], ['user', 'status', 'complexity', 'private', 'comments', 'marks', 'products', 'collections', 'calendar_recipe']);
    
            if (isset($data['user'])) {
                unset($data['user']['auth_key'], $data['user']['password']);
            }
    
            return $data;
        }, $models);
    
        return [
            'success' => true,
            'recipes' => $result,
        ];
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

    public function actionRandom()
    {
        // Получаем случайный рецепт
        $recipe = Recipe::find()
            ->with([
                'user',
                'marks',
                'products',
                'steps',
                'status',
                'private',
                'recipeReactions',
                'collectionRecipes'
            ])
            ->orderBy('RAND()') // Для MySQL/MariaDB
            ->one();

        if ($recipe === null) {
            throw new NotFoundHttpException('Случайный рецепт не найден.');
        }

        // Преобразуем данные в массив, убирая чувствительные поля
        $data = $recipe->toArray([], ['user', 'status', 'complexity', 'private', 'comments', 'marks', 'products', 'collections', 'calendar_recipe']);
        if (isset($data['user'])) {
            unset($data['user']['auth_key'], $data['user']['password']);
        }

        return [
            'success' => true,
            'recipe' => $data,
        ];
    }

    private function uploadImage($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Ошибка при загрузке файла.', 'e' => $file['error']];
        }

        $uploadDir = Yii::getAlias('@webroot/uploads/');
        !is_dir($uploadDir) && mkdir($uploadDir, 0777, true);

        $fileName = Yii::$app->user->id . '_' . time() . '_' . Yii::$app->security->generateRandomString() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = $uploadDir . $fileName;
        $fileUrl = Yii::$app->request->hostInfo . '/uploads/' . $fileName;

        return (move_uploaded_file($file['tmp_name'], $filePath) || copy($file['tmp_name'], $filePath))
            ? ['success' => true, 'url' => $fileUrl, 'filePath' => $filePath]
            : ['success' => false, 'message' => 'Ошибка при сохранении файла.'];
    }

    public function actionCreateData()
    {
        try {
            $products = Product::getAll();
            $product_types = ProductType::getAll();
            $marks = Mark::getAll();
            $privates = PrivateType::getAll();
            $measures = Measure::getAll();
            $complexities = Complexity::getAll();
            $mark_types = MarkType::getAll();

            return [
                'success' => true,
                'data' => [
                    'products' => $products,
                    'product_types' => $product_types,
                    'marks' => $marks,
                    'measures' => $measures,
                    'privates' => $privates,
                    'complexities' => $complexities,
                    'mark_types' => $mark_types,
                ],
                'message' => 'Данные для создания рецепта успешно получены',
            ];
        } catch (\Exception $e) {
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Ошибка при получении данных',
                'error' => $e->getMessage(),
            ];
        }   
    }

    public function actionCreate()
    {
        $data = Yii::$app->request->getBodyParams();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
        $steps = [];
        $products = [];
        $marks = [];
        $recipePhotoPath = null;
        $stepPhotoPaths = [];

        try {
            // Инициализация рецепта
            $recipe = new Recipe([
                'user_id' => Yii::$app->user->id,
                'status_id' => StatusContent::getOne('Новое'),
                'scenario' => Recipe::SCENARIO_CREATE,
            ]);

            // Установка атрибутов рецепта
            foreach ($data as $attribute => $value) {
                if ($recipe->hasAttribute($attribute)) {
                    $recipe->$attribute = $value;
                }
            }

            // Валидация рецепта
            if (!$recipe->validate()) {
                foreach ($recipe->errors as $attribute => $messages) {
                    $errors[$attribute] = $messages;
                }
                Yii::error("Recipe validation errors: " . json_encode($recipe->errors));
            }

            // Проверка наличия связанных данных
            if (!isset($data['steps']) || empty($data['steps'])) {
                $errors['steps'] = ['Рецепт должен содержать хотя бы один шаг.'];
            }

            if (!isset($data['products']) || empty($data['products'])) {
                $errors['products'] = ['Рецепт должен содержать хотя бы один продукт.'];
            }

            if (!isset($data['marks']) || empty($data['marks'])) {
                $errors['marks'] = ['Рецепт должен содержать хотя бы одну метку.'];
            }

            // Валидация продуктов
            foreach ($data['products'] ?? [] as $i => $productData) {
                $recipeProduct = new RecipeProduct([
                    'product_id' => $productData['product_id'] ?? null,
                    'measure_id' => $productData['measure_id'] ?? null,
                    'count' => $productData['count'] ?? null,
                ]);

                if ($recipeProduct->getIsToTaste()) {
                    $recipeProduct->count = null;
                } else {
                    $recipeProduct->scenario = RecipeProduct::SCENARIO_NO_TASTE;
                }

                if (!$recipeProduct->validate()) {
                    $errors["product_{$i}"] = $recipeProduct->errors;
                    Yii::error("Product {$i} validation errors: " . json_encode($recipeProduct->errors));
                }
                $products[] = $recipeProduct;
            }

            // Валидация шагов
            foreach ($data['steps'] ?? [] as $i => $stepData) {
                $step = new Step([
                    'number' => $i + 1,
                    'title' => $stepData['title'] ?? "Шаг " . ($i + 1),
                    'description' => $stepData['description'] ?? null,
                    'scenario' => Step::SCENARIO_CREATE,
                ]);

                if (!$step->validate()) {
                    $errors["step_{$i}"] = $step->errors;
                    Yii::error("Step {$i} validation errors: " . json_encode($step->errors));
                }
                $steps[] = $step;
            }

            // Валидация меток
            foreach ($data['marks'] ?? [] as $i => $id) {
                $recipeMark = new RecipeMark([
                    'mark_id' => $id,
                    'scenario' => RecipeMark::SCENARIO_CREATE,
                ]);
                if (!$recipeMark->validate()) {
                    $errors["mark_{$i}"] = $recipeMark->errors;
                    Yii::error("Mark {$i} validation errors: " . json_encode($recipeMark->errors));
                }
                $marks[] = $recipeMark;
            }

            // Проверка фото рецепта
            if (empty($_FILES["recipe_photo"]) || empty($_FILES["recipe_photo"]["name"])) {
                $errors['imageFile'] = ['Фото рецепта обязательно'];
            } else {
                $upload = $this->uploadImage($_FILES["recipe_photo"]);
                if (!$upload['success']) {
                    $errors['imageFile'] = [$upload['message']];
                } else {
                    $recipe->photo = $upload['url'];
                    $recipePhotoPath = $upload['filePath'];
                }
            }

            // Проверка фото шагов
            foreach ($steps as $i => $step) {
                if (empty($_FILES["step_photos"]["name"][$i])) {
                    $errors["step_{$i}"]['imageFile'] = ['Фото шага обязательно'];
                } else {
                    $file = [
                        'name' => $_FILES["step_photos"]["name"][$i],
                        'tmp_name' => $_FILES["step_photos"]["tmp_name"][$i],
                        'type' => $_FILES["step_photos"]["type"][$i],
                        'error' => $_FILES["step_photos"]["error"][$i],
                        'size' => $_FILES["step_photos"]["size"][$i],
                    ];

                    $upload = $this->uploadImage($file);
                    if ($upload['success']) {
                        $step->photo = $upload['url'];
                        $stepPhotoPaths[$i] = $upload['filePath'];
                    } else {
                        $errors["step_{$i}"]['imageFile'] = [$upload['message']];
                    }
                }
            }

            // Возврат ошибок, если они есть
            if (!empty($errors)) {
                if ($recipePhotoPath && file_exists($recipePhotoPath)) {
                    unlink($recipePhotoPath);
                }
                foreach ($stepPhotoPaths as $path) {
                    if ($path && file_exists($path)) {
                        unlink($path);
                    }
                }

                Yii::error("All validation errors: " . json_encode($errors));
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $errors,
                    'recipe' => $recipe->attributes,
                    'products' => array_map(fn($product) => $product->attributes, $products),
                    'steps' => array_map(fn($step) => $step->attributes, $steps),
                    'marks' => array_map(fn($mark) => $mark->attributes, $marks),
                ];
            }

            // Сохранение данных
            $recipe->save();

            foreach ($steps as $step) {
                $step->recipe_id = $recipe->id;
                $step->save();
            }

            foreach ($products as $product) {
                $product->recipe_id = $recipe->id;
                $product->save();
            }

            foreach ($marks as $mark) {
                $mark->recipe_id = $recipe->id;
                $mark->save();
            }

            $transaction->commit();
            return [
                'success' => true,
                'message' => 'Рецепт успешно создан',
                'id' => $recipe->id,
                'recipe' => $recipe->toArray(),
            ];
        } catch (\Exception $e) {
            if ($recipePhotoPath && file_exists($recipePhotoPath)) {
                unlink($recipePhotoPath);
            }
            foreach ($stepPhotoPaths as $path) {
                if ($path && file_exists($path)) {
                    unlink($path);
                }
            }

            $transaction->rollBack();
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Ошибка при создании рецепта',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function actionUpdate($id)
    {
        $recipe = $this->findModel($id);
        $recipe->scenario = Recipe::SCENARIO_UPDATE;
        $recipe->status_id = StatusContent::getOne('Новое');
        $products = [];
        $marks = [];
        $steps = [];
        $recipePhotoPath = null;
        $stepPhotoPaths = [];

        $data = Yii::$app->request->getBodyParams();

        if (empty($data)) {
            return ['success' => false, 'message' => 'Ошибка загрузки данных рецепта'];
        }

        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];

        try {
            // Проверка наличия связанных данных
            if (!isset($data['steps']) || empty($data['steps'])) {
                $errors['steps'] = ['Рецепт должен содержать хотя бы один шаг.'];
            }

            if (!isset($data['products']) || empty($data['products'])) {
                $errors['products'] = ['Рецепт должен содержать хотя бы один продукт.'];
            }

            if (!isset($data['marks']) || empty($data['marks'])) {
                $errors['marks'] = ['Рецепт должен содержать хотя бы одну метку.'];
            }

            // Установка атрибутов рецепта
            foreach ($data as $attribute => $value) {
                if ($recipe->hasAttribute($attribute)) {
                    $recipe->$attribute = $value;
                }
            }

            // Валидация рецепта
            if (!$recipe->validate()) {
                foreach ($recipe->errors as $attribute => $messages) {
                    $errors[$attribute] = $messages;
                }
                Yii::error("Recipe validation errors: " . json_encode($recipe->errors));
            }

            // Проверка фото рецепта
            if (empty($recipe->photo) && (empty($_FILES["recipe_photo"]) || empty($_FILES["recipe_photo"]["name"]))) {
                $errors['imageFile'] = ['Фото рецепта обязательно'];
            } elseif (!empty($_FILES["recipe_photo"]["name"])) {
                $upload = $this->uploadImage($_FILES["recipe_photo"]);
                if (!$upload['success']) {
                    $errors['imageFile'] = [$upload['message']];
                } else {
                    $recipe->photo = $upload['url'];
                    $recipePhotoPath = $upload['filePath'];
                }
            }

            // Валидация продуктов
            $existingProducts = $recipe->getProducts()->indexBy('id')->all();
            $newProductIds = [];

            foreach ($data['products'] as $i => $productData) {
                $product = isset($productData['id']) ? RecipeProduct::findOne($productData['id']) : new RecipeProduct(['recipe_id' => $recipe->id]);
                if (!$product) {
                    $errors["product_{$i}"] = ['Продукт с указанным ID не найден.'];
                    continue;
                }

                $product->setAttributes($productData);

                if ($product->getIsToTaste()) {
                    $product->count = null;
                } else {
                    $product->scenario = RecipeProduct::SCENARIO_NO_TASTE;
                }

                if (!$product->validate()) {
                    $errors["product_{$i}"] = $product->errors;
                    Yii::error("Product {$i} validation errors: " . json_encode($product->errors));
                }

                $products[] = $product;
                if (!$product->isNewRecord) {
                    $newProductIds[] = $product->id;
                }
            }

            // Валидация шагов
            $existingSteps = $recipe->getSteps()->indexBy('id')->all();
            $newStepIds = [];

            foreach ($data['steps'] as $i => $stepData) {
                $step = isset($stepData['id']) ? Step::findOne($stepData['id']) : new Step();
                if (!$step) {
                    $errors["step_{$i}"] = ['Шаг с указанным ID не найден.'];
                    continue;
                }

                $step->scenario = $step->isNewRecord ? Step::SCENARIO_CREATE : Step::SCENARIO_UPDATE;
                $step->recipe_id = $recipe->id;
                $step->number = $i + 1;

                foreach ($stepData as $attribute => $value) {
                    if ($step->hasAttribute($attribute)) {
                        $step->$attribute = $value;
                    }
                }

                if (empty($step->photo) && (empty($_FILES["step_photos"]["name"][$i]) || !isset($_FILES["step_photos"]["name"][$i]))) {
                    $errors["step_{$i}"]['imageFile'] = ['Фото шага обязательно'];
                } elseif (!empty($_FILES["step_photos"]["name"][$i])) {
                    if (!empty($step->photo)) {
                        $oldPhotoPath = Yii::getAlias('@webroot') . parse_url($step->photo, PHP_URL_PATH);
                        if (file_exists($oldPhotoPath)) {
                            unlink($oldPhotoPath);
                        }
                    }

                    $file = [
                        'name' => $_FILES["step_photos"]["name"][$i],
                        'tmp_name' => $_FILES["step_photos"]["tmp_name"][$i],
                        'type' => $_FILES["step_photos"]["type"][$i],
                        'error' => $_FILES["step_photos"]["error"][$i],
                        'size' => $_FILES["step_photos"]["size"][$i],
                    ];

                    $upload = $this->uploadImage($file);
                    if ($upload['success']) {
                        $step->photo = $upload['url'];
                        $stepPhotoPaths[$i] = $upload['filePath'];
                    } else {
                        $errors["step_{$i}"]['imageFile'] = [$upload['message']];
                    }
                }

                if (!$step->validate()) {
                    $errors["step_{$i}"] = array_merge($errors["step_{$i}"] ?? [], $step->errors);
                    Yii::error("Step {$i} validation errors: " . json_encode($step->errors));
                }

                $steps[] = $step;
                if (!$step->isNewRecord) {
                    $newStepIds[] = $step->id;
                }
            }

            // Валидация меток
            if (isset($data['marks'])) {
                RecipeMark::deleteAll(['recipe_id' => $recipe->id]);
                foreach ($data['marks'] as $i => $id) {
                    $recipeMark = new RecipeMark([
                        'recipe_id' => $recipe->id,
                        'mark_id' => $id,
                    ]);
                    if (!$recipeMark->validate()) {
                        $errors["mark_{$i}"] = $recipeMark->errors;
                        Yii::error("Mark {$i} validation errors: " . json_encode($recipeMark->errors));
                    }
                    $marks[] = $recipeMark;
                }
            }

            // Возврат ошибок, если они есть
            if (!empty($errors)) {
                if ($recipePhotoPath && file_exists($recipePhotoPath)) {
                    unlink($recipePhotoPath);
                }
                foreach ($stepPhotoPaths as $path) {
                    if ($path && file_exists($path)) {
                        unlink($path);
                    }
                }

                Yii::error("All validation errors: " . json_encode($errors));
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $errors,
                    'recipe' => $recipe->attributes,
                    'steps' => array_map(fn($step) => $step->attributes, $steps),
                    'products' => array_map(fn($product) => $product->attributes, $products),
                    'marks' => array_map(fn($mark) => $mark->attributes, $marks),
                ];
            }

            // Сохранение данных
            $recipe->save();

            // Удаление старых шагов
            $stepsToDelete = array_diff(array_keys($existingSteps), $newStepIds);
            if (!empty($stepsToDelete)) {
                Step::deleteAll(['id' => $stepsToDelete]);
            }

            // Удаление старых продуктов
            $productsToDelete = array_diff(array_keys($existingProducts), $newProductIds);
            if (!empty($productsToDelete)) {
                RecipeProduct::deleteAll(['id' => $productsToDelete]);
            }

            foreach ($steps as $step) {
                $step->save();
            }

            foreach ($products as $product) {
                $product->save();
            }

            foreach ($marks as $mark) {
                $mark->save();
            }

            $transaction->commit();
            return ['success' => true, 'message' => 'Рецепт успешно обновлен', 'recipe' => $recipe->toArray()];
        } catch (\Exception $e) {
            if ($recipePhotoPath && file_exists($recipePhotoPath)) {
                unlink($recipePhotoPath);
            }
            foreach ($stepPhotoPaths as $path) {
                if ($path && file_exists($path)) {
                    unlink($path);
                }
            }

            $transaction->rollBack();
            return ['success' => false, 'message' => 'Ошибка обновления', 'error' => $e->getMessage()];
        }
    }
    
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->deleteRelatedData($model);

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Рецепт и все связанные данные успешно удалены.',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении рецепта.'); 
    }

    private function deleteRelatedData($model)
    {
        // фото рецепта
        $this->deleteImage($model->photo);

        // шаги
        $steps = Step::findAll(['recipe_id' => $model->id]);
        foreach ($steps as $step) {
            $this->deleteImage($step->photo);
            $step->delete();
        }

        // комментарии
        $comments = Comment::findAll(['recipe_id' => $model->id]);
        foreach ($comments as $comment) {
            $this->deleteImage($comment->photo);
            $comment->delete();
        }
    }

    private function deleteImage($fileUrl)
    {
        if ($fileUrl) {
            $filePath = Yii::getAlias('@webroot') . parse_url($fileUrl, PHP_URL_PATH);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}