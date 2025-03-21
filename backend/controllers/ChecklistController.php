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
use app\models\checklist\Checklist;
use app\models\checklist\ChecklistProduct;
use app\models\checklist\ChecklistSearch;
use yii\web\NotFoundHttpException;

class ChecklistController extends BaseApiController
{
    public $modelClass = "app\models\checklist\checklist";

    protected function findModel($id)
    {
        $model = Checklist::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Список покупок не найден.');
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
        if ($model->user_id !== \Yii::$app->user->id){
            throw new \yii\web\ForbiddenHttpException(sprintf('Вы можете выполнять это действие только с теми коллекциями, которые вы создали.', $action));

        }
    }

    public function actionSearch()
    {
        $request = Yii::$app->request;
        $params = $request->getQueryParams();
    
        $searchModel = new ChecklistSearch();
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

    public function actionCreate()
    {
        $data = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
        $products = [];

        try {
            $checklist = new Checklist();
            $checklist->user_id = Yii::$app->user->id;
            $checklist->status_id = StatusContent::getOne('Новое');

            if (!$checklist->load($data, '') || !$checklist->validate()) {
                $errors = array_merge($errors, $checklist->errors);
            }

            if (!($data['products'] ?? [])) {
                $errors['products'][] = 'Список покупок должен содержать хотя бы один продукт.';
            }

            foreach ($data['products'] as $id) {
                $collectionProduct = new ChecklistProduct([
                    'product_id' => $id,
                ]);
                $products[] = $collectionProduct;
            }

            if (isset($data['marks'])) {
                foreach ($data['marks'] as $id) {
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
                    'checklist' => $checklist->attributes,
                    'products' => $products,
                ];
            }

            $checklist->save();

            foreach ($products ?? [] as $product) {
                $product->list_id = $checklist->id;
                $product->save();
            }

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Список покупок успешно создан',
                'checklist' => $checklist->toArray(),
            ];
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->response->statusCode = 500;
            return [
                'success' => false,
                'message' => 'Ошибка при создании списка покупок',
                'error' => $e->getMessage(),
            ];
        }
    }

    // public function actionUpdate($id)
    // {
    //     $data = Yii::$app->request->post();
    //     $transaction = Yii::$app->db->beginTransaction();
    //     $errors = [];
        
    //     $marks = [];
    //     $products = [];

    //     try {
    //         $collection = Collection::findOne($id);
    //         if (!$collection) {
    //             Yii::$app->response->statusCode = 404;
    //             return [
    //                 'success' => false,
    //                 'message' => 'Коллекция не найдена',
    //             ];
    //         }

    //         // Фото коллекции
    //         if (!empty($_FILES["photo"]["name"])) {
                
    //             if (!empty($collection->photo)) {
    //                 $photoPath = Yii::getAlias('@webroot') . parse_url($collection->photo, PHP_URL_PATH);

    //                 if (file_exists($photoPath)) {
    //                     unlink($photoPath);
    //                 }
    //             }

    //             $upload = $this->uploadImage($_FILES["photo"]);
    //             if (!$upload['success']) {
    //                 $errors['photo'] = $upload['message'];
    //             } else {
    //                 $collection->photo = $upload['url'];
    //             }
    //         }

    //         // Коллекция
    //         foreach ($data as $attribute => $value) {
    //             if ($collection->hasAttribute($attribute)) {
    //                 $collection->$attribute = $value;
    //             }
    //         }

    //         // Продукты
    //         if (isset($data['products'])) {
    //             foreach($data['products'] as $id) {
    //                 $collectionProduct = new CollectionProduct([
    //                     'product_id' => $id,
    //                 ]);
    //                 $products[] = $collectionProduct;
    //             }
    //         }

    //         // Метки
    //         if (isset($data['marks'])) {
    //             foreach($data['marks'] as $id) {
    //                 $collectionMark = new CollectionMark([
    //                     'mark_id' => $id,
    //                 ]);
    //                 $marks[] = $collectionMark;
    //             }
    //         }

    //         if (!empty($errors)) {
    //             Yii::$app->response->statusCode = 422;
    //             return [
    //                 'success' => false,
    //                 'message' => 'Ошибка валидации',
    //                 'errors' => $errors,
    //                 'collection' => $collection,
    //                 'products' => $products,
    //                 'marks' => $marks
    //             ];
    //         }

    //         $collection->save();

    //         CollectionMark::deleteAll(['collection_id' => $collection->id]);
    //         CollectionProduct::deleteAll(['collection_id' => $collection->id]);

    //         foreach ($products as $product) {
    //             $product->collection_id = $collection->id;
    //             $product->save();
    //         }

    //         foreach ($marks as $mark) {
    //             $mark->collection_id = $collection->id;
    //             $mark->save();
    //         }

    //         $transaction->commit();

    //         return [
    //             'success' => true,
    //             'message' => 'Коллекция успешно обновлена',
    //             'collection' => $collection->toArray(),
    //         ];
    //     } catch (\Exception $e) {
    //         $transaction->rollBack();
    //         Yii::$app->response->statusCode = 500;
    //         return [
    //             'success' => false,
    //             'message' => 'Ошибка при обновлении коллекции',
    //             'error' => $e->getMessage(),
    //         ];
    //     }
    // }

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