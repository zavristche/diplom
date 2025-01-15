<?php

namespace app\models;

use Yii;
use app\models\Collection;
use app\models\Recipe;

/**
 * This is the model class for table "collection_recipe".
 *
 * @property int $id
 * @property int $recipe_id
 * @property int $collection_id
 * @property string $added_at
 *
 * @property Collection $collection
 * @property Recipe $recipe
 */
class CollectionRecipe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collection_recipe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recipe_id', 'collection_id'], 'required'],
            [['recipe_id', 'collection_id'], 'integer'],
            [['added_at'], 'safe'],
            [['collection_id'], 'exist', 'skipOnError' => true, 'targetClass' => Collection::class, 'targetAttribute' => ['collection_id' => 'id']],
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
            'collection_id' => 'Collection ID',
            'added_at' => 'Added At',
        ];
    }

    /**
     * Gets query for [[Collection]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollection()
    {
        return $this->hasOne(Collection::class, ['id' => 'collection_id']);
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
}
