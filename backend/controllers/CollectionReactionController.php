<?php
namespace app\controllers;
use yii\filters\AccessControl;
use app\models\recipe\RecipeReaction;
use Yii;
use app\controllers\BaseApiController;
use app\models\collection\Collection;
use app\models\collection\CollectionReaction;

class CollectionReactionController extends BaseApiController
{
    public $modelClass = 'app\models\collection\CollectionReaction';

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
                    'roles' => ['@'],
                    'actions' => ['create', 'delete', 'check'],
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
        unset($actions['check']);

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
        
        // Проверяем, существует ли коллекция
        $collection = Collection::findOne($data['collection_id']);
        if (!$collection) {
            throw new \yii\web\NotFoundHttpException('Коллекция не найдена.');
        }

        // Проверяем, не лайкает ли пользователь свою собственную коллекцию
        if ($collection->user_id == Yii::$app->user->id) {
            return [
                'success' => false,
                'message' => 'Вы не можете ставить реакцию на собственную коллекцию',
            ];
        }

        if (Yii::$app->user->identity->isAdmin) {
            return [
                'success' => false,
                'message' => 'Администратор не может ставить реакцию',
            ];
        }

        // Проверяем, не существует ли уже реакция
        $existingReaction = CollectionReaction::findOne(['collection_id' => $data['collection_id'], 'user_id' => Yii::$app->user->id]);
        if ($existingReaction) {
            return [
                'success' => false,
                'message' => 'Вы уже лайкнули эту коллекцию',
            ];
        }

        // Начинаем транзакцию
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $model = new CollectionReaction([
                'collection_id' => $data['collection_id'],
                'user_id' => Yii::$app->user->id,
            ]);

            if ($model->save()) {
                // Увеличиваем likes в модели Collection
                $collection->likes = $collection->likes + 1;
                if (!$collection->save(false)) {
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
        
        // Проверяем, существует ли коллекция
        $collection = Collection::findOne($data['collection_id']);
        if (!$collection) {
            throw new \yii\web\NotFoundHttpException('Коллекция не найдена.');
        }

        // Находим реакцию
        $model = CollectionReaction::findOne(['collection_id' => $data['collection_id'], 'user_id' => Yii::$app->user->id]);
        if (!$model) {
            throw new \yii\web\NotFoundHttpException('Реакция не найдена.');
        }

        // Начинаем транзакцию
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->delete()) {
                // Уменьшаем likes в модели Collection
                $collection->likes = $collection->likes > 0 ? $collection->likes - 1 : 0; // Не допускаем отрицательное значение
                if (!$collection->save(false)) { // Сохраняем без валидации
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
        $model = CollectionReaction::findOne(['collection_id' => $data['collection_id'], 'user_id' => $data['user_id']]);

        return [
            'success' => true,
            'isLiked' => !is_null($model),
        ];
    }
}