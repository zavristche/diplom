<?php

namespace app\models\block;

use Yii;
use app\models\block\BlockRecipe;
use app\models\block\BlockCollection;
use app\models\block\BlockUser;

/**
 * This is the model class for table "block_type".
 *
 * @property int $id
 * @property string $title
 *
 * @property BlockCollection[] $blockCollections
 * @property BlockRecipe[] $blockRecipes
 * @property BlockUser[] $blockUsers
 */
class BlockType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'block_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
        ];
    }

    /**
     * Gets query for [[BlockCollections]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockCollections()
    {
        return $this->hasMany(BlockCollection::class, ['type_id' => 'id']);
    }

    /**
     * Gets query for [[BlockRecipes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockRecipes()
    {
        return $this->hasMany(BlockRecipe::class, ['type_id' => 'id']);
    }

    /**
     * Gets query for [[BlockUsers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlockUsers()
    {
        return $this->hasMany(BlockUser::class, ['type_id' => 'id']);
    }
}
