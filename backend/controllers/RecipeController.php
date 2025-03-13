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
            ? ['success' => true, 'url' => $fileUrl] 
            : ['success' => false, 'message' => 'Ошибка при сохранении файла.'];
    }

    public function actionCreate()
    {
        $data = Yii::$app->request->getBodyParams();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
        $steps = [];
        $products = [];
        $marks = [];

        try {

            //Рецепт
            $recipe = new Recipe([
                'user_id' => Yii::$app->user->id,
                'status_id' => StatusContent::getOne('Новое'),
                'imageFile' => $_FILES["recipe_photo"],
                'scenario' => Recipe::SCENARIO_CREATE,
            ]);

            foreach ($data as $attribute => $value) {
                if ($recipe->hasAttribute($attribute)) {
                    $recipe->$attribute = $value;
                }
            }

            if (!$recipe->validate()) {
                $errors = array_merge($errors, $recipe->errors);
            }

            //проверки связанных таблиц
            if (!($data['steps'] ?? [])) {
                $errors['steps'][] = 'Рецепт должен содержать хотя бы один шаг.';
            }

            if (!($data['products'] ?? [])) {
                $errors['products'][] = 'Рецепт должен содержать хотя бы один продукт.';
            }

            //шаги
            foreach ($data['steps'] ?? [] as $i => $stepData) {
                $step = new Step([
                    'number' => $i + 1,
                    'title' => $stepData['title'] ?? "Шаг " . ($i + 1),
                    'photo' => $stepData['photo'] ?? null,
                    'description' => $stepData['description'] ?? null,
                    'imageFile' => $_FILES["step_photos"]["name"][$i],
                    'scenario' => Recipe::SCENARIO_CREATE,
                ]);
                if (!$step->validate()) {
                    $errors["step_{$i}"] = $step->errors;
                }
                $steps[] = $step;
            }

            //продукты
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
                    $errors["products_{$i}"] = $recipeProduct->errors;
                }
                $products[] = $recipeProduct;
            }

            //метки
            foreach ($data['marks'] ?? [] as $i => $id) {
                $recipeMark = new RecipeMark([
                    'mark_id' => $id,
                ]);

                $marks[] = $recipeMark;
            }

            //фото рецепта
            $upload = $this->uploadImage($recipe->imageFile);
            if (!$upload['success']) {
                $errors['recipe_photo'] = $upload['message'];
            } else {
                $recipe->photo = $upload['url'];
            }

            //валидация
            if ($errors) {
                Yii::$app->response->statusCode = 422;
                return ['success' => false, 'message' => 'Ошибка валидации', 'errors' => $errors, 'recipe' => $recipe->attributes, 'products' => $products, 'steps' => $steps, 'marks' => $marks,];
            }

            $recipe->save();

            //фото шагов рецепта
            foreach ($steps as $i => $step) {

                $step->recipe_id = $recipe->id;

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
                }
            
                $step->save();
            }
            

            foreach ($products ?? [] as $product) {
                $product->recipe_id = $recipe->id;
                $product->save();
            }

            foreach ($marks ?? [] as $mark) {
                $mark->recipe_id = $recipe->id;
                $mark->save();
            }

            $transaction->commit();

            return ['success' => true, 'message' => 'Рецепт успешно создан', 'recipe' => $recipe->toArray()];
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = 500;
            return ['success' => false, 'message' => 'Ошибка при создании рецепта', 'error' => $e->getMessage()];
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
    
        $data = Yii::$app->request->getBodyParams();
    
        if (empty($data)) {
            return ['success' => false, 'message' => 'Ошибка загрузки данных рецепта'];
        }
    
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
    
        try {
            // Шаги и продукты
            if (!isset($data['steps']) || empty($data['steps'])) {
                $errors['steps'][] = 'Рецепт должен содержать хотя бы один шаг.';
            }
    
            if (!isset($data['products']) || empty($data['products'])) {
                $errors['products'][] = 'Рецепт должен содержать хотя бы один продукт.';
            }
    
            // Рецепт
            foreach ($data as $attribute => $value) {
                if ($recipe->hasAttribute($attribute)) {
                    $recipe->$attribute = $value;
                }
            }
    
            // Фото рецепта
            if (!empty($_FILES["recipe_photo"]["name"])) {
                
                if (!empty($recipe->imageFile)) {
                    $photoPath = Yii::getAlias('@webroot') . parse_url($recipe->imageFile, PHP_URL_PATH);

                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }

                $upload = $this->uploadImage($_FILES["recipe_photo"]);
                if (!$upload['success']) {
                    $errors['imageFile'] = $upload['message'];
                } else {
                    $recipe->imageFile = $upload['url'];
                }
            }
    
            if (!$recipe->validate()) {
                $errors = array_merge($errors, $recipe->errors);
            }
    
            // Шаги
            $existingSteps = $recipe->getSteps()->indexBy('id')->all();
            $newSteps = [];
    
            foreach ($data['steps'] as $i => $stepData) {
                if (isset($stepData['id'])) {

                    $step = Step::findOne($stepData['id']);
                } else {
                    $step = new Step();
                }
    
                $step->scenario = $step->isNewRecord ? Step::SCENARIO_CREATE : Step::SCENARIO_UPDATE;
                $step->recipe_id = $recipe->id;
                $step->number = $i + 1;
                
                foreach ($stepData as $attribute => $value) {
                    if ($step->hasAttribute($attribute)) {
                        $step->$attribute = $value;
                    }
                }
    
                // Фото шага
                if (!empty($_FILES["step_photos"]["name"][$i])) {

                    $file = [
                        'name' => $_FILES["step_photos"]["name"][$i],
                        'tmp_name' => $_FILES["step_photos"]["tmp_name"][$i],
                        'type' => $_FILES["step_photos"]["type"][$i],
                        'error' => $_FILES["step_photos"]["error"][$i],
                        'size' => $_FILES["step_photos"]["size"][$i],
                    ];
                    
                    $upload = $this->uploadImage($file);
                    if ($upload['success']) {
                        $step->imageFile = $upload['url'];
                    } else {
                        $errors["step_{$i}"]['imageFile'] = $upload['message'];
                    }
                }
    
                if (!$step->validate()) {
                    $errors["step_{$i}"] = $step->errors;
                }

                $steps[] = $step;
            }

    
            // Продукты
            $existingProducts = $recipe->getProducts()->indexBy('id')->all();
            $newProducts = [];
    
            foreach ($data['products'] as $productData) {
                if (isset($productData['id'])) {
                    $product = RecipeProduct::findOne($productData['id']);
                } else {
                    $product = new RecipeProduct(['recipe_id' => $recipe->id]);
                }
    
                $product->setAttributes($productData);
    
                if (!$product->validate()) {
                    $errors["product_{$product->id}"] = $product->errors;
                }

                $products[] = $product;
            }
    
            // Метки
            if (isset($data['marks'])) {

                RecipeMark::deleteAll(['recipe_id' => $recipe->id]);
    
                foreach ($data['marks'] as $mark) {
                    $recipeMark = new RecipeMark();
                    $recipeMark->recipe_id = $recipe->id;
                    $recipeMark->mark_id = $mark;
                    if (!$recipeMark->validate()) {
                        $errors["marks"][] = $recipeMark->errors;
                    }
                    $marks[] = $recipeMark;
                }
            }

            if (!empty($errors)) {
                Yii::$app->response->statusCode = 422;
                foreach($steps as $step){
                    if (!empty($step->imageFile)) { 
                        $photoPath = Yii::getAlias('@webroot') . parse_url($step->imageFile, PHP_URL_PATH);
    
                        if (file_exists($photoPath)) {
                            unlink($photoPath);
                        }
                    }
                }

                if (!empty($recipe->imageFile)) { 
                    $photoPath = Yii::getAlias('@webroot') . parse_url($recipe->imageFile, PHP_URL_PATH);

                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }

                return ['success' => false, 'message' => 'Ошибка валидации', 'errors' => $errors, 'recipe' => $recipe, 'steps' => $steps, 'products' => $products, 'marks' => $marks];
            }

            if (!empty($recipe->imageFile)) { 
                $photoPath = Yii::getAlias('@webroot') . parse_url($step->photo, PHP_URL_PATH);

                if (file_exists($photoPath)) {
                    unlink($photoPath);
                }
                $recipe->photo = $recipe->imageFile;
            }
            $recipe->save();

            $stepsToDelete = array_diff(array_keys($existingSteps), $newSteps);
            if (!empty($stepsToDelete)) {
                Step::deleteAll(['id' => $stepsToDelete]);
            }

            $productsToDelete = array_diff(array_keys($existingProducts), $newProducts);
            if (!empty($productsToDelete)) {
                RecipeProduct::deleteAll(['id' => $productsToDelete]);
            }

            RecipeMark::deleteAll(['recipe_id' => $recipe->id]);

            foreach ($products as $product) {
                $product->recipe_id = $recipe->id;
                $product->save();
            }

            foreach ($steps as $step) {
                $step->recipe_id = $recipe->id;
                if (!empty($step->imageFile)) { 
                    $photoPath = Yii::getAlias('@webroot') . parse_url($step->photo, PHP_URL_PATH);
    
                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }

                    $step->photo = $step->imageFile;
                }
                $step->save();
            }

            foreach ($marks as $mark) {
                $mark->recipe_id = $recipe->id;
                $mark->save();
            }

            $transaction->commit();
            return ['success' => true, 'message' => 'Рецепт успешно обновлен', 'recipe' => $recipe->toArray()];
        } catch (\Exception $e) {
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