<?php

namespace app\models;

use Yii;
use app\models\Recipe;
use app\models\User;

/**
 * This is the model class for table "recipe_calendar".
 *
 * @property int $id
 * @property int $user_id
 * @property int $recipe_id
 * @property string $start_date
 * @property string $end_date
 *
 * @property Recipe $recipe
 * @property User $user
 */
class RecipeCalendar extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_calendar';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'recipe_id', 'start_date', 'end_date'], 'required'],
            [['user_id', 'recipe_id'], 'integer'],
            [['start_date', 'end_date'], 'safe'],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Recipe::class, 'targetAttribute' => ['recipe_id' => 'id']],
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
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
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
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
