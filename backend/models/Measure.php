<?php

namespace app\models;

use Yii;
use app\models\recipe\RecipeProduct;

/**
 * This is the model class for table "measure".
 *
 * @property int $id
 * @property string $title
 *
 * @property RecipeProduct[] $recipeProducts
 */
class Measure extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'measure';
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

    public static function getAll()
    {
        return self::find()->select('title')->indexBy('id')->column();
    }

    /**
     * Gets query for [[RecipeProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeProducts()
    {
        return $this->hasMany(RecipeProduct::class, ['measure_id' => 'id']);
    }

    public static function getOne($title)
    {
        return self::find()->where(['title' => $title])->one()->id;
    }
}
