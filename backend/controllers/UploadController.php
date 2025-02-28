<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\web\BadRequestHttpException;

class UploadController extends Controller
{
    public function actionUpload()
    {

        $file = UploadedFile::getInstanceByName('photo');
        if (!$file) {
            throw new BadRequestHttpException('Файл не был загружен.');
        }

        $fileName = Yii::$app->user->id . '_' . time() . '_' . Yii::$app->security->generateRandomString() . '.' . $file->imageFile->extension;

        $filePath = Yii::getAlias('@webroot/uploads/') . $fileName;

        if (!is_dir(Yii::getAlias('@webroot/uploads/'))) {
            mkdir(Yii::getAlias('@webroot/uploads/'), 0777, true);
        }

        if ($file->saveAs($filePath)) {
            return [
                'success' => true,
                'url' => Yii::$app->request->hostInfo . '/uploads/' . $fileName
            ];
        }

        return ['success' => false, 'message' => 'Ошибка при загрузке файла.'];
    }
}
