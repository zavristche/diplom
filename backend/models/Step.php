<?php

namespace app\models;

use Yii;
use app\models\recipe\Recipe;
use app\models\recipe\RecipeNote;

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
    public $imageFile;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    
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
            [['number', 'title', 'description'], 'required'],
            [['recipe_id', 'number'], 'integer'],
            [['recipe_id'], 'safe'],
            [['description'], 'string'],
            [['title', 'photo'], 'string', 'max' => 255],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Recipe::class, 'targetAttribute' => ['recipe_id' => 'id']],

            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg'], // Убираем skipOnEmpty => false
            [['imageFile'], 'validateImageFile', 'on' => [self::SCENARIO_CREATE]], // Кастомная валидация для новых шагов
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'on' => [self::SCENARIO_UPDATE]],
            ['photo', 'safe'],
        ];
    }

    /**
     * Кастомная валидация для imageFile
     */
    public function validateImageFile($attribute, $params)
    {
        if ($this->isNewRecord && !$this->$attribute) {
            $this->addError($attribute, 'Фото шага обязательно.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = ['recipe_id', 'number', 'title', 'description', 'photo', 'imageFile'];
        $scenarios[self::SCENARIO_UPDATE] = ['recipe_id', 'number', 'title', 'description', 'photo', 'imageFile'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recipe_id' => 'Рецепт',
            'number' => 'Порядковый номер',
            'title' => 'Заголовок',
            'photo' => 'Фото',
            'step_photo' => 'Загрузка фото',
            'description' => 'Описание',
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