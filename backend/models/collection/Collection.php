<?php

namespace app\models\collection;

use Yii;
use app\models\collection\CollectionMark;
use app\models\collection\CollectionProduct;
use app\models\collection\CollectionReaction;
use app\models\collection\CollectionRecipe;
use app\models\collection\CollectionSubscribe;
use app\models\mark\Mark;
use app\models\PrivateType;
use app\models\product\Product;
use app\models\StatusContent;
use app\models\user\User;
use yii\helpers\Url;
use yii\web\Link;
use yii\web\Linkable;

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
 * @property string $created_at
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
class Collection extends \yii\db\ActiveRecord implements Linkable
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
            [['created_at'], 'safe'],
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
            'id' => 'Номер коллекции',
            'user_id' => 'Автор',
            'private_id' => 'Кому доступна коллекция?',
            'status_id' => 'Статус',
            'title' => 'Заголовок',
            'photo' => 'Фото',
            'description' => 'Описание',
            'saved' => 'Сохранения',
            'likes' => 'Лайки',
        ];
    }

    public function getLinks()
    {
        $url = Yii::$app->params['frontendUrl'];
        return [
            Link::REL_SELF => [
                'method' => 'GET',
                'href' => $url . '/collection/' . $this->id,
            ],
            'edit' => [
                'method' => 'PATCH',
                'href' => $url . '/collection/' . $this->id,
            ],
            'delete' => [
                'method' => 'DELETE',
                'href' => $url . '/collection/' . $this->id,
            ],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['user'] = function() {
            $userData = $this->user;
            unset(
                $userData['auth_key'],
                $userData['password'],
            );
            return $userData;
        };

        $fields['status'] = fn() => $this->status;
        $fields['private'] = fn() => $this->private;
        $fields['likes'] = fn() => count($this->getCollectionReactions()->all());
        $fields['subs'] = fn() => count($this->getCollectionSubscribes()->all());

        $fields['marks'] = fn() => $this->getMarks()->select([])->asArray()->all();
        $fields['products'] = fn() => $this->getProducts()->select([])->asArray()->all();

        return $fields;
    }

    public function afterDelete()
    {
        parent::afterDelete();

        CollectionMark::deleteAll(['collection_id' => $this->id]);
        CollectionProduct::deleteAll(['collection_id' => $this->id]);
        CollectionReaction::deleteAll(['collection_id' => $this->id]);
        CollectionRecipe::deleteAll(['collection_id' => $this->id]);
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

    public function getMarks()
    {
        return $this->hasMany(Mark::class, ['id' => 'mark_id'])->via('collectionMarks');
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

    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('collectionProducts');
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
