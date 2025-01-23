<?php
namespace app\controllers;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use app\models\Recipe;
use Yii;
use yii\rest\ActiveController;
use app\resources\RecipeResource;

class RecipeController extends ActiveController
{
    public $modelClass = "app\models\Recipe";

    public $enableCsrfValidation = false;
    
    public function actions()
    {
        return ArrayHelper::merge(parent::actions(), [
            'index' => [
                'pagination' => [
                    'pageSize' => 10,
                ],
                'sort' => [
                    'defaultOrder' => [
                        'created_at' => SORT_DESC,
                    ],
                ],
            ],
        ]);
    }
     
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['index', 'view',],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                ],
                [
                    'allow' => true,
                    'actions' => ['create', 'update', 'delete'],
                    'roles' => ['@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['update', 'delete'],
                    'matchCallback' => function ($rule, $action) {
                        $id = \Yii::$app->request->get('id');
                        
                        if (!$model = Recipe::findOne($id)) {
                            throw new NotFoundHttpException("Запись не найдена.");
                        }
    
                        return $model->user_id == \Yii::$app->user->identity->id;
                    },
                ],
            ],
        ];
        return $behaviors;
    }

    protected function serializeData($data)
    {
        if ($data instanceof Recipe) {
            return new RecipeResource($data);
        }
        return $data;
    }
}