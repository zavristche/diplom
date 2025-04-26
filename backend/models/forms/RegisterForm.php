<?php

namespace app\models\forms;

use app\models\PrivateType;
use app\models\Role;
use app\models\user\User;
use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int $role_id
 * @property string $name
 * @property string $surname
 * @property string|null $patronymic
 * @property string $email
 * @property string $login
 * @property string $password
 * @property string $auth_key
 * @property string|null $avatar
 *
 * @property Comment[] $comments
 * @property Post[] $posts
 * @property Role $role
 * @property UserLock[] $userLocks
 */
class RegisterForm extends \yii\base\Model
{

    public string $name = '';
    public string $surname = '';
    public string $email = '';
    public string $login = '';
    public string $password = '';
    public string $password_repeat = '';

    public function rules()
    {
        return [
            [['name', 'surname', 'email', 'login', 'password', 'password_repeat',], 'required'],
            [['name', 'surname', 'email', 'login', 'password', 'password_repeat'], 'string', 'max' => 255],

            ['login', 'unique', 'targetClass' => '\app\models\user\User', 'message' => 'Логин уже занят'],
            ['email', 'unique', 'targetClass' => '\app\models\user\User', 'message' => 'Email уже занят'],
            ['email', 'email'],

            [['name', 'surname'], 'match', 'pattern' => '/^[а-яё\s]+$/ui', 'message' => 'Разрешена только кириллица и пробелы'],
            ['login', 'match', 'pattern' => '/^[a-z\d\-]+$/i', 'message' => 'Разрешена только латиница, цифры и знак тире'],

            ['password', 'string', 'min' => 6],
            ['password_repeat', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Имя',
            'surname' => 'Фамилия',
            'email' => 'Почта',
            'login' => 'Логин',
            'password' => 'Пароль',
            'password_repeat' => 'Повтор пароля',
        ];
    }

    public function register()
    {
        if ($this->validate()) {
            $user = new User();
            $backendUrl = Yii::$app->params['backendUrl'];

            $user->attributes = $this->attributes;
            $user->password = Yii::$app->security->generatePasswordHash($this->password);
            $user->auth_key = Yii::$app->security->generateRandomString();
            $user->role_id = Role::getOne('user');
            $user->private_id = PrivateType::getOne('Все');
            $user->avatar = $backendUrl . '/uploads/default_avatar.jpg';

            if (!$user->save()) {
                VarDumper::dump($user->errors);
            }
        }

        return $user ?? null;
    }
}
