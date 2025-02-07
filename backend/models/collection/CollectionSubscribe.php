<?php

namespace app\models\collection;

use Yii;
use app\models\collection\Collection;
use app\models\user\User;

/**
 * This is the model class for table "collection_subscribe".
 *
 * @property int $id
 * @property int $follower_id
 * @property int $collection_id
 * @property string $subscribe_at
 *
 * @property Collection $collection
 * @property User $follower
 */
class CollectionSubscribe extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collection_subscribe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['follower_id', 'collection_id'], 'required'],
            [['follower_id', 'collection_id'], 'integer'],
            [['subscribe_at'], 'safe'],
            [['follower_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['follower_id' => 'id']],
            [['collection_id'], 'exist', 'skipOnError' => true, 'targetClass' => Collection::class, 'targetAttribute' => ['collection_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'follower_id' => 'Follower ID',
            'collection_id' => 'Collection ID',
            'subscribe_at' => 'Subscribe At',
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
     * Gets query for [[Follower]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFollower()
    {
        return $this->hasOne(User::class, ['id' => 'follower_id']);
    }
}
