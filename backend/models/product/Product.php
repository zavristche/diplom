<?php

namespace app\models\product;

use Yii;
use app\models\checklist\ChecklistProduct;
use app\models\collection\CollectionProduct;
use app\models\preference\PreferenceProduct;
use app\models\product\ProductAllergen;
use app\models\recipe\RecipeProduct;
use app\models\product\ProductType;

/**
 * This is the model class for table "product".
 *
 * @property int $id
 * @property int $type_id
 * @property string $title
 * @property string $description
 *
 * @property ChecklistProduct[] $checklistProducts
 * @property CollectionProduct[] $collectionProducts
 * @property PreferenceProduct[] $preferenceProducts
 * @property ProductAllergen[] $productAllergens
 * @property RecipeProduct[] $recipeProducts
 * @property ProductType $type
 */
class Product extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type_id', 'title', 'description'], 'required'],
            [['type_id'], 'integer'],
            [['description'], 'string'],
            [['title'], 'string', 'max' => 255],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ProductType::class, 'targetAttribute' => ['type_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type_id' => 'Type ID',
            'title' => 'Title',
            'description' => 'Description',
        ];
    }

    /**
     * Gets query for [[ChecklistProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChecklistProducts()
    {
        return $this->hasMany(ChecklistProduct::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionProducts()
    {
        return $this->hasMany(CollectionProduct::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[PreferenceProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPreferenceProducts()
    {
        return $this->hasMany(PreferenceProduct::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[ProductAllergens]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProductAllergens()
    {
        return $this->hasMany(ProductAllergen::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeProducts()
    {
        return $this->hasMany(RecipeProduct::class, ['product_id' => 'id']);
    }

    /**
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ProductType::class, ['id' => 'type_id']);
    }
}
