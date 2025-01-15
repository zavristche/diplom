<?php

namespace app\models;

use Yii;
use app\models\CollectionMark;
use app\models\CollectionProduct;
use app\models\CollectionReaction;
use app\models\CollectionRecipe;
use app\models\CollectionSubscribe;
use app\models\PrivateType;
use app\models\StatusContent;
use app\models\User;

/**
 * This is the model class for table "collection".
 *
 * @property int $id
 * @property int $user_id
 * @property int $private_id
 * @property int $status_id
 * @property string $title
 * @property string|null $photo
 * @property string $description
 * @property int|null $saved
 * @property int|null $likes
 *
 * @property CollectionMark[] $collectionMarks
 * @property CollectionProduct[] $collectionProducts
 * @property CollectionReaction[] $collectionReactions
 * @property CollectionRecipe[] $collectionRecipes
 * @property CollectionSubscribe[] $collectionSubscribes
 * @property PrivateType $private
 * @property StatusContent $status
 * @property User $user
 */
class Collection extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'private_id', 'status_id', 'title', 'description'], 'required'],
            [['user_id', 'private_id', 'status_id', 'saved', 'likes'], 'integer'],
            [['description'], 'string'],
            [['title', 'photo'], 'string', 'max' => 255],
            [['private_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrivateType::class, 'targetAttribute' => ['private_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => StatusContent::class, 'targetAttribute' => ['status_id' => 'id']],
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
            'private_id' => 'Private ID',
            'status_id' => 'Status ID',
            'title' => 'Title',
            'photo' => 'Photo',
            'description' => 'Description',
            'saved' => 'Saved',
            'likes' => 'Likes',
        ];
    }

    /**
     * Gets query for [[CollectionMarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionMarks()
    {
        return $this->hasMany(CollectionMark::class, ['collection_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionProducts()
    {
        return $this->hasMany(CollectionProduct::class, ['collection_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionReactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionReactions()
    {
        return $this->hasMany(CollectionReaction::class, ['collection_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionRecipes()
    {
        return $this->hasMany(CollectionRecipe::class, ['collection_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionSubscribes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionSubscribes()
    {
        return $this->hasMany(CollectionSubscribe::class, ['collection_id' => 'id']);
    }

    /**
     * Gets query for [[Private]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrivate()
    {
        return $this->hasOne(PrivateType::class, ['id' => 'private_id']);
    }

    /**
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getStatus()
    {
        return $this->hasOne(StatusContent::class, ['id' => 'status_id']);
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
