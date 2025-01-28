<?php

namespace app\models\user;

use Yii;
use app\models\checklist\Checklist;
use app\models\collection\CollectionReaction;
use app\models\collection\CollectionSubscribe;
use app\models\collection\Collection;
use app\models\Comment;
use app\models\preference\PreferenceMark;
use app\models\preference\PreferenceProduct;
use app\models\PrivateType;
use app\models\recipe\RecipeCalendar;
use app\models\recipe\RecipeNote;
use app\models\recipe\RecipeReaction;
use app\models\recipe\Recipe;
use app\models\Role;
use app\models\user\UserBlacklist;
use app\models\user\UserSubscribe;
use app\models\user\UserAllergen;
use app\models\block\BlockUser;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int $role_id
 * @property int $private_id
 * @property string|null $last_active
 * @property string|null $status
 * @property string $name
 * @property string $surname
 * @property string $login
 * @property string $email
 * @property string $password
 * @property string|null $avatar
 * @property string|null $photo_header
 * @property string $auth_key
 *
 * @property BlockUser[] $blockUsers
 * @property Checklist[] $checklists
 * @property CollectionReaction[] $collectionReactions
 * @property CollectionSubscribe[] $collectionSubscribes
 * @property Collection[] $collections
 * @property Comment[] $comments
 * @property PreferenceMark[] $preferenceMarks
 * @property PreferenceProduct[] $preferenceProducts
 * @property PrivateType $private
 * @property RecipeCalendar[] $recipeCalendars
 * @property RecipeNote[] $recipeNotes
 * @property RecipeReaction[] $recipeReactions
 * @property Recipe[] $recipes
 * @property Role $role
 * @property UserBlacklist[] $userBlacklists
 * @property UserBlacklist[] $userBlacklists0
 * @property UserSubscribe[] $userSubscribes
 * @property UserSubscribe[] $userSubscribes0
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role_id', 'private_id', 'name', 'surname', 'login', 'email', 'password', 'auth_key'], 'required'],
            [['role_id', 'private_id'], 'integer'],
            [['last_active'], 'safe'],
            [['status', 'name', 'surname', 'login', 'email', 'password', 'avatar', 'photo_header', 'auth_key'], 'string', 'max' => 255],
            [['login', 'email'], 'unique', 'targetAttribute' => ['login', 'email']],
            [['private_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrivateType::class, 'targetAttribute' => ['private_id' => 'id']],
            [['role_id'], 'exist', 'skipOnError' => true, 'targetClass' => Role::class, 'targetAttribute' => ['role_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'role_id' => 'Role ID',
            'private_id' => 'Private ID',
            'last_active' => 'Last Active',
            'status' => 'Status',
            'name' => 'Name',
            'surname' => 'Surname',
            'login' => 'Login',
            'email' => 'Email',
            'password' => 'Password',
            'avatar' => 'Avatar',
            'photo_header' => 'Photo Header',
            'auth_key' => 'Auth Key',
        ];
    }
    

    public function init()
    {
        parent::init();
        \Yii::$app->user->enableSession = false;
    }

    public static function findIdentityByBasicAuth($username, $password)
    {
        $user = self::findByUsername($username);
        return $user && $user->validatePassword($password) ? $user : null;
    }

    //identity
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
        // return $this->password === $password;
    }

    public static function findByUsername($login)
    {
        return self::findOne(['login' => $login]);
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token]);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public function validateAuthKey($auth_key)
    {
        return $this->auth_key === $auth_key;
    }

    /**
     * Gets query for [[BlockUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockUsers()
    {
        return $this->hasMany(BlockUser::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[BlockUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAllergens()
    {
        return $this->hasMany(UserAllergen::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Checklists]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChecklists()
    {
        return $this->hasMany(Checklist::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionReactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionReactions()
    {
        return $this->hasMany(CollectionReaction::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionSubscribes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionSubscribes()
    {
        return $this->hasMany(CollectionSubscribe::class, ['follower_id' => 'id']);
    }

    /**
     * Gets query for [[Collections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollections()
    {
        return $this->hasMany(Collection::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[PreferenceMarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreferenceMarks()
    {
        return $this->hasMany(PreferenceMark::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[PreferenceProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreferenceProducts()
    {
        return $this->hasMany(PreferenceProduct::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Private]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrivate()
    {
        return $this->hasOne(PrivateType::class, ['id' => 'private_id']);
    }

    /**
     * Gets query for [[RecipeCalendars]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeCalendars()
    {
        return $this->hasMany(RecipeCalendar::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeNotes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeNotes()
    {
        return $this->hasMany(RecipeNote::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeReactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeReactions()
    {
        return $this->hasMany(RecipeReaction::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Recipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipes()
    {
        return $this->hasMany(Recipe::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Role]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRole()
    {
        return $this->hasOne(Role::class, ['id' => 'role_id']);
    }

    /**
     * Gets query for [[UserBlacklists]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserBlacklists()
    {
        return $this->hasMany(UserBlacklist::class, ['block_user_id' => 'id']);
    }

    /**
     * Gets query for [[UserBlacklists0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserBlacklists0()
    {
        return $this->hasMany(UserBlacklist::class, ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserSubscribes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSubscribes()
    {
        return $this->hasMany(UserSubscribe::class, ['follower_id' => 'id']);
    }

    /**
     * Gets query for [[UserSubscribes0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserSubscribes0()
    {
        return $this->hasMany(UserSubscribe::class, ['subscriber_id' => 'id']);
    }
}
