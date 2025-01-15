<?php

namespace app\models;

use Yii;
use app\models\Recipe;
use app\models\ReactionType;
use app\models\User;

/**
 * This is the model class for table "recipe_reaction".
 *
 * @property int $id
 * @property int $user_id
 * @property int $recipe_id
 * @property int $type_id
 *
 * @property Recipe $recipe
 * @property ReactionType $type
 * @property User $user
 */
class RecipeReaction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_reaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'recipe_id', 'type_id'], 'required'],
            [['user_id', 'recipe_id', 'type_id'], 'integer'],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Recipe::class, 'targetAttribute' => ['recipe_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ReactionType::class, 'targetAttribute' => ['type_id' => 'id']],
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
            'recipe_id' => 'Recipe ID',
            'type_id' => 'Type ID',
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
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ReactionType::class, ['id' => 'type_id']);
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
