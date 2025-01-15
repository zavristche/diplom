<?php

namespace app\models;

use Yii;
use app\models\Collection;
use app\models\Recipe;

/**
 * This is the model class for table "status_content".
 *
 * @property int $id
 * @property string $title
 *
 * @property Collection[] $collections
 * @property Recipe[] $recipes
 */
class StatusContent extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'status_content';
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
     * Gets query for [[Collections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollections()
    {
        return $this->hasMany(Collection::class, ['status_id' => 'id']);
    }

    /**
     * Gets query for [[Recipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipes()
    {
        return $this->hasMany(Recipe::class, ['status_id' => 'id']);
    }
}
