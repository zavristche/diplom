<?php
namespace app\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;

class BaseApiController extends ActiveController
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        if (!Yii::$app->user->isGuest && !Yii::$app->user->identity->isAdmin && Yii::$app->user->identity->isBlocked()) {
            Yii::$app->user->logout();
            throw new ForbiddenHttpException('Ваш аккаунт заблокирован.');
        }

        return true;
    }
}
