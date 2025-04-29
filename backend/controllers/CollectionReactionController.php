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
                    'roles' => ['@'],
                    'actions' => ['create', 'delete', 'check'], // Добавляем check
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
        unset($actions['check']); // Добавляем check в исключения

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
        $collection = Collection::findOne($data['collection_id']);

        if ($collection->user_id == Yii::$app->user->id) {
            return [
                'success' => false,
                'message' => 'Вы не можете лайкнуть свой собственную коллекцию'
            ];
        }

        $model = new CollectionReaction([
            'collection_id' => $data['collection_id'],
            'user_id' => Yii::$app->user->id,
        ]);

        if ($model->save()) {
            return ['success' => true, 'model' => $model->attributes];
        }

        return ['success' => false, 'errors' => $model->errors, 'attributes' => $model->attributes];
    }

    public function actionDelete()
    {
        $data = Yii::$app->request->getBodyParams();
        $model = CollectionReaction::findOne(['collection_id' => $data['collection_id'], 'user_id' => Yii::$app->user->id]);
        
        if ($model && $model->delete()) {
            return [
                'success' => true,
                'message' => 'Лайк убран',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении реакции.'); 
    }

    public function actionCheck()
    {
        $data = Yii::$app->request->getBodyParams();
        $model = CollectionReaction::findOne(['collection_id' => $data['collection_id'], 'user_id' => $data['user_id']]);

        return [
            'success' => true,
            'isLiked' => !is_null($model), // true, если лайк есть, false, если нет
        ];
    }
}