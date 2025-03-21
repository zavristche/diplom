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
use app\models\Comment;
use yii\web\NotFoundHttpException;

class CommentController extends BaseApiController
{
    public $modelClass = "app\models\Comment";

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

    public function actionCreate()
    {
        $data = Yii::$app->request->post();
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
        $uploadedFilePath = null;
        try {
            $comment = new Comment();
            $comment->user_id = Yii::$app->user->id;

            if (!$comment->load($data, '') || !$comment->validate()) {
                $errors = array_merge($errors, $comment->errors);
            }

            // Фото комментария
            if (!empty($_FILES["photo"]["name"])) {
                $upload = $this->uploadImage($_FILES["photo"]);
                if (!$upload['success']) {
                    $errors['photo'] = $upload['message'];
                } else {
                    $comment->imageFile = $upload['url'];
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
                    'comment' => $comment->attributes,
                ];
            }

            $comment->photo = $comment->imageFile;
            $comment->save();

            $transaction->commit();

            return [
                'success' => true,
                'message' => 'Коллекция успешно создана',
                'collection' => $comment->toArray(),
            ];
        } catch (\Exception $e) {
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

    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->deleteRelatedData($model);

        if ($model->delete()) {
            return [
                'success' => true,
                'message' => 'Комментарий и все связанные данные успешно удалены.',
            ];
        }

        throw new \yii\web\ServerErrorHttpException('Ошибка при удалении комментария.'); 
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