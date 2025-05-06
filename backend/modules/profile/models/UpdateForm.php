<?php

namespace app\modules\profile\models;

use app\models\user\User;
use Yii;
use yii\web\NotFoundHttpException;

class UpdateForm extends \yii\base\Model
{
    public int $id;
    public string $name = '';
    public string $surname = '';
    public string $email = '';
    public string $status = '';
    public string $login = '';
    public string $password = '';
    public string $password_repeat = '';
    // public string $password_old = '';
    public $avatar;
    public $photo_header;
    public bool $delete_avatar = false;
    public bool $delete_photo_header = false;

    const SCENARIO_AVATAR = 'avatar';
    const SCENARIO_PHOTO_HEADER = 'photo_header';
    const SCENARIO_TWO_IMG = 'two_img';

    public function rules()
    {
        return [
            [['name', 'surname', 'email', 'login', 'password', 'status'], 'string', 'max' => 255],
            [['name', 'surname', 'email', 'login'], 'required'], // Оставляем обязательными только ключевые поля
            ['email', 'email'],
            [['name', 'surname'], 'match', 'pattern' => '/^[а-яё\s]+$/ui', 'message' => 'Разрешена только кириллица и пробелы'],
            ['login', 'match', 'pattern' => '/^[a-z\d\-]+$/i', 'message' => 'Разрешена только латиница, цифры и знак тире'],
            ['password', 'string', 'min' => 6, 'skipOnEmpty' => true], // Пароль необязателен
            [['avatar', 'photo_header'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg'],
            [['delete_avatar', 'delete_photo_header'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'email' => 'Почта',
            'login' => 'Логин',
            'status' => 'Статус',
            'password_old' => 'Старый пароль',
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
            'avatar' => 'Аватар',
            'photo_header' => 'Шапка профиля',
            'delete_avatar' => 'Удалить аватар',
            'delete_photo_header' => 'Удалить шапку профиля',
        ];
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

    public function validateImg($user)
    {

        if (!empty($_FILES["avatar"]["name"])) {
            $upload = $this->uploadImage($_FILES["avatar"]);
            if (!$upload['success']) {
                $this->addError('avatar', $upload['message']);
            } else {
                $this->avatar = $upload['url'];
                
                // Проверяем, что у пользователя был аватар И это не дефолтный аватар
                if ($user->avatar && basename($user->avatar) !== 'default_avatar.jpg') {
                    $photoPath = Yii::getAlias('@webroot') . parse_url($user->avatar, PHP_URL_PATH);
                    
                    if ($this->avatar && file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }
            }
        }

        if (!empty($_FILES["photo_header"]["name"])) {

            $upload = $this->uploadImage($_FILES["photo_header"]);
            if (!$upload['success']) {
                $this->addError('photo_header', $upload['message']);
            } else {
                $this->photo_header = $upload['url'];
                if($user->photo_header){
                    $photoPath = Yii::getAlias('@webroot') . parse_url($user->photo_header, PHP_URL_PATH);
    
                    if ($this->photo_header && file_exists($photoPath)) {
                        unlink($photoPath);
                    }
                }
            }
        }
    }

    public function update($id)
    {
        $user = User::findIdentity($id);
        if (!$user) {
            throw new NotFoundHttpException('Пользователь не найден');
        }
    
        if (!$this->validate()) {
            return $this->errors;
        }
    
        if (!empty($_FILES)) {
            $this->validateImg($user);
            if (!$this->validate()) {
                return $this->errors;
            }
        }
    
        foreach ($this->attributes as $attribute => $value) {
            if ($value !== null && $user->hasAttribute($attribute) && $attribute !== 'password') {
                $user->$attribute = $value;
            }
        }
    
        if (!empty($this->password)) {
            $user->password = Yii::$app->security->generatePasswordHash($this->password);
        }
    
        if (!$user->save()) {
            $this->addErrors($user->errors);
            return $this->errors;
        }
    
        return $user;
    }

    public function getUser($id)
    {
        if ($this->_user === false) {
            $this->_user = User::findIdentity($id);
        }

        return $this->_user;
    }
}
