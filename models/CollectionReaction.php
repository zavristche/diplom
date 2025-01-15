<?php

namespace app\models;

use Yii;
use app\models\Collection;
use app\models\ReactionType;
use app\models\User;

/**
 * This is the model class for table "collection_reaction".
 *
 * @property int $id
 * @property int $user_id
 * @property int $collection_id
 * @property int $type_id
 *
 * @property Collection $collection
 * @property ReactionType $type
 * @property User $user
 */
class CollectionReaction extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collection_reaction';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'collection_id', 'type_id'], 'required'],
            [['user_id', 'collection_id', 'type_id'], 'integer'],
            [['collection_id'], 'exist', 'skipOnError' => true, 'targetClass' => Collection::class, 'targetAttribute' => ['collection_id' => 'id']],
            [['type_id'], 'exist', 'skipOnError' => true, 'targetClass' => ReactionType::class, 'targetAttribute' => ['type_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'collection_id' => 'Collection ID',
            'type_id' => 'Type ID',
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
     * Gets query for [[Type]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getType()
    {
        return $this->hasOne(ReactionType::class, ['id' => 'type_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
