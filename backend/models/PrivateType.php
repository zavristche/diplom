<?php

namespace app\models;

use Yii;
use app\models\collection\Collection;
use app\models\recipe\Recipe;
use app\models\user\User;

/**
 * This is the model class for table "private_type".
 *
 * @property int $id
 * @property string $title
 *
 * @property Collection[] $collections
 * @property Recipe[] $recipes
 * @property User[] $users
 */
class PrivateType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'private_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
        ];
    }

    public static function getAll()
    {
        return self::find()->select('title')->indexBy('id')->column();
    }

    public static function getOne($title)
    {
        return self::findOne(['title' => $title])->id;
    }

    /**
     * Gets query for [[Collections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollections()
    {
        return $this->hasMany(Collection::class, ['private_id' => 'id']);
    }

    /**
     * Gets query for [[Recipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipes()
    {
        return $this->hasMany(Recipe::class, ['private_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['private_id' => 'id']);
    }
}
