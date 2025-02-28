<?php
namespace app\controllers;

use app\models\Measure;
use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use app\models\recipe\RecipeMark;
use app\models\recipe\RecipeProduct;
use app\models\recipe\RecipeSearch;
use app\models\StatusContent;
use app\models\Step;
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
        $params = $request->post();
    
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

    private function uploadImage($file)
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'Ошибка при загрузке файла.'];
        }

        $fileName = Yii::$app->user->id . '_' . time() . '_' . Yii::$app->security->generateRandomString() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = Yii::getAlias('@webroot/uploads/') . $fileName;

        if (!is_dir(Yii::getAlias('@webroot/uploads/'))) {
            mkdir(Yii::getAlias('@webroot/uploads/'), 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return [
                'success' => true,
                'url' => Yii::$app->request->hostInfo . '/uploads/' . $fileName
            ];
        }

        return ['success' => false, 'message' => 'Ошибка при сохранении файла.'];
    }

    public function actionCreate()
    {
        $data = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];

        try {

            //Рецепт
            $recipe = new Recipe([
                'user_id' => Yii::$app->user->id,
                'status_id' => StatusContent::getOne('Новый'),
            ]);

            if (!$recipe->load($data, '') || !$recipe->validate()) {
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
                ]);
                if (!$step->validate()) {
                    $errors["step_{$i}"] = $step->errors;
                }
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
            }

            if (empty($_FILES['recipe_photo'])) {
                $errors['recipe_photo'][] = 'Фото рецепта обязательно.';
            }

            foreach ($data['steps'] ?? [] as $i => $stepData) {
                if (empty($_FILES["step_photos"]["name"][$i])) {
                    $errors["step_photos_{$i}"][] = 'Фото для шага обязательно.';
                }
            }

            //валидация
            if ($errors) {
                Yii::$app->response->statusCode = 422;
                return ['success' => false, 'message' => 'Ошибка валидации', 'errors' => $errors];
            }

            //фото рецепта
            $upload = $this->uploadImage($_FILES['recipe_photo']);
            if (!$upload['success']) {
                return ['success' => false, 'message' => 'Ошибка при загрузке изображения рецепта'];
            }
            $recipe->photo = $upload['url'];

            $recipe->save();

            //фото шагов рецепта
            foreach ($data['steps'] ?? [] as $i => $stepData) {
                $step = new Step([
                    'recipe_id' => $recipe->id,
                    'number' => $i + 1,
                    'title' => $stepData['title'] ?? "Шаг " . ($i + 1),
                    'description' => $stepData['description'] ?? null,
                ]);

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

            foreach ($data['products'] ?? [] as $productData) {
                (new RecipeProduct([
                    'recipe_id' => $recipe->id,
                    'product_id' => $productData['product_id'],
                    'measure_id' => $productData['measure_id'],
                    'count' => $productData['count'] ?? null,
                ]))->save();
            }

            foreach ($data['marks'] ?? [] as $markId) {
                (new RecipeMark([
                    'recipe_id' => $recipe->id,
                    'mark_id' => $markId,
                ]))->save();
            }

            $transaction->commit();

            return ['success' => true, 'message' => 'Рецепт успешно создан', 'recipe' => $recipe->toArray()];
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = 500;
            return ['success' => false, 'message' => 'Ошибка при создании рецепта', 'error' => $e->getMessage()];
        }
    }



    // public function actionCreate()
    // {
    //     $data = Yii::$app->request->post();
    //     $transaction = Yii::$app->db->beginTransaction();
    //     $errors = [];

    //     try {
    //         $recipe = new Recipe([
    //             'user_id' => Yii::$app->user->id,
    //             'status_id' => StatusContent::getOne('Новый'),
    //         ]);

    //         if (!$recipe->load($data, '') || !$recipe->validate()) {
    //             $errors = array_merge($errors, $recipe->errors);
    //         }

    //         if (!($data['steps'] ?? [])) {
    //             $errors['steps'][] = 'Рецепт должен содержать хотя бы один шаг.';
    //         }

    //         if (!($data['products'] ?? [])) {
    //             $errors['products'][] = 'Рецепт должен содержать хотя бы один продукт.';
    //         }

    //         foreach ($data['steps'] ?? [] as $i => $stepData) {
    //             $step = new Step([
    //                 'number' => $i + 1,
    //                 'title' => $stepData['title'] ?? "Шаг " . ($i + 1),
    //                 'photo' => $stepData['photo'] ?? null,
    //                 'description' => $stepData['description'] ?? null,
    //             ]);
    //             if (!$step->validate()) {
    //                 $errors["steps_{$i}"] = $step->errors;
    //             }
    //         }

    //         foreach ($data['products'] ?? [] as $i => $productData) {
    //             $recipeProduct = new RecipeProduct([
    //                 'product_id' => $productData['product_id'] ?? null,
    //                 'measure_id' => $productData['measure_id'] ?? null,
    //                 'count' => $productData['count'] ?? null,
    //             ]);

    //             if ($recipeProduct->getIsToTaste()) {
    //                 $recipeProduct->count = null;
    //             } else {
    //                 $recipeProduct->scenario = RecipeProduct::SCENARIO_NO_TASTE;
    //             }

    //             if (!$recipeProduct->validate()) {
    //                 $errors["products_{$i}"] = $recipeProduct->errors;
    //             }
    //         }

    //         if ($errors) {
    //             Yii::$app->response->statusCode = 422;
    //             return ['success' => false, 'message' => 'Ошибка валидации', 'errors' => $errors];
    //         }

    //         $recipe->save();

    //         foreach ($data['steps'] ?? [] as $i => $stepData) {
    //             (new Step([
    //                 'recipe_id' => $recipe->id,
    //                 'number' => $i + 1,
    //                 'title' => $stepData['title'] ?? "Шаг " . ($i + 1),
    //                 'photo' => $stepData['photo'] ?? null,
    //                 'description' => $stepData['description'] ?? null,
    //             ]))->save();
    //         }

    //         foreach ($data['products'] ?? [] as $productData) {
    //             (new RecipeProduct([
    //                 'recipe_id' => $recipe->id,
    //                 'product_id' => $productData['product_id'],
    //                 'measure_id' => $productData['measure_id'],
    //                 'count' => $productData['count'] ?? null,
    //             ]))->save();
    //         }

    //         foreach ($data['marks'] ?? [] as $markId) {
    //             (new RecipeMark([
    //                 'recipe_id' => $recipe->id,
    //                 'mark_id' => $markId,
    //             ]))->save();
    //         }

    //         $transaction->commit();
    //         return ['success' => true, 'message' => 'Рецепт успешно создан', 'recipe' => $recipe->toArray()];
    //     } catch (\Exception $e) {
    //         $transaction->rollBack();
    //         Yii::$app->response->statusCode = 500;
    //         return ['success' => false, 'message' => 'Ошибка при создании рецепта', 'error' => $e->getMessage()];
    //     }
    // }

    public function actionUpdate($id)
    {
        $data = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];

        try {
            $recipe = Recipe::findOne($id);
            if (!$recipe) {
                Yii::$app->response->statusCode = 404;
                return ['success' => false, 'message' => 'Рецепт не найден'];
            }

            if ($recipe->user_id !== Yii::$app->user->id) {
                Yii::$app->response->statusCode = 403;
                return ['success' => false, 'message' => 'Доступ запрещен'];
            }

            if (!$recipe->load($data, '') || !$recipe->validate()) {
                $errors = array_merge($errors, $recipe->errors);
            }

            if (empty($data['steps']) || !is_array($data['steps'])) {
                $errors['steps'][] = 'Рецепт должен содержать хотя бы один шаг.';
            }

            if (empty($data['products']) || !is_array($data['products'])) {
                $errors['products'][] = 'Рецепт должен содержать хотя бы один продукт.';
            }

            if (!empty($errors)) {
                Yii::$app->response->statusCode = 422;
                return ['success' => false, 'message' => 'Ошибка валидации', 'errors' => $errors];
            }

            if (!$recipe->save()) {
                throw new \yii\web\ServerErrorHttpException('Не удалось сохранить рецепт.');
            }

            // Удаляем старые шаги, продукты и метки
            Step::deleteAll(['recipe_id' => $recipe->id]);
            RecipeProduct::deleteAll(['recipe_id' => $recipe->id]);
            RecipeMark::deleteAll(['recipe_id' => $recipe->id]);

            foreach ($data['steps'] as $i => $stepData) {
                $step = new Step([
                    'recipe_id' => $recipe->id,
                    'number' => $i + 1,
                    'title' => $stepData['title'] ?? "Шаг " . ($i + 1),
                    'photo' => $stepData['photo'] ?? null,
                    'description' => $stepData['description'] ?? null,
                ]);
                if (!$step->save()) {
                    throw new \yii\web\ServerErrorHttpException('Не удалось сохранить шаг.');
                }
            }

            foreach ($data['products'] as $productData) {
                $recipeProduct = new RecipeProduct([
                    'recipe_id' => $recipe->id,
                    'product_id' => $productData['product_id'],
                    'measure_id' => $productData['measure_id'],
                    'count' => $productData['count'] ?? null,
                ]);
                if (!$recipeProduct->save()) {
                    throw new \yii\web\ServerErrorHttpException('Не удалось сохранить продукт.');
                }
            }

            if (!empty($data['marks'])) {
                foreach ($data['marks'] as $markId) {
                    $recipeMark = new RecipeMark([
                        'recipe_id' => $recipe->id,
                        'mark_id' => $markId,
                    ]);
                    if (!$recipeMark->save()) {
                        throw new \yii\web\ServerErrorHttpException('Не удалось сохранить метку.');
                    }
                }
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Рецепт успешно обновлен',
                'recipe' => $recipe->toArray(),
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = 500;
            return ['success' => false, 'message' => 'Ошибка при обновлении рецепта', 'error' => $e->getMessage()];
        }
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