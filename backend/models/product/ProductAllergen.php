<?php

namespace app\models\product;

use Yii;
use app\models\Allergen;
use app\models\product\Product;

/**
 * This is the model class for table "product_allergen".
 *
 * @property int $id
 * @property int $product_id
 * @property int $allergen_id
 *
 * @property Allergen $allergen
 * @property Product $product
 */
class ProductAllergen extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product_allergen';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['product_id', 'allergen_id'], 'required'],
            [['product_id', 'allergen_id'], 'integer'],
            [['allergen_id'], 'exist', 'skipOnError' => true, 'targetClass' => Allergen::class, 'targetAttribute' => ['allergen_id' => 'id']],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'product_id' => 'Product ID',
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
     * Gets query for [[Product]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProduct()
    {
        return $this->hasOne(Product::class, ['id' => 'product_id']);
    }
}
