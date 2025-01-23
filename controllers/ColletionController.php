<?php
namespace app\controllers;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\models\Recipe;
use app\models\User;
use Yii;
use yii\rest\ActiveController;
use yii\web\Response;

class CollectionController extends ActiveController
{
    public $modelClass = "app\models\Collection";

    public $enableCsrfValidation = false;
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['index', 'view'],
            
        ];
        // $behaviors['access'] = [
        //     'class' => AccessControl::class,
        //     'rules' => [
        //         [
        //             'allow' => true,
        //             'actions' => ['create'],
        //             'roles' => ['@'],
        //         ],
        //         [
        //             'allow' => true,
        //             'actions' => ['update', 'delete'],
        //             'matchCallback' => function ($rule, $action) {
        //                 // Получаем ID записи из параметра запроса
        //                 $id = \Yii::$app->request->get('id');
                        
        //                 if (!$model = Recipe::findOne($id)) { // Подставьте название вашей модели вместо 'Post'
        //                     throw new NotFoundHttpException("Запись не найдена.");
        //                 }
    
        //                 // Проверяем, является ли текущий пользователь автором записи
        //                 return $model->user_id == \Yii::$app->user->identity->id;
        //             },
        //         ],

        //     ],
        // ];
        return $behaviors;
    }
}