<?php

namespace app\models;

use Yii;
use app\models\ProductAllergen;
use app\models\UserAllergen;

/**
 * This is the model class for table "allergen".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 *
 * @property ProductAllergen[] $productAllergens
 */
class Allergen extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'allergen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'description'], 'required'],
            [['description'], 'string'],
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
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[ProductAllergens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductAllergens()
    {
        return $this->hasMany(ProductAllergen::class, ['allergen_id' => 'id']);
    }

    /**
     * Gets query for [[BlockUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAllergens()
    {
        return $this->hasMany(UserAllergen::class, ['user_id' => 'id']);
    }
}
