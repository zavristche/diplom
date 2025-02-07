<?php

namespace app\models;

use Yii;
use app\models\collection\CollectionReaction;
use app\models\recipe\RecipeReaction;

/**
 * This is the model class for table "reaction_type".
 *
 * @property int $id
 * @property string $title
 *
 * @property CollectionReaction[] $collectionReactions
 * @property RecipeReaction[] $recipeReactions
 */
class ReactionType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'reaction_type';
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

    /**
     * Gets query for [[CollectionReactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionReactions()
    {
        return $this->hasMany(CollectionReaction::class, ['type_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeReactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeReactions()
    {
        return $this->hasMany(RecipeReaction::class, ['type_id' => 'id']);
    }
}
