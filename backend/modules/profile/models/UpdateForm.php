<?php

namespace app\modules\profile\models;

use app\models\user\User;
use Yii;

class UpdateForm extends \yii\base\Model
{
    public int $id;
    public int $private_id;
    public string $name = '';
    public string $surname = '';
    public string $email = '';
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
            [[ 'name', 'surname', 'email', 'login', 'password', 'password_repeat', 'private_id'], 'required'],
            [['name', 'surname', 'email', 'login', 'password', 'password_repeat'], 'string', 'max' => 255],
            
            ['email', 'email'],

            [['name', 'surname'], 'match', 'pattern' => '/^[а-яё\s]+$/ui', 'message' => 'Разрешена только кириллица и пробелы'],
            ['login', 'match', 'pattern' => '/^[a-z\d\-]+$/i', 'message' => 'Разрешена только латиница, цифры и знак тире'],
            
            // ['password_old', 'validateOldPassword'],
            ['password', 'string', 'min' => 6 ],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'], 

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
            'private_id' => 'Кто может видеть мой профиль?',
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'email' => 'Почта',
            'login' => 'Логин',
            'password_old' => 'Старый пароль',
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
            'avatar' => 'Аватар',
            'photo_header' => 'Шапка профиля',
            'delete_avatar' => 'Удалить аватар',
            'delete_photo_header' => 'Удалить шапку профиля',
        ];
    }

    // public function validateOldPassword($attribute, $params)
    // {
    //     if (!$this->hasErrors()) {
    //         $user = $this->user($this->id);

    //         if (!$user || !$user->validatePassword($this->password_щдв)) {
    //             $this->addError($attribute, 'Неверный старый пароль');
    //         }
    //     }
    // }

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
            ? ['success' => true, 'url' => $fileUrl] 
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
                if($user->avatar){
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

        if($this->validate()){
            $user = User::findIdentity($id);
            if(!empty($_FILES)){
                $this->validateImg($user);
                $this->validate();
            }

            foreach ($this->attributes as $attribute => $value) {
                if ($value !== null && $user->hasAttribute($attribute)) {
                    $user->$attribute = $value;
                }
            }
            // return $user;
            
            if(!empty($this->password)){
                $user->password = Yii::$app->security->generatePasswordHash($this->password);
            }

            $user->save();
        }

        return empty($this->errors) ? $user : $this->errors;
    }

    public function getUser($id)
    {
        if ($this->_user === false) {
            $this->_user = User::findIdentity($id);
        }

        return $this->_user;
    }
}
