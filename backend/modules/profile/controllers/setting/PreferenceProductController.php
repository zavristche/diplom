<?php

namespace app\modules\profile\controllers\setting;

use Yii;
use yii\filters\AccessControl;
use app\controllers\BaseApiController;
use app\models\preference\PreferenceProduct;

class PreferenceProductController extends BaseApiController
{
    public $modelClass = "app\models\preference\PreferenceMark";

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
                    'roles' => ['@'],
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

    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'update') {
            if ($model->id !== \Yii::$app->user->id)
                throw new \yii\web\ForbiddenHttpException(sprintf('Доступ запрещен', $action));
        }
    }

    public function actionCreate()
    {
        $data = Yii::$app->request->post();

        $preferences = PreferenceProduct::findAll(['user_id' => Yii::$app->user->id]);
        foreach ($preferences as $preference) {
            $preference->delete();
        }

        foreach ($data['preference_products'] as $id) {
            $preference = new PreferenceProduct([
                'user_id' => Yii::$app->user->id,
                'product_id' => $id,
            ]);
            $newPreferences[] = $preference;
        }
        
        foreach ($newPreferences ?? [] as $preference) {
            $preference->save();
        }

        return [
            'success' => true,
            'message' => 'Предпочтения в продуктах обновлены',
            'preferences' => $newPreferences,
        ];
    }
}
