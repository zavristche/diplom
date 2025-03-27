<?php
namespace app\controllers;

use app\models\collection\Collection;
use app\models\collection\CollectionMark;
use app\models\collection\CollectionProduct;
use app\models\collection\CollectionSearch;
use app\models\StatusContent;
use yii\filters\AccessControl;
use Yii;
use yii\data\ActiveDataProvider;
use app\controllers\BaseApiController;
use app\models\mark\Mark;
use app\models\mark\MarkType;
use app\models\PrivateType;
use app\models\product\Product;
use app\models\product\ProductType;
use yii\web\NotFoundHttpException;

class CollectionController extends BaseApiController
{
    public $modelClass = "app\models\collection\Collection";

    protected function findModel($id)
    {
        $model = Collection::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Коллекция не найдена.');
        }
        return $model;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['index', 'view', 'search', 'create-data'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'search', 'create-data'],
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

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['update']);

        return $actions;
    }

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'update' || $action === 'delete') {
            if ($model->user_id !== \Yii::$app->user->id)
                throw new \yii\web\ForbiddenHttpException(sprintf('Вы можете выполнять это действие только с теми коллекциями, которые вы создали.', $action));
        }
    }

    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Collection::find()->with([
                'user', 
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

    public function actionSearch()
    {
        $request = Yii::$app->request;
        $params = $request->getQueryParams();
    
        $searchModel = new CollectionSearch();
        $dataProvider = $searchModel->search($params);

        $models = $dataProvider->getModels();

        $result = array_map(function ($model) {

            $data = $model->toArray([]);
    
            if (isset($data['user'])) {
                unset($data['user']['auth_key'], $data['user']['password']);
            }
    
            return $data;
        }, $models);

        return $result;
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
            $mark_types = MarkType::getAll();
            $privates = PrivateType::getAll();

            // Формируем ответ
            return [
                'success' => true,
                'data' => [
                    'products' => $products,
                    'product_types' => $product_types,
                    'marks' => $marks,
                    'mark_types' => $mark_types,
                    'privates' => $privates,
                ],
                'message' => 'Данные для создания коллекции успешно получены',
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
        $data = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
        $products = [];
        $marks = [];
        $uploadedFilePath = null;
        try {
            $collection = new Collection();
            $collection->user_id = Yii::$app->user->id;
            $collection->status_id = StatusContent::getOne('Новое');

            if (!$collection->load($data, '') || !$collection->validate()) {
                $errors = array_merge($errors, $collection->errors);
            }

            if (isset($data['products'])) {
                foreach ($data['products'] as $id) {
                    $collectionProduct = new CollectionProduct([
                        'product_id' => $id,
                    ]);
                    $products[] = $collectionProduct;
                }
            }

            if (isset($data['marks'])) {
                foreach ($data['marks'] as $id) {
                    $collectionMark = new CollectionMark([
                        'mark_id' => $id,
                    ]);
                    $marks[] = $collectionMark;
                }
            }

            // Фото коллекции
            if (!empty($_FILES["photo"]["name"])) {
                $upload = $this->uploadImage($_FILES["photo"]);
                if (!$upload['success']) {
                    $errors['photo'] = $upload['message'];
                } else {
                    $collection->imageFile = $upload['url'];
                    $uploadedFilePath = $upload['filePath'];
                }
            }

            if (!empty($errors)) {
                if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                    unlink($uploadedFilePath);
                }
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $errors,
                    'collection' => $collection->attributes,
                    'products' => $products,
                    'marks' => $marks
                ];
            }

            $collection->photo = $collection->imageFile;
            $collection->save();

            foreach ($products ?? [] as $product) {
                $product->collection_id = $collection->id;
                $product->save();
            }

            foreach ($marks ?? [] as $mark) {
                $mark->collection_id = $collection->id;
                $mark->save();
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Коллекция успешно создана',
                'collection' => $collection->toArray(),
            ];
        } catch (\Exception $e) {
            // В случае исключения удаляем загруженный файл
            if ($uploadedFilePath && file_exists($uploadedFilePath)) {
                unlink($uploadedFilePath);
            }
            $transaction->rollBack();
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Ошибка при создании коллекции',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function actionUpdate($id)
    {
        $data = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
        
        $marks = [];
        $products = [];

        try {
            $collection = Collection::findOne($id);
            if (!$collection) {
                Yii::$app->response->statusCode = 404;
                return [
                    'success' => false,
                    'message' => 'Коллекция не найдена',
                ];
            }

            // Фото коллекции
            if (!empty($_FILES["photo"]["name"])) {
                
                if (!empty($collection->photo)) {
                    $photoPath = Yii::getAlias('@webroot') . parse_url($collection->photo, PHP_URL_PATH);

                    if (file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }

                $upload = $this->uploadImage($_FILES["photo"]);
                if (!$upload['success']) {
                    $errors['photo'] = $upload['message'];
                } else {
                    $collection->photo = $upload['url'];
                }
            }

            // Коллекция
            foreach ($data as $attribute => $value) {
                if ($collection->hasAttribute($attribute)) {
                    $collection->$attribute = $value;
                }
            }

            // Продукты
            if (isset($data['products'])) {
                foreach($data['products'] as $id) {
                    $collectionProduct = new CollectionProduct([
                        'product_id' => $id,
                    ]);
                    $products[] = $collectionProduct;
                }
            }

            // Метки
            if (isset($data['marks'])) {
                foreach($data['marks'] as $id) {
                    $collectionMark = new CollectionMark([
                        'mark_id' => $id,
                    ]);
                    $marks[] = $collectionMark;
                }
            }

            if (!empty($errors)) {
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $errors,
                    'collection' => $collection,
                    'products' => $products,
                    'marks' => $marks
                ];
            }

            $collection->save();

            CollectionMark::deleteAll(['collection_id' => $collection->id]);
            CollectionProduct::deleteAll(['collection_id' => $collection->id]);

            foreach ($products as $product) {
                $product->collection_id = $collection->id;
                $product->save();
            }

            foreach ($marks as $mark) {
                $mark->collection_id = $collection->id;
                $mark->save();
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Коллекция успешно обновлена',
                'collection' => $collection->toArray(),
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Ошибка при обновлении коллекции',
                'error' => $e->getMessage(),
            ];
        }
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->deleteRelatedData($model);

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Коллекция и все связанные данные успешно удалены.',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении коллекции.'); 
    }

    private function deleteRelatedData($model)
    {
        if ($model->photo) {
            $filePath = Yii::getAlias('@webroot') . parse_url($model->photo, PHP_URL_PATH);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
}