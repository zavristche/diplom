<?php

namespace app\models\recipe;

use Yii;
use app\models\Step;
use app\models\User;

/**
 * This is the model class for table "recipe_note".
 *
 * @property int $id
 * @property int $step_id
 * @property int $user_id
 * @property string $selected_text
 * @property string|null $text
 *
 * @property Step $step
 * @property User $user
 */
class RecipeNote extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_note';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['step_id', 'user_id', 'selected_text'], 'required'],
            [['step_id', 'user_id'], 'integer'],
            [['selected_text', 'text'], 'string'],
            [['step_id'], 'exist', 'skipOnError' => true, 'targetClass' => Step::class, 'targetAttribute' => ['step_id' => 'id']],
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
            'step_id' => 'Step ID',
            'user_id' => 'User ID',
            'selected_text' => 'Selected Text',
            'text' => 'Text',
        ];
    }

    /**
     * Gets query for [[Step]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStep()
    {
        return $this->hasOne(Step::class, ['id' => 'step_id']);
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
