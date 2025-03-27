<?php
namespace app\controllers;

use yii\filters\AccessControl;
use Yii;
use app\controllers\BaseApiController;
use app\models\Complexity;
use app\models\mark\Mark;
use app\models\mark\MarkType;
use app\models\Measure;
use app\models\PrivateType;
use app\models\product\Product;
use app\models\product\ProductType;

class SearchController extends BaseApiController
{
    public $modelClass = "app\models\collection\Collection";

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => \yii\filters\auth\HttpBearerAuth::class,
            'except' => ['data'],
        ];
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['data'],
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
        unset($actions['update']);
        unset($actions['view']);

        return $actions;
    }

    public function actionData()
    {
        try {
            $products = Product::getAll();
            $product_types = ProductType::getAll();
            $marks = Mark::getAll();
            $privates = PrivateType::getAll();
            $measures = Measure::getAll();
            $complexities = Complexity::getAll();
            $mark_types = MarkType::getAll();

            return [
                'success' => true,
                'data' => [
                    'products' => $products,
                    'product_types' => $product_types,
                    'marks' => $marks,
                    'measures' => $measures,
                    'privates' => $privates,
                    'complexities' => $complexities,
                    'mark_types' => $mark_types,
                ],
                'message' => 'Данные для поиска получены',
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
}