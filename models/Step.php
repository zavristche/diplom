<?php

namespace app\models;

use Yii;
use app\models\Recipe;
use app\models\RecipeNote;

/**
 * This is the model class for table "step".
 *
 * @property int $id
 * @property int $recipe_id
 * @property int $number
 * @property string $title
 * @property string $photo
 * @property string $description
 *
 * @property Recipe $recipe
 * @property RecipeNote[] $recipeNotes
 */
class Step extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'step';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recipe_id', 'number', 'title', 'photo', 'description'], 'required'],
            [['recipe_id', 'number'], 'integer'],
            [['description'], 'string'],
            [['title', 'photo'], 'string', 'max' => 255],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Recipe::class, 'targetAttribute' => ['recipe_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recipe_id' => 'Recipe ID',
            'number' => 'Number',
            'title' => 'Title',
            'photo' => 'Photo',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[Recipe]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipe()
    {
        return $this->hasOne(Recipe::class, ['id' => 'recipe_id']);
    }

    /**
     * Gets query for [[RecipeNotes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeNotes()
    {
        return $this->hasMany(RecipeNote::class, ['step_id' => 'id']);
    }
}
