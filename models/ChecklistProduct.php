<?php

namespace app\models;

use app\models\Product;
use app\models\RecipeProduct;
use Yii;

/**
 * This is the model class for table "checklist_product".
 *
 * @property int $id
 * @property int|null $resipe_product_id
 * @property int|null $product_id
 * @property int $list_id
 *
 * @property Product $product
 * @property RecipeProduct $resipeProduct
 */
class ChecklistProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'checklist_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['resipe_product_id', 'product_id', 'list_id'], 'integer'],
            [['list_id'], 'required'],
            [['product_id'], 'exist', 'skipOnError' => true, 'targetClass' => Product::class, 'targetAttribute' => ['product_id' => 'id']],
            [['resipe_product_id'], 'exist', 'skipOnError' => true, 'targetClass' => RecipeProduct::class, 'targetAttribute' => ['resipe_product_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'resipe_product_id' => 'Resipe Product ID',
            'product_id' => 'Product ID',
            'list_id' => 'List ID',
        ];
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
     * Gets query for [[ResipeProduct]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getResipeProduct()
    {
        return $this->hasOne(RecipeProduct::class, ['id' => 'resipe_product_id']);
    }
}
