<?php

namespace app\models\collection;

use Yii;
use app\models\collection\Collection;
use app\models\mark\Mark;

/**
 * This is the model class for table "collection_mark".
 *
 * @property int $id
 * @property int $collection_id
 * @property int $mark_id
 *
 * @property Collection $collection
 * @property Mark $mark
 */
class CollectionMark extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collection_mark';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['collection_id', 'mark_id'], 'required'],
            [['collection_id', 'mark_id'], 'integer'],
            [['collection_id'], 'exist', 'skipOnError' => true, 'targetClass' => Collection::class, 'targetAttribute' => ['collection_id' => 'id']],
            [['mark_id'], 'exist', 'skipOnError' => true, 'targetClass' => Mark::class, 'targetAttribute' => ['mark_id' => 'id']],
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
            'mark_id' => 'Mark ID',
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
     * Gets query for [[Mark]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getMark()
    {
        return $this->hasOne(Mark::class, ['id' => 'mark_id']);
    }
}
