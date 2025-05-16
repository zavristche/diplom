<?php

namespace app\models;

use Yii;
use app\models\recipe\Recipe;
use app\models\user\User;

/**
 * This is the model class for table "comment".
 *
 * @property int $id
 * @property int $recipe_id
 * @property int $user_id
 * @property int|null $answer_id
 * @property string $created_at
 * @property string $text
 * @property string $photo
 *
 * @property Comment $answer
 * @property Comment[] $comments
 * @property Recipe $recipe
 * @property User $user
 */
class Comment extends \yii\db\ActiveRecord
{
    public $imageFile;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'comment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['recipe_id', 'user_id', 'text'], 'required'],
            [['recipe_id', 'user_id', 'answer_id'], 'integer'],
            [['created_at'], 'safe'],
            [['text'], 'string'],
            [['photo'], 'string', 'max' => 255],
            [['answer_id'], 'exist', 'skipOnError' => true, 'targetClass' => self::class, 'targetAttribute' => ['answer_id' => 'id']],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Recipe::class, 'targetAttribute' => ['recipe_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg'],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        
        $fields['user'] = function() {
            return [
                'id' => $this->user->id ?? null,
                'login' => $this->user->login ?? null,
                'avatar' => $this->user->avatar ?? null
            ];
        };
        
        $fields['answer'] = fn() => $this->answer;
        $fields['created_at'] = fn() => Yii::$app->formatter->asDate($this->created_at, 'php:d.m.Y') . ' в ' . Yii::$app->formatter->asTime($this->created_at, 'H:i');
    
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recipe_id' => 'Рецепт',
            'user_id' => 'Пользователь',
            'answer_id' => 'Ответ на комментарий',
            'created_at' => 'Дата создания',
            'text' => 'Текст',
            'photo' => 'Фото',
            'imageFile' => 'Фото',
        ];
    }

    /**
     * Gets query for [[Answer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAnswer()
    {
        return $this->hasOne(Comment::class, ['id' => 'answer_id']);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['answer_id' => 'id']);
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
