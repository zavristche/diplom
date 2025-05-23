<?php

namespace app\models\block;

use Yii;
use app\models\block\Block;

/**
 * This is the model class for table "block_type".
 *
 * @property int $id
 * @property string $title
 *
 * @property Block[] $blocks
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
     * Gets query for [[Blocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBlocks()
    {
        return $this->hasMany(Block::class, ['block_type_id' => 'id']);
    }

    public static function getOne($title)
    {
        return self::findOne(['title' => $title])->id;
    }
}
