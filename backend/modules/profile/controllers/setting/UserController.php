<?php

namespace app\modules\profile\controllers\setting;

use app\models\user\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\controllers\BaseApiController;
use app\modules\profile\models\UpdateForm;

class UserController extends BaseApiController
{
    public $modelClass = "app\models\user\User";

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'delete' => ['DELETE'],
                'update' => ['PATCH'],
            ],
        ];
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['index', 'view', 'search'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'update'],
                ],
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['delete']);
        unset($actions['update']);
        unset($actions['create']);

        return $actions;
    }

    public function actionUpdate($id)
    {
        Yii::info('Received PATCH request: ' . json_encode([
            'POST' => Yii::$app->request->post(),
            'FILES' => $_FILES,
        ]), __METHOD__);
    
        $model = new UpdateForm(['id' => $id]);
        $postData = Yii::$app->request->post();
        $model->load($postData, '');
    
        // Передаем файлы в модель
        if (isset($_FILES['avatar'])) {
            $model->avatar = $_FILES['avatar'];
        }
        if (isset($_FILES['photo_header'])) {
            $model->photo_header = $_FILES['photo_header'];
        }
    
        $user = $model->update($id);
    
        if (!empty($model->errors)) {
            Yii::$app->response->statusCode = 422;
            Yii::info('Validation errors: ' . json_encode($model->errors), __METHOD__);
            return ['success' => false, 'errors' => $model->errors];
        }
    
        return ['success' => true, 'user' => $user->toArray()];
    }

    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            unset($model['auth_key']);
            unset($model['password']);
            return $model;
        }
        throw new NotFoundHttpException('Страница не найдена');
    }
}
