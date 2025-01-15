<?php

namespace app\models;

use Yii;
use app\models\Collection;
use app\models\Product;

/**
 * This is the model class for table "collection_product".
 *
 * @property int $id
 * @property int $collection_id
 * @property int $product_id
 *
 * @property Collection $collection
 * @property Product $product
 */
class CollectionProduct extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collection_product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['collection_id', 'product_id'], 'required'],
            [['collection_id', 'product_id'], 'integer'],
            [['collection_id'], 'exist', 'skipOnError' => true, 'targetClass' => Collection::class, 'targetAttribute' => ['collection_id' => 'id']],
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
            'collection_id' => 'Collection ID',
            'product_id' => 'Product ID',
        ];
    }

    /**
     * Gets query for [[Collection]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollection()
    {
        return $this->hasOne(Collection::class, ['id' => 'collection_id']);
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
