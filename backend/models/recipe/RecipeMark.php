<?php

namespace app\models\recipe;

use Yii;
use app\models\mark\Mark;
use app\models\recipe\Recipe;

/**
 * This is the model class for table "recipe_mark".
 *
 * @property int $id
 * @property int $recipe_id
 * @property int $mark_id
 *
 * @property Mark $mark
 * @property Recipe $recipe
 */
class RecipeMark extends \yii\db\ActiveRecord
{
    const SCENARIO_CREATE = 'create';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_mark';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mark_id'], 'required'],
            [['recipe_id', 'mark_id'], 'integer'],
            [['mark_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mark::class, 'targetAttribute' => ['mark_id' => 'id']],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'skipOnEmpty' => true, 'targetClass' => Recipe::class, 'targetAttribute' => ['recipe_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['mark_id'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recipe_id' => 'Recipe ID',
            'mark_id' => 'Mark ID',
        ];
    }

    /**
     * Gets query for [[Mark]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMark()
    {
        return $this->hasOne(Mark::class, ['id' => 'mark_id']);
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