<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "complexity".
 *
 * @property int $id
 * @property string $description
 * @property int $value
 *
 * @property Recipe[] $recipes
 */
class Complexity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'complexity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description', 'value'], 'required'],
            [['value'], 'integer'],
            [['description'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'description' => 'Description',
            'value' => 'Value',
        ];
    }

    /**
     * Gets query for [[Recipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipes()
    {
        return $this->hasMany(Recipe::class, ['complexity_id' => 'id']);
    }
}
