<?php

namespace app\models\recipe;

use Yii;
use yii\web\Link;
use yii\web\Linkable;

use app\models\block\BlockRecipe;
use app\models\collection\CollectionRecipe;
use app\models\Comment;
use app\models\Complexity;
use app\models\mark\Mark;
use app\models\PrivateType;
use app\models\product\Product;
use app\models\recipe\RecipeCalendar;
use app\models\recipe\RecipeMark;
use app\models\recipe\RecipeProduct;
use app\models\recipe\RecipeReaction;
use app\models\StatusContent;
use app\models\Step;
use app\models\user\User;

/**
 * This is the model class for table "recipe".
 *
 * @property int $id
 * @property int $user_id
 * @property int $status_id
 * @property int $private_id
 * @property int $complexity_id
 * @property string $created_at
 * @property string $admin_comment
 * @property string $title
 * @property string $photo
 * @property string $time
 * @property string $description
 * @property int|null $saved
 * @property int|null $likes
 *
 * @property BlockRecipe[] $blockRecipes
 * @property Complexity[] $complexit
 * @property CollectionRecipe[] $collectionRecipes
 * @property Comment[] $comments
 * @property PrivateType $private
 * @property RecipeCalendar[] $recipeCalendars
 * @property RecipeMark[] $recipeMarks
 * @property RecipeProduct[] $recipeProducts
 * @property RecipeReaction[] $recipeReactions
 * @property StatusContent $status
 * @property Step[] $steps
 * @property User $user
 */
class Recipe extends \yii\db\ActiveRecord implements Linkable
{
    public $imageFile;
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_CANCEL = 'update';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'recipe';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'status_id', 'private_id', 'title', 'description', 'time', 'complexity_id'], 'required'],
            [['user_id', 'status_id', 'private_id', 'saved', 'likes'], 'integer'],
            [['created_at'], 'safe'],
            [['description'], 'string'],
            [['title', 'photo', 'time'], 'string', 'max' => 255],

            [['status_id'], 'exist', 'skipOnError' => true, 'targetClass' => StatusContent::class, 'targetAttribute' => ['status_id' => 'id']],
            [['private_id'], 'exist', 'skipOnError' => true, 'targetClass' => PrivateType::class, 'targetAttribute' => ['private_id' => 'id']],
            [['complexity_id'], 'exist', 'skipOnError' => true, 'targetClass' => Complexity::class, 'targetAttribute' => ['complexity_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],

            [['imageFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'png, jpg, jpeg', 'on' => [self::SCENARIO_CREATE]],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'on' => [self::SCENARIO_UPDATE]],
            ['photo', 'safe'],

            ['admin_comment', 'required', 'on' => self::SCENARIO_CANCEL],
        ];
    }

    public function attributes()
    {
        return array_merge(parent::attributes(), ['recipe_photo']);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Номер рецепта',
            'user_id' => 'Автор',
            'status_id' => 'Статус',
            'complexity_id' => 'Сложность',
            'private_id' => 'Кому доступен рецепт?',
            'created_at' => 'Дата создания',
            'title' => 'Название',
            'photo' => 'Фото',
            'recipe_photo' => 'Загрузка фото',
            'description' => 'Описание',
            'saved' => 'Сохранения',
            'likes' => 'Оценки',
            'time' => 'Время приготовления',
            'admin_comment' => 'Причина отклонения публикации',
        ];
    }

    public function safeAttributes()
    {
        return array_merge(parent::safeAttributes(), ['recipe_photo']);
    }

    public function getLinks()
    {
        $url = Yii::$app->params['frontendUrl'];
        return [
            Link::REL_SELF => [
                'method' => 'GET',
                'href' => $url . '/recipe/' . $this->id,
            ],
            'edit' => [
                'method' => 'PATCH',
                'href' => $url . '/recipe/' . $this->id,
            ],
            'delete' => [
                'method' => 'DELETE',
                'href' => $url . '/recipe/' . $this->id,
            ],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['status'] = fn() => $this->status;
        $fields['complexity'] = fn() => $this->complexity;
        $fields['private'] = fn() => $this->private;
        $fields['comments'] = fn() => $this->comments;
            
        $fields['likes'] = fn() => count($this->getRecipeReactions()->all());
        $fields['saved'] = fn() => count($this->getCollectionRecipes()->all());

        $fields['marks'] = fn() => $this->getMarks()->select([])->asArray()->all();
        $fields['products'] = fn() => $this->getProducts()->select([])->asArray()->all();
        $fields['steps'] = fn() => $this->getSteps()->asArray()->all();

        $fields['collections'] = function () {
            if (!Yii::$app->user->isGuest) {
                $userId = Yii::$app->user->id;

                return $this->getCollectionRecipes()
                    ->joinWith('collection')
                    ->andWhere(['collection.user_id' => $userId])
                    ->andWhere(['recipe_id' => $this->id])
                    ->all();
            }
            return [];
        };
        $fields['calendar_recipe'] = function () {
            if (!Yii::$app->user->isGuest) {
                return $this->getRecipeCalendars()->andWhere(['user_id' => Yii::$app->user->identity->id])->all();
            }
            return [];
        };

        return $fields;
    }

    public static function getIsAuthor($recipe_id)
    {
        return self::findOne(['id' => $recipe_id])->user_id == Yii::$app->user->id;
    }

    /**
     * Gets query for [[BlockRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockRecipes()
    {
        return $this->hasMany(BlockRecipe::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[CollectionRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCollectionRecipes()
    {
        return $this->hasMany(CollectionRecipe::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[Comments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::class, ['recipe_id' => 'id']);
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
     * Gets query for [[RecipeCalendars]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeCalendars()
    {
        return $this->hasMany(RecipeCalendar::class, ['recipe_id' => 'id']);
    }

    /**
     * Gets query for [[RecipeMarks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeMarks()
    {
        return $this->hasMany(RecipeMark::class, ['recipe_id' => 'id']);
    }

    public function getMarks()
    {
        return $this->hasMany(Mark::class, ['id' => 'mark_id'])->via('recipeMarks');
    }

    /**
     * Gets query for [[RecipeProducts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeProducts()
    {
        return $this->hasMany(RecipeProduct::class, ['recipe_id' => 'id']);
    }

    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])->via('recipeProducts');
    }

    /**
     * Gets query for [[RecipeReactions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getRecipeReactions()
    {
        return $this->hasMany(RecipeReaction::class, ['recipe_id' => 'id']);
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
     * Gets query for [[Status]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComplexity()
    {
        return $this->hasOne(Complexity::class, ['id' => 'complexity_id']);
    }

    /**
     * Gets query for [[Steps]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSteps()
    {
        return $this->hasMany(Step::class, ['recipe_id' => 'id']);
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
