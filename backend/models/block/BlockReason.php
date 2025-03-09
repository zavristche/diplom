<?php

namespace app\models\block;

use Yii;
use app\models\block\Block;

/**
 * This is the model class for table "block_reason".
 *
 * @property int $id
 * @property string $title
 *
 * @property Block[] $blocks
 */
class BlockReason extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'block_reason';
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
        return $this->hasMany(Block::class, ['block_reason_id' => 'id']);
    }
}
