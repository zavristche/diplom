<?php
namespace app\modules\admin\controllers;

use app\models\Measure;
use yii\filters\AccessControl;
use app\models\recipe\Recipe;
use app\models\recipe\RecipeMark;
use app\models\recipe\RecipeProduct;
use app\modules\admin\models\RecipeSearch;
use app\models\Role;
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
        ];
        
        $behaviors['access'] = [
            'class' => \yii\filters\AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['apply', 'cancel', 'delete', 'index', 'view'],
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
        $user = Yii::$app->user->identity;
        $adminRoleId = Role::getOne('admin');
        \Yii::info("Checking access for user_id: {$user->id}, role_id: {$user->role_id}, required admin role_id: {$adminRoleId}", __METHOD__);
        
        if ($user->role_id !== $adminRoleId) {
            throw new \yii\web\NotFoundHttpException('Страница не найдена');
        }
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);
        unset($actions['index']);

        return $actions;
    }
    
    public function actionIndex()
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

    public function actionApply($id)
    {
        $recipe = $this->findModel($id);
        $recipe->status_id = StatusContent::getOne('Опубликовано');
    
        $recipe->save();
        return ['success' => true, 'message' => 'Рецепт опубликован', 'recipe' => $recipe->toArray()];
    }

    public function actionCancel($id)
    {
        $recipe = $this->findModel($id);
        $recipe->scenario = Recipe::SCENARIO_CANCEL;
        $recipe->status_id = StatusContent::getOne('Отклонено');
    
        $data = Yii::$app->request->getBodyParams();
        \Yii::info("Received data for cancel: " . json_encode($data), __METHOD__);
    
        $transaction = Yii::$app->db->beginTransaction();
        $errors = [];
    
        try {
            // Рецепт
            foreach ($data as $attribute => $value) {
                if ($recipe->hasAttribute($attribute)) {
                    $recipe->$attribute = $value;
                }
            }
    
            if (!$recipe->validate()) {
                $errors = array_merge($errors, $recipe->errors);
                \Yii::info("Validation errors: " . json_encode($recipe->errors), __METHOD__);
            }

            if (!empty($errors)) {
                Yii::$app->response->statusCode = 422;
                return ['success' => false, 'message' => 'Ошибка валидации', 'errors' => $errors];
            }
    
            $recipe->save();

            $transaction->commit();
            return ['success' => true, 'message' => 'Рецепт отклонен', 'recipe' => $recipe->toArray()];
        } catch (\Exception $e) {
            $transaction->rollBack();
            return ['success' => false, 'message' => 'Ошибка обновления', 'error' => $e->getMessage()];
        }
    }

    private function deleteRelatedData($model)
    {
        // Удаляем фото рецепта
        $this->deleteImage($model->photo);

        // Удаляем шаги и их фото
        $steps = Step::findAll(['recipe_id' => $model->id]);
        foreach ($steps as $step) {
            $this->deleteImage($step->photo);
            $step->delete(); // Удаляем шаг
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