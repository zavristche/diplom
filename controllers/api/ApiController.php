<?php
namespace app\controllers\api;

use Yii;
use yii\rest\Controller;
use yii\web\Response;

class ApiController extends Controller
{
    public $enableCsrfValidation = false;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => \yii\filters\ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }

    protected function successResponse($data, $message = 'OK')
    {
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ];
    }

    protected function errorResponse($message = 'Error', $code = 400)
    {
        Yii::$app->response->statusCode = $code;
        return [
            'status' => 'error',
            'message' => $message,
        ];
    }
}
