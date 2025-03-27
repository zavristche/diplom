<?php

namespace app\models\recipe;

use Yii;
use app\models\checklist\ChecklistProduct;
use app\models\Measure;
use app\models\product\Product;
use app\models\recipe\Recipe;

/**
 * This is the model class for table "recipe_product".
 *
 * @property int $id
 * @property int $recipe_id
 * @property int $product_id
 * @property int $measure_id
 * @property int|null $count
 *
 * @property ChecklistProduct[] $checklistProducts
 * @property Measure $measure
 * @property Product $product
 * @property Recipe $recipe
 */
class RecipeProduct extends \yii\db\ActiveRecord
{
    const SCENARIO_NO_TASTE = 'no_taste';
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'measure_id'], 'required'],
            [['recipe_id'], 'safe'],
            [['recipe_id', 'product_id', 'measure_id', 'count'], 'integer'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['recipe_id'], 'exist', 'skipOnError' => true, 'targetClass' => Recipe::class, 'targetAttribute' => ['recipe_id' => 'id']],
            [['measure_id'], 'exist', 'skipOnError' => true, 'targetClass' => Measure::class, 'targetAttribute' => ['measure_id' => 'id']],
            ['count', 'required', 'on' => self::SCENARIO_NO_TASTE],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'recipe_id' => 'Рецепт',
            'product_id' => 'Продукт',
            'measure_id' => 'Мера счисления',
            'count' => 'Количество',
        ];
    }

    public function fields()
    {
        $fields = parent::fields();
        $fields['product'] = fn() => $this->product;
        $fields['measure'] = fn() => $this->measure;

        return $fields;
    }

    /**
     * Gets query for [[ChecklistProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getChecklistProducts()
    {
        return $this->hasMany(ChecklistProduct::class, ['resipe_product_id' => 'id']);
    }

    /**
     * Gets query for [[Measure]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMeasure()
    {
        return $this->hasOne(Measure::class, ['id' => 'measure_id']);
    }

    /**
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
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

    
    public function getIsToTaste()
    {
        return $this->measure_id == Measure::getOne('по вкусу');
    }
}
