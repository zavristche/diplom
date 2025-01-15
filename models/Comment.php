<?php

namespace app\models;

use Yii;
use app\models\Recipe;
use app\models\User;

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
            [['recipe_id', 'user_id', 'text', 'photo'], 'required'],
            [['recipe_id', 'user_id', 'answer_id'], 'integer'],
            [['created_at'], 'safe'],
            [['text'], 'string'],
            [['photo'], 'string', 'max' => 255],
            [['answer_id'], 'exist', 'skipOnError' => true, 'targetClass' => self::class, 'targetAttribute' => ['answer_id' => 'id']],
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
            'recipe_id' => 'Recipe ID',
            'user_id' => 'User ID',
            'answer_id' => 'Answer ID',
            'created_at' => 'Created At',
            'text' => 'Text',
            'photo' => 'Photo',
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
