<?php

namespace app\models;

use Yii;
use app\models\Allergen;
use app\models\User;

/**
 * This is the model class for table "user_allergen".
 *
 * @property int $id
 * @property int $user_id
 * @property int $allergen_id
 *
 * @property Allergen $allergen
 * @property User $user
 */
class UserAllergen extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_allergen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'allergen_id'], 'required'],
            [['user_id', 'allergen_id'], 'integer'],
            [['allergen_id'], 'exist', 'skipOnError' => true, 'targetClass' => Allergen::class, 'targetAttribute' => ['allergen_id' => 'id']],
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
            'allergen_id' => 'Allergen ID',
        ];
    }

    /**
     * Gets query for [[Allergen]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAllergen()
    {
        return $this->hasOne(Allergen::class, ['id' => 'allergen_id']);
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
