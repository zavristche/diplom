<?php

namespace app\models;

use Yii;
use app\models\BlockRecipe;
use app\models\CollectionRecipe;
use app\models\Comment;
use app\models\PrivateType;
use app\models\RecipeCalendar;
use app\models\RecipeMark;
use app\models\RecipeProduct;
use app\models\RecipeReaction;
use app\models\StatusContent;
use app\models\Step;
use app\models\User;

/**
 * This is the model class for table "recipe".
 *
 * @property int $id
 * @property int $user_id
 * @property int $status_id
 * @property int $private_id
 * @property string $created_at
 * @property string $title
 * @property string $photo
 * @property string $description
 * @property int|null $saved
 * @property int|null $likes
 *
 * @property BlockRecipe[] $blockRecipes
 * @property CollectionRecipe[] $collectionRecipes
 * @property Comment[] $comments
 * @property PrivateType $private
 * @property RecipeCalendar[] $recipeCalendars
 * @property RecipeMark[] $recipeMarks
 * @property RecipeProduct[] $recipeProducts
 * @property RecipeReaction[] $recipeReactions
 * @property StatusContent $status
 * @property Step[] $steps
 * @property User $user
 */
class Recipe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'status_id', 'private_id', 'title', 'photo', 'description'], 'required'],
            [['user_id', 'status_id', 'private_id', 'saved', 'likes'], 'integer'],
            [['created_at'], 'safe'],
            [['description'], 'string'],
            [['title', 'photo'], 'string', 'max' => 255],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => StatusContent::class, 'targetAttribute' => ['status_id' => 'id']],
            [['private_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrivateType::class, 'targetAttribute' => ['private_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'status_id' => 'Status ID',
            'private_id' => 'Private ID',
            'created_at' => 'Created At',
            'title' => 'Title',
            'photo' => 'Photo',
            'description' => 'Description',
            'saved' => 'Saved',
            'likes' => 'Likes',
        ];
    }

    /**
     * Gets query for [[BlockRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockRecipes()
    {
        return $this->hasMany(BlockRecipe::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionRecipes()
    {
        return $this->hasMany(CollectionRecipe::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['recipe_id' => 'id']);
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
        return $this->hasMany(RecipeCalendar::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeMarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeMarks()
    {
        return $this->hasMany(RecipeMark::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeProducts()
    {
        return $this->hasMany(RecipeProduct::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeReactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeReactions()
    {
        return $this->hasMany(RecipeReaction::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(StatusContent::class, ['id' => 'status_id']);
    }

    /**
     * Gets query for [[Steps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSteps()
    {
        return $this->hasMany(Step::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
