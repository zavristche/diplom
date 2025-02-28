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
use yii\rest\ActiveController;
use yii\web\NotFoundHttpException;

class CollectionController extends ActiveController
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
            throw new \yii\web\UnauthorizedHttpException('Доступ запрещен');
        }
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['delete']);
        unset($actions['update']);
        // unset($actions['index']);

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
        $params = $request->post();
    
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

    public function actionCreate()
    {
        $data = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
    
        try {
            $collection = new Collection();
            $collection->user_id = Yii::$app->user->id;
            $collection->status_id = StatusContent::getOne('Новый');
    
            if (!$collection->load($data, '') || !$collection->validate()) {
                $errors = array_merge($errors, $collection->errors);
            }
    
            if (!empty($errors)) {
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $errors,
                ];
            }
    
            $collection->save();
    
            if (!empty($data['products'])) {
                foreach ($data['products'] as $id) {
                    $collectionProduct = new CollectionProduct();
                    $collectionProduct->collection_id = $collection->id;
                    $collectionProduct->product_id = $id;
                    if (!$collectionProduct->save()) {
                        $errors['products'][] = $collectionProduct->errors;
                    }
                }
            }

            if (!empty($data['marks'])) {
                foreach ($data['marks'] as $id) {
                    $collectionMark = new CollectionMark();
                    $collectionMark->collection_id = $collection->id;
                    $collectionMark->mark_id = $id;
                    if (!$collectionMark->save()) {
                        $errors['marks'][] = $collectionMark->errors;
                    }
                }
            }
    
            if (!empty($errors)) {
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Ошибка при сохранении связанных данных',
                    'errors' => $errors,
                ];
            }
    
            $transaction->commit();
    
            return [
                'success' => true,
                'message' => 'Коллекция успешно создана',
                'collection' => $collection->toArray(),
            ];
        } catch (\Exception $e) {
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

        try {
            $collection = Collection::findOne($id);
            if (!$collection) {
                Yii::$app->response->statusCode = 404;
                return [
                    'success' => false,
                    'message' => 'Коллекция не найдена',
                ];
            }

            if (!$collection->load($data, '') || !$collection->validate()) {
                $errors = array_merge($errors, $collection->errors);
            }

            if (!empty($errors)) {
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Ошибка валидации',
                    'errors' => $errors,
                ];
            }

            if (!$collection->save()) {
                throw new \yii\web\ServerErrorHttpException('Не удалось обновить коллекцию.');
            }

            CollectionProduct::deleteAll(['collection_id' => $collection->id]);
            CollectionMark::deleteAll(['collection_id' => $collection->id]);

            if (!empty($data['products'])) {
                foreach ($data['products'] as $id) {
                    $collectionProduct = new CollectionProduct();
                    $collectionProduct->collection_id = $collection->id;
                    $collectionProduct->product_id = $id;
                    if (!$collectionProduct->save()) {
                        $errors['products'][] = $collectionProduct->errors;
                    }
                }
            }

            if (!empty($data['marks'])) {
                foreach ($data['marks'] as $id) {
                    $collectionMark = new CollectionMark();
                    $collectionMark->collection_id = $collection->id;
                    $collectionMark->mark_id = $id;
                    if (!$collectionMark->save()) {
                        $errors['marks'][] = $collectionMark->errors;
                    }
                }
            }

            if (!empty($errors)) {
                Yii::$app->response->statusCode = 422;
                return [
                    'success' => false,
                    'message' => 'Ошибка при обновлении связанных данных',
                    'errors' => $errors,
                ];
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
    
        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Коллекция и все связанные данные успешно удалены.',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении коллекции.'); 
    } 

}